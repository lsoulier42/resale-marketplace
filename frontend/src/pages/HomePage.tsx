import { Link } from 'react-router-dom';
import { Sparkles } from 'lucide-react';
import { GlassCard } from '../components/ui/GlassCard';
import { ItemGrid } from '../components/domain/ItemGrid';
import { CategoryCard } from '../components/domain/CategoryCard';
import { useAuth } from '../auth/useAuth';
import { useHome } from '../hooks/useCatalog';

export function HomePage() {
  const { user } = useAuth();
  const { data, isLoading, isError } = useHome();

  return (
    <div>
      <GlassCard style={{ padding: '2.2rem 2.4rem', textAlign: 'center' }}>
        <h1 className="page-title" style={{ fontSize: '2rem', marginBottom: '0.5rem' }}>
          Bienvenue sur Resale Marketplace
        </h1>
        <p className="page-subtitle" style={{ marginBottom: 0 }}>
          La marketplace de revente entre particuliers — achetez et vendez en toute confiance.
        </p>
        {user && (
          <p className="text-muted mt-2">
            Ravie de vous revoir, <strong>{user.email}</strong> !
          </p>
        )}
      </GlassCard>

      {isError && (
        <div className="glass-card mt-3" style={{ padding: '1.2rem', textAlign: 'center' }}>
          <p className="text-muted">Impossible de charger la boutique pour le moment.</p>
        </div>
      )}

      <h2 className="mt-3" style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
        <Sparkles size={20} style={{ color: 'var(--mauve-500)' }} /> Articles en vedette
      </h2>
      <div style={{ marginTop: '1rem' }}>
        <ItemGrid items={data?.featuredItems ?? []} loading={isLoading} />
      </div>

      <div className="flex-between mt-4" style={{ alignItems: 'baseline' }}>
        <h2 style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
          <Sparkles size={20} style={{ color: 'var(--rose-400)' }} /> Catégories
        </h2>
        <Link to="/categories" className="text-small">
          Tout voir
        </Link>
      </div>
      <div
        style={{
          display: 'grid',
          gridTemplateColumns: 'repeat(auto-fill, minmax(220px, 1fr))',
          gap: '1.2rem',
          marginTop: '1rem',
        }}
      >
        {(data?.categories ?? []).map((category) => (
          <CategoryCard key={category.uuid} category={category} />
        ))}
      </div>
    </div>
  );
}
