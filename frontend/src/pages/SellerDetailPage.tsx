import { Link, useParams } from 'react-router-dom';
import { ArrowLeft, Package } from 'lucide-react';
import { Card } from '../components/ui/Card';
import { ItemGrid } from '../components/domain/ItemGrid';
import { ItemImage } from '../components/domain/ItemImage';
import { Rating } from '../components/ui/Rating';
import { Skeleton } from '../components/ui/Skeleton';
import { EmptyState } from '../components/ui/EmptyState';
import { useSeller } from '../hooks/useCatalog';
import { formatDate } from '../utils/format';

export function SellerDetailPage() {
  const { uuid = '' } = useParams();
  const { data, isLoading, isError } = useSeller(uuid);

  if (isLoading) {
    return (
      <div className="card" style={{ padding: '1.6rem', display: 'flex', gap: '1.2rem', alignItems: 'center' }}>
        <Skeleton height={110} borderRadius="50%" width={110} />
        <div style={{ flex: 1 }}>
          <Skeleton height={22} width="45%" />
          <Skeleton height={16} width="70%" />
        </div>
      </div>
    );
  }

  if (isError || !data) {
    return (
      <Card style={{ padding: '2rem', textAlign: 'center' }}>
        <p className="text-muted">Vendeur·se introuvable.</p>
        <Link to="/sellers" className="btn btn-secondary mt-2">
          <ArrowLeft size={16} /> Retour aux vendeur·ses
        </Link>
      </Card>
    );
  }

  const { seller, items, reviews } = data;

  return (
    <div>
      <Link to="/sellers" className="btn btn-ghost btn-sm mb-2">
        <ArrowLeft size={15} /> Retour aux vendeur·ses
      </Link>

      <Card style={{ padding: '1.8rem', marginBottom: '1.6rem' }}>
        <div className="seller-detail">
          <div className="seller-detail-avatar">
            <ItemImage src={seller.avatarUrl ?? ''} alt={seller.displayName ?? 'Vendeur·se'} className="seller-card-image" />
          </div>
          <div style={{ flex: 1, minWidth: 220 }}>
            <h1 style={{ fontSize: '1.4rem' }}>
              {seller.displayName ?? 'Vendeur·se'}
            </h1>
            {seller.reviewAvg !== null ? (
              <div className="mt-1" style={{ display: 'flex', alignItems: 'center', gap: '0.6rem' }}>
                <Rating value={seller.reviewAvg} readOnly />
                <span className="text-muted text-small">
                  {seller.reviewCount} avis
                </span>
              </div>
            ) : (
              <span className="badge badge-neutral mt-1">Nouveau·elle vendeur·se</span>
            )}
            {seller.bio && <p className="text-muted mt-2">{seller.bio}</p>}
            <p className="text-muted text-small mt-2">
              {seller.itemCount} article{seller.itemCount > 1 ? 's' : ''} en boutique
            </p>
          </div>
        </div>
      </Card>

      <h2 className="mb-1" style={{ fontSize: '1.15rem' }}>
        Ses articles
      </h2>
      <ItemGrid items={items} loading={false} emptyTitle="Aucun article en boutique" />

      {reviews.length > 0 && (
        <>
          <h2 className="mt-4 mb-1" style={{ fontSize: '1.15rem' }}>
            Avis ({reviews.length})
          </h2>
          <div className="reviews-list mt-2">
            {reviews.map((review) => (
              <Card key={review.uuid} className="review-card">
                <div className="review-card-header">
                  <Rating value={review.star} readOnly />
                  <span className="text-muted text-small">{formatDate(review.createdAt)}</span>
                </div>
                {review.comment && <p className="review-card-comment">{review.comment}</p>}
                <div className="review-card-meta">
                  <Package size={14} style={{ color: 'var(--color-text-faint)' }} />
                  <span className="text-muted text-small">{review.orderReference}</span>
                </div>
              </Card>
            ))}
          </div>
        </>
      )}

      {reviews.length === 0 && (
        <div className="mt-3">
          <EmptyState icon={<Package size={28} />} title="Aucun avis pour le moment" />
        </div>
      )}
    </div>
  );
}
