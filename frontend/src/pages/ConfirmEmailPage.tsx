import { useEffect, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { CheckCircle2, XCircle } from 'lucide-react';
import { useConfirmEmail, useResendConfirmation } from '../hooks/useRegistration';

type Status = 'loading' | 'success' | 'error';

/** Page de confirmation d'email : appelle l'API avec le jeton du mail reçu. */
export function ConfirmEmailPage() {
  const [searchParams] = useSearchParams();
  const token = searchParams.get('token') ?? '';
  const confirm = useConfirmEmail();
  const resend = useResendConfirmation();

  const [status, setStatus] = useState<Status>('loading');
  const [error, setError] = useState<string | null>(null);
  const [email, setEmail] = useState('');
  const [resendMessage, setResendMessage] = useState<string | null>(null);

  useEffect(() => {
    if (token === '') {
      setStatus('error');
      setError('Lien de confirmation invalide : aucun jeton fourni.');
      return;
    }

    confirm
      .mutateAsync(token)
      .then(() => setStatus('success'))
      .catch((err) => {
        setError(err instanceof Error ? err.message : 'Confirmation impossible.');
        setStatus('error');
      });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [token]);

  const handleResend = async () => {
    setResendMessage(null);
    try {
      await resend.mutateAsync(email.trim());
      setResendMessage('Email renvoyé si un compte en attente de confirmation existe.');
    } catch (err) {
      setResendMessage(err instanceof Error ? err.message : 'Renvoi impossible.');
    }
  };

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
      <div className="glass-panel" style={{ width: '100%', maxWidth: 420, padding: '2.4rem', textAlign: 'center' }}>
        {status === 'loading' && (
          <>
            <h1 style={{ fontSize: '1.4rem' }}>Confirmation de votre adresse email…</h1>
            <p className="text-muted text-small" style={{ marginTop: '0.6rem' }}>
              Un instant, nous activons votre compte.
            </p>
          </>
        )}

        {status === 'success' && (
          <>
            <CheckCircle2 size={44} style={{ color: 'var(--emerald-500, #10b981)' }} />
            <h1 style={{ marginTop: '0.9rem', fontSize: '1.4rem' }}>Compte confirmé !</h1>
            <p className="text-muted text-small" style={{ marginTop: '0.5rem', lineHeight: 1.6 }}>
              Votre adresse email est vérifiée, vous pouvez maintenant vous connecter.
            </p>
            <p style={{ marginTop: '1.2rem' }}>
              <Link className="btn btn-primary" to="/login" style={{ textDecoration: 'none' }}>
                Se connecter
              </Link>
            </p>
          </>
        )}

        {status === 'error' && (
          <>
            <XCircle size={44} style={{ color: 'var(--rose-500, #f43f5e)' }} />
            <h1 style={{ marginTop: '0.9rem', fontSize: '1.4rem' }}>Lien invalide ou expiré</h1>
            <p className="text-muted text-small" style={{ marginTop: '0.5rem', lineHeight: 1.6 }}>
              {error}
            </p>
            <div style={{ marginTop: '1.2rem' }}>
              <div className="field">
                <label htmlFor="confirm-email">Votre email</label>
                <input
                  id="confirm-email"
                  type="email"
                  value={email}
                  onChange={(event) => setEmail(event.target.value)}
                  placeholder="vous@exemple.fr"
                />
              </div>
              <button
                type="button"
                className="btn btn-primary"
                style={{ width: '100%' }}
                onClick={handleResend}
                disabled={resend.isPending || email.trim() === ''}
              >
                {resend.isPending ? 'Envoi…' : "Renvoyer l'email de confirmation"}
              </button>
              {resendMessage && (
                <p className="text-muted text-small" role="status" style={{ marginTop: '0.6rem' }}>
                  {resendMessage}
                </p>
              )}
            </div>
            <p className="text-muted text-small" style={{ marginTop: '1.2rem' }}>
              <Link to="/register">Créer un compte</Link> · <Link to="/">Accueil</Link>
            </p>
          </>
        )}
      </div>
    </div>
  );
}
