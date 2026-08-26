import { Link } from 'react-router-dom';
import { ItemImage } from './ItemImage';
import { formatPrice } from '../../utils/format';
import type { ItemCardData } from '../../api/types';

/** Carte article : photo, titre, prix, catégorie et vendeur·se — sans encadré. */
export function ItemCard({ item }: { item: ItemCardData }) {
  return (
    <article className="item-card">
      <Link to={`/items/${item.uuid}`} className="item-card-link">
        <div className="item-card-media">
          <ItemImage src={item.medias[0] ?? ''} alt={item.title} className="item-card-image" />
          {item.isSold && <span className="badge badge-neutral item-card-sold">Vendu</span>}
        </div>
        <div className="item-card-body">
          <h3 className="item-card-title">{item.title}</h3>
          <span className="item-card-price">{formatPrice(item.price)}</span>
          <div className="item-card-meta">
            <span>{item.category.title}</span>
            <span>{item.seller.displayName ?? 'Vendeur·se'}</span>
          </div>
        </div>
      </Link>
    </article>
  );
}
