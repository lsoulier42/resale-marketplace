import type { ReactNode } from 'react';
import { Navigate } from 'react-router-dom';
import { useAuth, useIsAdmin } from './useAuth';

/** Garde de route : accès réservé aux administrateurs. */
export function RequireAdmin({ children }: { children: ReactNode }) {
  const { loading } = useAuth();
  const isAdmin = useIsAdmin();

  if (loading) {
    return (
      <div className="glass-panel" style={{ padding: '2rem', textAlign: 'center', maxWidth: 420, margin: '3rem auto' }}>
        Chargement…
      </div>
    );
  }

  if (!isAdmin) {
    return <Navigate to="/" replace />;
  }

  return children;
}
