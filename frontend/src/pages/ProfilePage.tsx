import { useEffect, useState, type FormEvent } from 'react';
import { CreditCard, Store, UserCircle } from 'lucide-react';
import { Card } from '../components/ui/Card';
import { Button } from '../components/ui/Button';
import { Field } from '../components/ui/Field';
import { Skeleton } from '../components/ui/Skeleton';
import { useToast } from '../components/ui/useToast';
import { useProfile, useUpdateProfile } from '../hooks/useCustomer';
import { useBecomeSeller } from '../hooks/useRegistration';
import { useStripeOnboarding, useStripeStatus } from '../hooks/useSeller';
import { useAuth } from '../auth/useAuth';
import type { ProfileData } from '../api/types';

export function ProfilePage() {
  const toast = useToast();
  const { isSeller, refresh } = useAuth();
  const { data, isLoading, isError } = useProfile();
  const updateProfile = useUpdateProfile();
  const becomeSeller = useBecomeSeller();
  const { data: stripeData } = useStripeStatus();
  const stripeOnboarding = useStripeOnboarding();

  const [displayName, setDisplayName] = useState('');
  const [bio, setBio] = useState('');

  const profile: ProfileData | null | undefined = data?.profile;

  // Pré-remplit le formulaire quand le profil est chargé.
  useEffect(() => {
    if (!isLoading && data) {
      setDisplayName(profile?.displayName ?? '');
      setBio(profile?.bio ?? '');
    }
  }, [data, isLoading, profile]);

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault();
    try {
      await updateProfile.mutateAsync({
        displayName,
        bio: bio.trim() !== '' ? bio : null,
      });
      toast.success('Profil enregistré.');
    } catch (error) {
      toast.error(error instanceof Error ? error.message : 'Enregistrement impossible.');
    }
  };

  const handleBecomeSeller = async () => {
    try {
      await becomeSeller.mutateAsync();
      await refresh();
      toast.success('Bienvenue dans l\'espace vendeur·se !');
    } catch (error) {
      toast.error(error instanceof Error ? error.message : 'Opération impossible.');
    }
  };

  const handleStripeOnboarding = async () => {
    try {
      const { url } = await stripeOnboarding.mutateAsync();
      window.location.assign(url);
    } catch (error) {
      toast.error(error instanceof Error ? error.message : 'Onboarding Stripe impossible.');
    }
  };

  if (isLoading) {
    return (
      <div className="card" style={{ padding: '1.6rem', display: 'flex', flexDirection: 'column', gap: '1rem' }}>
        <Skeleton height={26} width={200} />
        <Skeleton height={16} width="70%" />
      </div>
    );
  }

  if (isError) {
    return (
      <Card style={{ padding: '1.5rem', textAlign: 'center' }}>
        <p className="text-muted">Impossible de charger votre profil.</p>
      </Card>
    );
  }

  return (
    <div style={{ maxWidth: 640 }}>
      <h1 className="page-title" style={{ display: 'flex', alignItems: 'center', gap: '0.6rem' }}>
        <UserCircle size={26} style={{ color: 'var(--color-accent)' }} /> Mon profil
      </h1>
      <p className="page-subtitle">
        {profile ? 'Vos informations publiques sur la boutique.' : 'Créez votre profil public de vendeur·se.'}
      </p>

      <Card style={{ padding: '1.8rem' }}>
        <form onSubmit={handleSubmit}>
          <Field label="Pseudo" htmlFor="profile-name">
            <input
              id="profile-name"
              value={displayName}
              onChange={(event) => setDisplayName(event.target.value)}
              required
              placeholder="Ex. Camille"
            />
          </Field>
          <Field label="Biographie" htmlFor="profile-bio">
            <textarea
              id="profile-bio"
              rows={4}
              value={bio}
              onChange={(event) => setBio(event.target.value)}
              placeholder="Quelques mots sur vous…"
            />
          </Field>
          <Button type="submit" disabled={updateProfile.isPending}>
            {updateProfile.isPending ? 'Enregistrement…' : 'Enregistrer'}
          </Button>
        </form>
      </Card>

      {!isSeller && (
        <Card
          style={{
            marginTop: '1.2rem',
            padding: '1.6rem',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            gap: '1.2rem',
            flexWrap: 'wrap',
          }}
        >
          <div style={{ display: 'flex', gap: '0.9rem', alignItems: 'flex-start' }}>
            <Store size={24} style={{ color: 'var(--color-accent)', flexShrink: 0, marginTop: '0.15rem' }} />
            <div>
              <h2 style={{ fontSize: '1.05rem', margin: 0 }}>Vendre sur Resale Marketplace</h2>
              <p className="text-muted text-small" style={{ margin: '0.3rem 0 0' }}>
                Publiez vos articles, recevez des commandes et des avis.
              </p>
            </div>
          </div>
          <Button onClick={handleBecomeSeller} disabled={becomeSeller.isPending}>
            {becomeSeller.isPending ? 'Création…' : 'Devenir vendeur·se'}
          </Button>
        </Card>
      )}

      {isSeller && (
        <Card
          style={{
            marginTop: '1.2rem',
            padding: '1.6rem',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            gap: '1.2rem',
            flexWrap: 'wrap',
          }}
        >
          <div style={{ display: 'flex', gap: '0.9rem', alignItems: 'flex-start' }}>
            <CreditCard size={24} style={{ color: 'var(--color-accent)', flexShrink: 0, marginTop: '0.15rem' }} />
            <div>
              <h2 style={{ fontSize: '1.05rem', margin: 0 }}>Recevoir des paiements</h2>
              <p className="text-muted text-small" style={{ margin: '0.3rem 0 0' }}>
                {stripeData?.stripe.ready
                  ? 'Votre compte de paiement est prêt : vos ventes seront encaissées via Stripe.'
                  : 'Connectez votre compte Stripe pour encaisser vos ventes (KYC sécurisé).'}
              </p>
            </div>
          </div>
          <Button
            onClick={handleStripeOnboarding}
            disabled={stripeOnboarding.isPending}
          >
            {stripeOnboarding.isPending
              ? 'Redirection…'
              : stripeData?.stripe.ready
                ? 'Gérer mon compte Stripe'
                : 'Configurer mes paiements'}
          </Button>
        </Card>
      )}
    </div>
  );
}
