import { Tags } from 'lucide-react';
import { CategoryCard } from '../components/domain/CategoryCard';
import { EmptyState } from '../components/ui/EmptyState';
import { Skeleton } from '../components/ui/Skeleton';
import { useCategories } from '../hooks/useCatalog';

export function CategoriesPage() {
  const { data, isLoading, isError } = useCategories();

  return (
    <div>
      <h1 className="page-title">Catégories</h1>
      <p className="page-subtitle">Parcourez la boutique par catégorie.</p>

      {isError && (
        <div className="card" style={{ padding: '1.2rem', textAlign: 'center' }}>
          <p className="text-muted">Impossible de charger les catégories.</p>
        </div>
      )}

      {isLoading ? (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(220px, 1fr))', gap: '1.2rem' }}>
          {Array.from({ length: 6 }, (_, index) => (
            <div key={index} className="card" style={{ padding: '1.5rem', display: 'flex', flexDirection: 'column', gap: '0.6rem' }}>
              <Skeleton height={44} borderRadius={14} width={44} />
              <Skeleton height={18} width="60%" />
              <Skeleton height={14} width="85%" />
            </div>
          ))}
        </div>
      ) : data && data.categories.length > 0 ? (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(220px, 1fr))', gap: '1.2rem' }}>
          {data.categories.map((category) => (
            <CategoryCard key={category.uuid} category={category} />
          ))}
        </div>
      ) : (
        <EmptyState icon={<Tags size={28} />} title="Aucune catégorie" />
      )}
    </div>
  );
}
