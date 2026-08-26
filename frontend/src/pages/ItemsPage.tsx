import { useEffect, useState } from 'react';
import { ItemGrid } from '../components/domain/ItemGrid';
import { Pagination } from '../components/ui/Pagination';
import { useItems, useCategories } from '../hooks/useCatalog';
import { Skeleton } from '../components/ui/Skeleton';

export function ItemsPage() {
  const [page, setPage] = useState(1);
  const [category, setCategory] = useState('');
  const { data, isLoading, isError } = useItems(page, category);
  const categoriesQuery = useCategories();

  useEffect(() => {
    setPage(1);
  }, [category]);

  return (
    <div>
      <div className="flex-between" style={{ alignItems: 'flex-end', flexWrap: 'wrap' }}>
        <div>
          <h1 className="page-title" style={{ marginBottom: '0.3rem' }}>
            Articles
          </h1>
          <p className="text-muted text-small">
            {data ? `${data.total} article${data.total > 1 ? 's' : ''} disponible${data.total > 1 ? 's' : ''}` : '…'}
          </p>
        </div>
        <div className="field" style={{ marginBottom: 0, minWidth: 220 }}>
          <label htmlFor="items-category">Filtrer par catégorie</label>
          <select
            id="items-category"
            value={category}
            onChange={(event) => setCategory(event.target.value)}
          >
            <option value="">Toutes les catégories</option>
            {(categoriesQuery.data?.categories ?? []).map((cat) => (
              <option key={cat.uuid} value={cat.uuid}>
                {cat.title}
              </option>
            ))}
          </select>
        </div>
      </div>

      {isError && (
        <div className="glass-card mt-2" style={{ padding: '1.2rem', textAlign: 'center' }}>
          <p className="text-muted">Impossible de charger les articles.</p>
        </div>
      )}
      {isLoading && (
        <div className="mt-2">
          <Skeleton height={24} width={180} />
          <div style={{ marginTop: '1rem' }}>
            <ItemGrid items={[]} loading />
          </div>
        </div>
      )}

      {data && (
        <>
          <div style={{ marginTop: '1rem' }}>
            <ItemGrid items={data.items} loading={false} />
          </div>
          <Pagination page={data.page} pages={data.pages} onChange={setPage} />
        </>
      )}
    </div>
  );
}
