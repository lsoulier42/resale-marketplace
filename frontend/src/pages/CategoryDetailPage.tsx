import { Link, useParams } from 'react-router-dom';
import { ArrowLeft } from 'lucide-react';
import { Card } from '../components/ui/Card';
import { ItemGrid } from '../components/domain/ItemGrid';
import { Skeleton } from '../components/ui/Skeleton';
import { useCategory } from '../hooks/useCatalog';

export function CategoryDetailPage() {
  const { uuid = '' } = useParams();
  const { data, isLoading, isError } = useCategory(uuid);

  if (isLoading) {
    return (
      <div className="card" style={{ padding: '1.6rem' }}>
        <Skeleton height={24} width={200} />
        <div style={{ marginTop: '1rem' }}>
          <ItemGrid items={[]} loading />
        </div>
      </div>
    );
  }

  if (isError || !data) {
    return (
      <Card style={{ padding: '2rem', textAlign: 'center' }}>
        <p className="text-muted">Catégorie introuvable.</p>
        <Link to="/categories" className="btn btn-secondary mt-2">
          <ArrowLeft size={16} /> Retour aux catégories
        </Link>
      </Card>
    );
  }

  return (
    <div>
      <Link to="/categories" className="btn btn-ghost btn-sm mb-2">
        <ArrowLeft size={15} /> Retour aux catégories
      </Link>
      <Card style={{ padding: '1.6rem 1.8rem', marginBottom: '1.4rem' }}>
        <div className="flex-between" style={{ alignItems: 'flex-end', flexWrap: 'wrap', gap: '0.8rem' }}>
          <div>
            <h1 className="page-title" style={{ marginBottom: '0.3rem' }}>
              {data.category.title}
            </h1>
            {data.category.description && (
              <p className="text-muted">{data.category.description}</p>
            )}
          </div>
          <span className="badge badge-accent">
            {data.category.itemCount} article{data.category.itemCount > 1 ? 's' : ''}
          </span>
        </div>
      </Card>

      <ItemGrid items={data.items} loading={false} emptyTitle="Aucun article dans cette catégorie" />
    </div>
  );
}
