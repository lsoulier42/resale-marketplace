import { Link } from 'react-router-dom';
import { Users } from 'lucide-react';
import { GlassCard } from '../ui/GlassCard';
import { Rating } from '../ui/Rating';
import { ItemImage } from './ItemImage';
import type { SellerData } from '../../api/types';

/** Carte vendeur·se : avatar, nom, note moyenne, nombre d'articles. */
export function SellerCard({ seller }: { seller: SellerData }) {
  return (
    <GlassCard hover className="seller-card">
      <Link to={`/sellers/${seller.uuid}`} className="seller-card-link">
        <div className="seller-card-avatar">
          <ItemImage
            src={seller.avatarUrl ?? ''}
            alt={seller.displayName ?? 'Vendeur·se'}
            className="seller-card-image"
          />
        </div>
        <h3 className="seller-card-name">
          {seller.displayName ?? 'Vendeur·se'}
        </h3>
        {seller.bio && (
          <p className="text-muted text-small seller-card-bio">{seller.bio}</p>
        )}
        <div className="seller-card-meta">
          {seller.reviewAvg !== null ? (
            <Rating value={seller.reviewAvg} readOnly />
          ) : (
            <span className="badge badge-muted">
              <Users size={12} /> Nouveau·elle
            </span>
          )}
          <span className="text-muted text-small">
            {seller.itemCount} article{seller.itemCount > 1 ? 's' : ''}
          </span>
        </div>
      </Link>
    </GlassCard>
  );
}
