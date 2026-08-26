import { Link, Navigate } from 'react-router-dom';
import { Heart, Store } from 'lucide-react';
import { useAuth } from '../auth/useAuth';
import { RegistrationForm } from '../components/domain/RegistrationForm';
import { useRegister, useResendConfirmation } from '../hooks/useRegistration';

/** Parcours d'inscription client (acheteuse). */
export function RegisterPage() {
  const { user } = useAuth();
  const register = useRegister();
  const resend = useResendConfirmation();

  if (user) {
    return <Navigate to="/" replace />;
  }

  return (
    <RegistrationForm
      title="Créer un compte"
      subtitle="Rejoignez la boutique en quelques secondes"
      submitLabel="Créer mon compte"
      pendingLabel="Création…"
      onSubmit={async (values) => {
        await register.mutateAsync(values);
      }}
      onResend={async (email) => {
        await resend.mutateAsync(email);
      }}
      footer={
        <>
          <p className="text-muted text-small" style={{ marginBottom: '0.6rem' }}>
            <Heart size={12} style={{ verticalAlign: '-2px', color: 'var(--rose-400)' }} /> Déjà
            inscrite ? <Link to="/login">Se connecter</Link>
          </p>
          <p className="text-muted text-small">
            <Store size={12} style={{ verticalAlign: '-2px', color: 'var(--mauve-500)' }} /> Vous
            voulez vendre ? <Link to="/register/seller">Créer un compte vendeur·se</Link>
          </p>
        </>
      }
    />
  );
}
