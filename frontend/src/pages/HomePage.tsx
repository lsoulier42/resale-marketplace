import { Link } from 'react-router-dom';
import { ItemGrid } from '../components/domain/ItemGrid';
import { useAuth } from '../auth/useAuth';
import { useHome } from '../hooks/useCatalog';

export function HomePage() {
  const { user } = useAuth();
  const { data, isLoading, isError } = useHome();

  return (
    <div>
      <section className="home-hero">
        <h1 className="home-hero-title">Bienvenue sur Resale Marketplace</h1>
        <p className="home-hero-subtitle">
          La marketplace de revente entre particuliers — achetez et vendez en toute confiance.
        </p>
        {user && (
          <p className="text-muted text-small mt-2">
            Ravie de vous revoir, <strong>{user.email}</strong> !
          </p>
        )}
      </section>

      {isError && (
        <div className="card mt-2" style={{ padding: '1.2rem', textAlign: 'center' }}>
          <p className="text-muted">Impossible de charger la boutique pour le moment.</p>
        </div>
      )}

      <section className="mt-2">
        <div className="flex-between" style={{ alignItems: 'baseline' }}>
          <h2 className="section-title">Articles en vedette</h2>
          <Link to="/items" className="link-more">
            Tout voir
          </Link>
        </div>
        <ItemGrid items={data?.featuredItems ?? []} loading={isLoading} />
      </section>

      <section className="mt-4">
        <div className="flex-between" style={{ alignItems: 'baseline' }}>
          <h2 className="section-title">Parcourir par catégorie</h2>
          <Link to="/categories" className="link-more">
            Tout voir
          </Link>
        </div>
        <div className="category-chips">
          {(data?.categories ?? []).map((category) => (
            <Link
              key={category.uuid}
              to={`/categories/${category.uuid}`}
              className="chip chip--link"
            >
              {category.title}
            </Link>
          ))}
        </div>
      </section>
    </div>
  );
}
