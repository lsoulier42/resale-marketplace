import { useState, type FormEvent, type ReactNode } from 'react';
import { Link } from 'react-router-dom';
import { MailCheck } from 'lucide-react';

export interface RegistrationFormValues {
  displayName: string;
  email: string;
  password: string;
}

interface RegistrationFormProps {
  title: string;
  subtitle: string;
  submitLabel: string;
  pendingLabel: string;
  onSubmit: (values: RegistrationFormValues) => Promise<void>;
  /** Si fourni, un bouton « Renvoyer l'email » apparaît sur l'écran de succès. */
  onResend?: (email: string) => Promise<void>;
  /** Liens affichés sous le panneau (connexion, parcours vendeur…). */
  footer: ReactNode;
}

/**
 * Formulaire d'inscription partagé (client·e & vendeur·se) + écran de succès
 * « Vérifiez votre boîte mail » (le compte est créé non vérifié).
 */
export function RegistrationForm({
  title,
  subtitle,
  submitLabel,
  pendingLabel,
  onSubmit,
  onResend,
  footer,
}: RegistrationFormProps) {
  const [displayName, setDisplayName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const [sent, setSent] = useState(false);
  const [resending, setResending] = useState(false);
  const [resendMessage, setResendMessage] = useState<string | null>(null);

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault();
    setError(null);
    setSubmitting(true);
    try {
      await onSubmit({ displayName, email: email.trim(), password });
      setSent(true);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Inscription impossible.');
    } finally {
      setSubmitting(false);
    }
  };

  const handleResend = async () => {
    if (!onResend) {
      return;
    }
    setResending(true);
    setResendMessage(null);
    try {
      await onResend(email);
      setResendMessage('Email renvoyé : vérifiez votre boîte mail (pensez aux indésirables).');
    } catch (err) {
      setResendMessage(err instanceof Error ? err.message : 'Renvoi impossible.');
    } finally {
      setResending(false);
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
      <div className="card" style={{ width: '100%', maxWidth: 420, padding: '2.4rem' }}>
        <div style={{ textAlign: 'center', marginBottom: '1.8rem' }}>
          <img
            src="/favicon.svg"
            alt="Resale Marketplace"
            width={56}
            height={56}
            style={{ borderRadius: 16, boxShadow: '0 8px 24px rgba(16,24,40,0.12)' }}
          />
          <h1 style={{ marginTop: '0.9rem', fontSize: '1.5rem' }}>{title}</h1>
          <p className="text-muted text-small" style={{ marginTop: '0.3rem' }}>
            {subtitle}
          </p>
        </div>

        {sent ? (
          <div style={{ textAlign: 'center' }}>
            <MailCheck size={44} style={{ color: 'var(--color-accent)' }} />
            <h2 style={{ marginTop: '0.9rem', fontSize: '1.2rem' }}>Vérifiez votre boîte mail</h2>
            <p className="text-muted text-small" style={{ marginTop: '0.5rem', lineHeight: 1.6 }}>
              Un email de confirmation a été envoyé à <strong>{email}</strong>. Cliquez sur le
              lien qu'il contient pour activer votre compte (valable 24&nbsp;heures).
            </p>
            {onResend && (
              <button
                type="button"
                className="btn btn-ghost btn-sm"
                style={{ marginTop: '0.8rem' }}
                onClick={handleResend}
                disabled={resending}
              >
                {resending ? 'Envoi…' : "Renvoyer l'email"}
              </button>
            )}
            {resendMessage && (
              <p className="text-muted text-small" role="status" style={{ marginTop: '0.6rem' }}>
                {resendMessage}
              </p>
            )}
            <p style={{ marginTop: '1.2rem' }}>
              <Link className="btn btn-primary" to="/login" style={{ textDecoration: 'none' }}>
                Se connecter
              </Link>
            </p>
          </div>
        ) : (
          <>
            {error && (
              <div className="form-error-banner" role="alert">
                {error}
              </div>
            )}

            <form onSubmit={handleSubmit} noValidate>
              <div className="field">
                <label htmlFor="register-name">Pseudo</label>
                <input
                  id="register-name"
                  value={displayName}
                  onChange={(event) => setDisplayName(event.target.value)}
                  required
                  placeholder="Ex. Camille"
                />
              </div>
              <div className="field">
                <label htmlFor="register-email">Email</label>
                <input
                  id="register-email"
                  type="email"
                  autoComplete="email"
                  value={email}
                  onChange={(event) => setEmail(event.target.value)}
                  required
                  placeholder="vous@exemple.fr"
                />
              </div>
              <div className="field">
                <label htmlFor="register-password">Mot de passe</label>
                <input
                  id="register-password"
                  type="password"
                  autoComplete="new-password"
                  minLength={8}
                  value={password}
                  onChange={(event) => setPassword(event.target.value)}
                  required
                  placeholder="8 caractères minimum"
                />
              </div>
              <button
                type="submit"
                className="btn btn-primary"
                style={{ width: '100%' }}
                disabled={submitting}
              >
                {submitting ? pendingLabel : submitLabel}
              </button>
            </form>
          </>
        )}

        <div style={{ marginTop: '1.4rem', textAlign: 'center' }}>{footer}</div>
      </div>
    </div>
  );
}
