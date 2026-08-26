import { Star, Package } from 'lucide-react';
import { Card } from '../components/ui/Card';
import { Rating } from '../components/ui/Rating';
import { EmptyState } from '../components/ui/EmptyState';
import { Skeleton } from '../components/ui/Skeleton';
import { useReviews } from '../hooks/useCatalog';
import { formatDate } from '../utils/format';

export function ReviewsPage() {
  const { data, isLoading, isError } = useReviews();

  return (
    <div>
      <h1 className="page-title">Avis</h1>
      <p className="page-subtitle">Ce que les acheteur·ses disent des vendeur·ses.</p>

      {isError && (
        <div className="card" style={{ padding: '1.2rem', textAlign: 'center' }}>
          <p className="text-muted">Impossible de charger les avis.</p>
        </div>
      )}

      {isLoading ? (
        <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          {Array.from({ length: 2 }, (_, index) => (
            <div key={index} className="card" style={{ padding: '1.3rem' }}>
              <Skeleton height={20} width={120} />
              <Skeleton height={14} width="80%" />
              <Skeleton height={14} width="50%" />
            </div>
          ))}
        </div>
      ) : data && data.reviews.length > 0 ? (
        <div className="reviews-list">
          {data.reviews.map((review) => (
            <Card key={review.uuid} className="review-card">
              <div className="review-card-header">
                <Rating value={review.star} readOnly />
                <span className="text-muted text-small">{formatDate(review.createdAt)}</span>
              </div>
              {review.comment && <p className="review-card-comment">{review.comment}</p>}
              <div className="review-card-meta">
                <span className="review-card-from">
                  {review.fromUser.displayName ?? 'Acheteur·se'} →{' '}
                  {review.toUser.displayName ?? 'Vendeur·se'}
                </span>
                <span className="text-muted text-small" style={{ marginLeft: 'auto', display: 'inline-flex', alignItems: 'center', gap: '0.35rem' }}>
                  <Package size={13} /> {review.orderReference}
                </span>
              </div>
            </Card>
          ))}
        </div>
      ) : (
        <EmptyState
          icon={<Star size={28} />}
          title="Aucun avis pour le moment"
          description="Les avis apparaîtront après les premières commandes."
        />
      )}
    </div>
  );
}
