import type { ReactNode } from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from './useAuth';

/** Garde de route : redirige vers /login si l'utilisateur n'est pas connecté. */
export function RequireAuth({ children }: { children: ReactNode }) {
  const { user, loading } = useAuth();
  const location = useLocation();

  if (loading) {
    return (
      <div className="glass-panel" style={{ padding: '2rem', textAlign: 'center', maxWidth: 420, margin: '3rem auto' }}>
        Chargement…
      </div>
    );
  }

  if (!user) {
    return <Navigate to="/login" replace state={{ from: location.pathname }} />;
  }

  return children;
}
