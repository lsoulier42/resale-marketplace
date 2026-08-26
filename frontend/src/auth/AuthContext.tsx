import { useCallback, useEffect, useMemo, useState, type ReactNode } from 'react';
import { apiFetch, apiLogin as loginRequest, apiLogout as logoutRequest } from '../api/client';
import type { ApiUser, MeResponse } from '../api/types';
import { AuthContext } from './context';

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<ApiUser | null>(null);
  const [isSeller, setIsSeller] = useState(false);
  const [isCustomer, setIsCustomer] = useState(false);
  const [loading, setLoading] = useState(true);

  const refresh = useCallback(async () => {
    try {
      const data = await apiFetch<MeResponse>('/api/me');
      setUser(data.user);
      setIsSeller(data.isSeller);
      setIsCustomer(data.isCustomer);
    } catch {
      setUser(null);
      setIsSeller(false);
      setIsCustomer(false);
    }
  }, []);

  useEffect(() => {
    void refresh().finally(() => setLoading(false));
  }, [refresh]);

  const login = useCallback(async (email: string, password: string) => {
    await loginRequest(email, password);
    await refresh();
  }, [refresh]);

  const logout = useCallback(async () => {
    try {
      await logoutRequest();
    } finally {
      setUser(null);
      setIsSeller(false);
      setIsCustomer(false);
    }
  }, []);

  const value = useMemo(
    () => ({ user, isSeller, isCustomer, loading, login, logout, refresh }),
    [user, isSeller, isCustomer, loading, login, logout, refresh],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
