import { Link, Navigate } from 'react-router-dom';
import { Heart, ShoppingBag } from 'lucide-react';
import { useAuth } from '../auth/useAuth';
import { RegistrationForm } from '../components/domain/RegistrationForm';
import { useRegisterSeller, useResendConfirmation } from '../hooks/useRegistration';

/** Parcours d'inscription vendeur·se : compte client + espace vente. */
export function SellerRegisterPage() {
  const { user } = useAuth();
  const register = useRegisterSeller();
  const resend = useResendConfirmation();

  if (user) {
    return <Navigate to="/" replace />;
  }

  return (
    <RegistrationForm
      title="Lancez votre boutique"
      subtitle="Créez votre compte vendeur·se et publiez vos articles"
      submitLabel="Créer mon compte vendeur·se"
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
            inscrit·e ? <Link to="/login">Se connecter</Link>
          </p>
          <p className="text-muted text-small">
            <ShoppingBag size={12} style={{ verticalAlign: '-2px', color: 'var(--mauve-500)' }} />{' '}
            Vous voulez juste acheter ? <Link to="/register">Créer un compte client</Link>
          </p>
        </>
      }
    />
  );
}
