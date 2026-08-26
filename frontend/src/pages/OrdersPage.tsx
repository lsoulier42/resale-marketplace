import { Package } from 'lucide-react';
import { OrderCard } from '../components/domain/OrderCard';
import { EmptyState } from '../components/ui/EmptyState';
import { Skeleton } from '../components/ui/Skeleton';
import { useOrders } from '../hooks/useCustomer';

export function OrdersPage() {
  const { data, isLoading, isError } = useOrders();

  return (
    <div>
      <h1 className="page-title">Mes commandes</h1>
      <p className="page-subtitle">Suivez vos achats et leurs statuts.</p>

      {isError && (
        <div className="card" style={{ padding: '1.2rem', textAlign: 'center' }}>
          <p className="text-muted">Impossible de charger vos commandes.</p>
        </div>
      )}

      {isLoading ? (
        <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          {Array.from({ length: 3 }, (_, index) => (
            <div key={index} className="card" style={{ padding: '1rem', display: 'flex', gap: '1rem' }}>
              <Skeleton height={72} borderRadius={14} width={72} />
              <div style={{ flex: 1 }}>
                <Skeleton height={18} width="55%" />
                <Skeleton height={14} width="35%" />
              </div>
            </div>
          ))}
        </div>
      ) : data && data.orders.length > 0 ? (
        <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          {data.orders.map((order) => (
            <OrderCard key={order.uuid} order={order} />
          ))}
        </div>
      ) : (
        <EmptyState
          icon={<Package size={28} />}
          title="Aucune commande"
          description="Vos commandes apparaîtront ici dès votre premier achat."
        />
      )}
    </div>
  );
}
