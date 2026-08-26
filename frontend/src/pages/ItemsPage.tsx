import { useEffect, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { ItemGrid } from '../components/domain/ItemGrid';
import { Pagination } from '../components/ui/Pagination';
import { useItems, useCategories } from '../hooks/useCatalog';
import type { ItemSort } from '../api/endpoints';

function readSort(value: string | null): ItemSort {
  return value === 'price_asc' || value === 'price_desc' ? value : 'recent';
}

export function ItemsPage() {
  const [searchParams, setSearchParams] = useSearchParams();
  const [page, setPage] = useState(() => {
    const value = Number(searchParams.get('page'));
    return Number.isFinite(value) && value > 0 ? value : 1;
  });
  const [category, setCategory] = useState(searchParams.get('category') ?? '');
  const [sort, setSort] = useState<ItemSort>(() => readSort(searchParams.get('sort')));
  const q = searchParams.get('q') ?? '';

  const { data, isLoading, isError } = useItems(page, category, q, sort);
  const categoriesQuery = useCategories();

  // Synchronise l'URL avec les filtres (permet le partage / le retour arrière).
  useEffect(() => {
    const params = new URLSearchParams();
    if (category !== '') params.set('category', category);
    if (sort !== 'recent') params.set('sort', sort);
    if (q !== '') params.set('q', q);
    if (page > 1) params.set('page', String(page));
    setSearchParams(params, { replace: true });
  }, [page, category, sort, q, setSearchParams]);

  const changeCategory = (uuid: string) => {
    setCategory(uuid);
    setPage(1);
  };

  const changeSort = (value: string) => {
    setSort(readSort(value));
    setPage(1);
  };

  const totalLabel = data
    ? `${data.total} article${data.total > 1 ? 's' : ''} disponible${data.total > 1 ? 's' : ''}${q !== '' ? ' — résultat' + (data.total > 1 ? 's' : '') : ''}`
    : '…';

  return (
    <div>
      <div className="flex-between" style={{ alignItems: 'flex-end', flexWrap: 'wrap', gap: '0.8rem' }}>
        <h1 className="page-title" style={{ marginBottom: 0 }}>
          {q !== '' ? <>Résultats pour « {q} »</> : 'Articles'}
        </h1>
      </div>

      {isError && (
        <div className="card mt-2" style={{ padding: '1.2rem', textAlign: 'center' }}>
          <p className="text-muted">Impossible de charger les articles.</p>
        </div>
      )}

      <div className="catalog-layout mt-3">
        <aside className="catalog-filters" aria-label="Filtres">
          <h2 className="catalog-filters-title">Catégories</h2>
          <ul className="catalog-filters-list">
            <li>
              <button
                type="button"
                className={`catalog-filter-btn${category === '' ? ' catalog-filter-btn--active' : ''}`}
                onClick={() => changeCategory('')}
              >
                <span>Toutes les catégories</span>
              </button>
            </li>
            {(categoriesQuery.data?.categories ?? []).map((cat) => (
              <li key={cat.uuid}>
                <button
                  type="button"
                  className={`catalog-filter-btn${category === cat.uuid ? ' catalog-filter-btn--active' : ''}`}
                  onClick={() => changeCategory(cat.uuid)}
                >
                  <span>{cat.title}</span>
                  <span className="catalog-filter-count">{cat.itemCount}</span>
                </button>
              </li>
            ))}
          </ul>
        </aside>

        <div>
          <div className="catalog-toolbar">
            <label className="catalog-count" htmlFor="items-sort">
              {isLoading ? 'Chargement…' : totalLabel}
            </label>
            <select
              id="items-sort"
              className="catalog-sort"
              value={sort}
              onChange={(event) => changeSort(event.target.value)}
              aria-label="Trier les articles"
            >
              <option value="recent">Plus récents</option>
              <option value="price_asc">Prix croissant</option>
              <option value="price_desc">Prix décroissant</option>
            </select>
          </div>

          <div className="catalog-chips" role="group" aria-label="Filtrer par catégorie">
            <button
              type="button"
              className={`chip${category === '' ? ' chip--active' : ''}`}
              onClick={() => changeCategory('')}
            >
              Toutes
            </button>
            {(categoriesQuery.data?.categories ?? []).map((cat) => (
              <button
                key={cat.uuid}
                type="button"
                className={`chip${category === cat.uuid ? ' chip--active' : ''}`}
                onClick={() => changeCategory(cat.uuid)}
              >
                {cat.title}
              </button>
            ))}
          </div>

          <ItemGrid
            items={data?.items ?? []}
            loading={isLoading}
            emptyTitle="Aucun article"
            emptyDescription={
              q !== ''
                ? `Aucun résultat pour « ${q} ». Essayez un autre terme.`
                : 'Aucun article dans cette sélection pour le moment.'
            }
          />
          {data && <Pagination page={data.page} pages={data.pages} onChange={setPage} />}
        </div>
      </div>
    </div>
  );
}
