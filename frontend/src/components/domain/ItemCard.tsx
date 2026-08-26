import { Link } from 'react-router-dom';
import { GlassCard } from '../ui/GlassCard';
import { ItemImage } from './ItemImage';
import { formatPrice } from '../../utils/format';
import type { ItemCardData } from '../../api/types';

/** Carte article : photo, titre, prix, catégorie, vendeur·se. */
export function ItemCard({ item }: { item: ItemCardData }) {
  return (
    <GlassCard hover className="item-card">
      <Link to={`/items/${item.uuid}`} className="item-card-link">
        <div className="item-card-media">
          <ItemImage src={item.medias[0] ?? ''} alt={item.title} className="item-card-image" />
          {item.isSold && <span className="badge badge-muted item-card-sold">Vendu</span>}
        </div>
        <div className="item-card-body">
          <h3 className="item-card-title">{item.title}</h3>
          <div className="flex-between">
            <span className="item-card-price">{formatPrice(item.price)}</span>
          </div>
          <div className="item-card-meta">
            <span className="badge badge-pink">{item.category.title}</span>
            <span className="text-muted text-small">
              {item.seller.displayName ?? 'Vendeur·se'}
            </span>
          </div>
        </div>
      </Link>
    </GlassCard>
  );
}
