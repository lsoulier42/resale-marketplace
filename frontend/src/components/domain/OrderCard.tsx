import { Link } from 'react-router-dom';
import { Package } from 'lucide-react';
import { GlassCard } from '../ui/GlassCard';
import { StatusBadge } from '../ui/StatusBadge';
import { ItemImage } from './ItemImage';
import { formatDate, formatPrice } from '../../utils/format';
import type { OrderData } from '../../api/types';

/** Carte commande : article, référence, statut, total, date. */
export function OrderCard({ order }: { order: OrderData }) {
  return (
    <GlassCard hover>
      <Link to={`/orders/${order.uuid}`} className="order-card-link">
        <ItemImage
          src={order.item.medias[0] ?? ''}
          alt={order.item.title}
          className="order-card-image"
        />
        <div className="order-card-body">
          <div className="flex-between" style={{ alignItems: 'flex-start', gap: '0.8rem' }}>
            <div>
              <h3 className="order-card-title">{order.item.title}</h3>
              <p className="text-muted text-small">
                <Package size={12} style={{ verticalAlign: '-2px' }} /> {order.reference}
              </p>
            </div>
            <StatusBadge status={order.status} />
          </div>
          <div className="order-card-footer">
            <span className="text-muted text-small">{formatDate(order.createdAt)}</span>
            <span className="item-card-price">{formatPrice(order.totalPrice)}</span>
          </div>
        </div>
      </Link>
    </GlassCard>
  );
}
