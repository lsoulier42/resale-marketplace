import { createContext } from 'react';
import type { ApiUser } from '../api/types';

export interface AuthContextValue {
  /** Utilisateur connecté, ou null si visiteur. */
  user: ApiUser | null;
  /** true si l'utilisateur est rattaché à un compte vendeur·se. */
  isSeller: boolean;
  /** true si l'utilisateur est rattaché à un compte client. */
  isCustomer: boolean;
  /** true tant que l'état d'auth initial (GET /api/me) n'est pas résolu. */
  loading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  refresh: () => Promise<void>;
}

export const AuthContext = createContext<AuthContextValue | null>(null);
