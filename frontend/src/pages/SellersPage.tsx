import { Users } from 'lucide-react';
import { SellerCard } from '../components/domain/SellerCard';
import { EmptyState } from '../components/ui/EmptyState';
import { Skeleton } from '../components/ui/Skeleton';
import { useSellers } from '../hooks/useCatalog';

export function SellersPage() {
  const { data, isLoading, isError } = useSellers();

  return (
    <div>
      <h1 className="page-title">Vendeur·ses</h1>
      <p className="page-subtitle">Découvrez les vendeur·ses derrière chaque article.</p>

      {isError && (
        <div className="glass-card" style={{ padding: '1.2rem', textAlign: 'center' }}>
          <p className="text-muted">Impossible de charger les vendeur·ses.</p>
        </div>
      )}

      {isLoading ? (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(240px, 1fr))', gap: '1.2rem' }}>
          {Array.from({ length: 3 }, (_, index) => (
            <div key={index} className="glass-card" style={{ padding: '1.6rem', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '0.6rem' }}>
              <Skeleton height={86} borderRadius="50%" width={86} />
              <Skeleton height={18} width="50%" />
              <Skeleton height={14} width="75%" />
            </div>
          ))}
        </div>
      ) : data && data.sellers.length > 0 ? (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(240px, 1fr))', gap: '1.2rem' }}>
          {data.sellers.map((seller) => (
            <SellerCard key={seller.uuid} seller={seller} />
          ))}
        </div>
      ) : (
        <EmptyState icon={<Users size={28} />} title="Aucun·e vendeur·se pour le moment" />
      )}
    </div>
  );
}
