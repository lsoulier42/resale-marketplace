import { useContext } from 'react';
import { AuthContext, type AuthContextValue } from './context';

export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error('useAuth doit être utilisé dans un AuthProvider.');
  }
  return ctx;
}

export function useIsAdmin(): boolean {
  const { user } = useAuth();
  return user?.roles.includes('ROLE_ADMIN') ?? false;
}
