import { useState, type FormEvent } from 'react';
import { Navigate, useNavigate, Link } from 'react-router-dom';
import { Heart, Mail } from 'lucide-react';
import { useAuth } from '../auth/useAuth';
import { useResendConfirmation } from '../hooks/useRegistration';

export function LoginPage() {
  const { user, loading, login } = useAuth();
  const navigate = useNavigate();
  const resend = useResendConfirmation();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const [showResend, setShowResend] = useState(false);
  const [resendMessage, setResendMessage] = useState<string | null>(null);

  if (loading) {
    return <div className="glass-panel page-title" style={{ maxWidth: 420, margin: '3rem auto', padding: '2rem', textAlign: 'center' }}>Chargement…</div>;
  }
  if (user) {
    return <Navigate to="/" replace />;
  }

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault();
    setError(null);
    setResendMessage(null);
    setSubmitting(true);
    try {
      await login(email.trim(), password);
      navigate('/', { replace: true });
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Connexion impossible.');
    } finally {
      setSubmitting(false);
    }
  };

  const handleResend = async () => {
    setResendMessage(null);
    try {
      await resend.mutateAsync(email.trim());
      setResendMessage('Email renvoyé si un compte en attente de confirmation existe.');
    } catch (err) {
      setResendMessage(err instanceof Error ? err.message : 'Renvoi impossible.');
    }
  };

  const isUnverified = error !== null && error.includes('vérifié');

  return (
    <div
      style={{
        minHeight: '100vh',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        padding: '1.5rem',
      }}
    >
      <div className="glass-panel" style={{ width: '100%', maxWidth: 420, padding: '2.4rem' }}>
        <div style={{ textAlign: 'center', marginBottom: '1.8rem' }}>
          <img src="/favicon.svg" alt="Resale Marketplace" width={56} height={56} style={{ borderRadius: 16, boxShadow: '0 8px 24px rgba(139,92,246,.35)' }} />
          <h1 style={{ marginTop: '0.9rem', fontSize: '1.5rem' }}>Bienvenue sur Resale Marketplace</h1>
          <p className="text-muted text-small" style={{ marginTop: '0.3rem' }}>
            Connectez-vous pour découvrir la boutique
          </p>
        </div>

        {error && (
          <div className="form-error-banner" role="alert">
            {error}
          </div>
        )}

        {isUnverified && (
          <div style={{ marginTop: '0.8rem', textAlign: 'center' }}>
            <button
              type="button"
              className="btn btn-ghost btn-sm"
              onClick={() => setShowResend((value) => !value)}
            >
              <Mail size={13} style={{ verticalAlign: '-2px', marginRight: '0.3rem' }} />
              Renvoyer l'email de confirmation
            </button>
            {showResend && (
              <p className="text-muted text-small" style={{ marginTop: '0.6rem', lineHeight: 1.5 }}>
                Le lien sera renvoyé à <strong>{email || 'votre adresse'}</strong>.
              </p>
            )}
            {showResend && (
              <button
                type="button"
                className="btn btn-ghost btn-sm"
                style={{ marginTop: '0.4rem' }}
                onClick={handleResend}
                disabled={resend.isPending || email.trim() === ''}
              >
                {resend.isPending ? 'Envoi…' : "Renvoyer l'email"}
              </button>
            )}
            {resendMessage && (
              <p className="text-muted text-small" role="status" style={{ marginTop: '0.6rem' }}>
                {resendMessage}
              </p>
            )}
          </div>
        )}

        <form onSubmit={handleSubmit} noValidate>
          <div className="field">
            <label htmlFor="login-email">Email</label>
            <input
              id="login-email"
              type="email"
              autoComplete="email"
              required
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="vous@exemple.fr"
            />
          </div>
          <div className="field">
            <label htmlFor="login-password">Mot de passe</label>
            <input
              id="login-password"
              type="password"
              autoComplete="current-password"
              required
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="••••••••"
            />
          </div>
          <button type="submit" className="btn btn-primary" style={{ width: '100%' }} disabled={submitting}>
            {submitting ? 'Connexion…' : 'Se connecter'}
          </button>
        </form>

        <p className="text-muted text-small" style={{ textAlign: 'center', marginTop: '1.4rem' }}>
          <Heart size={12} style={{ verticalAlign: '-2px', color: 'var(--rose-400)' }} />{' '}
          Pas encore de compte ? <Link to="/register">Créer un compte</Link> ·{' '}
          <Link to="/">Accueil</Link>
        </p>
      </div>
    </div>
  );
}
