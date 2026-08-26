import { useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { ShoppingBag, ArrowLeft } from 'lucide-react';
import { GlassCard } from '../components/ui/GlassCard';
import { ItemImage } from '../components/domain/ItemImage';
import { Skeleton } from '../components/ui/Skeleton';
import { useAuth } from '../auth/useAuth';
import { useItem } from '../hooks/useCatalog';
import { formatPrice } from '../utils/format';

export function ItemDetailPage() {
  const { uuid = '' } = useParams();
  const { user } = useAuth();
  const { data, isLoading, isError } = useItem(uuid);
  const [activeMedia, setActiveMedia] = useState(0);

  const item = data?.item;

  if (isLoading) {
    return (
      <div className="glass-card" style={{ padding: '1.6rem', display: 'flex', flexDirection: 'column', gap: '1rem' }}>
        <Skeleton height={280} borderRadius={18} />
        <Skeleton height={22} width="60%" />
        <Skeleton height={22} width="35%" />
      </div>
    );
  }

  if (isError || !item) {
    return (
      <GlassCard style={{ padding: '2rem', textAlign: 'center' }}>
        <p className="text-muted">Article introuvable.</p>
        <Link to="/items" className="btn btn-glass mt-2">
          <ArrowLeft size={16} /> Retour aux articles
        </Link>
      </GlassCard>
    );
  }

  const medias = item.medias.length > 0 ? item.medias : [''];

  return (
    <div>
      <Link to="/items" className="btn btn-ghost btn-sm mb-2">
        <ArrowLeft size={15} /> Retour aux articles
      </Link>

      <div className="item-detail">
        <div className="item-detail-gallery">
          <ItemImage
            src={medias[activeMedia] ?? ''}
            alt={item.title}
            className="item-detail-main-image"
          />
          {medias.length > 1 && (
            <div className="item-detail-thumbs">
              {medias.map((media, index) => (
                <button
                  key={media}
                  type="button"
                  className={`item-detail-thumb-btn${index === activeMedia ? ' item-detail-thumb-btn--active' : ''}`}
                  onClick={() => setActiveMedia(index)}
                  aria-label={`Afficher la photo ${index + 1}`}
                >
                  <ItemImage src={media} alt={`${item.title} — photo ${index + 1}`} className="item-detail-thumb" />
                </button>
              ))}
            </div>
          )}
        </div>

        <GlassCard style={{ padding: '1.8rem' }}>
          <div className="flex-between" style={{ alignItems: 'flex-start' }}>
            <span className="badge badge-pink">{item.category.title}</span>
            {item.isSold && <span className="badge badge-muted">Vendu</span>}
          </div>
          <h1 style={{ fontSize: '1.4rem', marginTop: '0.9rem' }}>{item.title}</h1>
          <p className="item-card-price" style={{ marginTop: '0.6rem', fontSize: '1.5rem' }}>
            {formatPrice(item.price)}
          </p>

          {item.description && (
            <p className="text-muted mt-2" style={{ whiteSpace: 'pre-line' }}>
              {item.description}
            </p>
          )}

          <Link to={`/sellers/${item.seller.uuid}`} className="btn btn-glass btn-sm mt-2">
            Vendeur·se : {item.seller.displayName ?? 'Vendeur·se'}
          </Link>

          <div className="mt-3">
            {item.isSold ? (
              <span className="badge badge-muted">Cet article a déjà trouvé preneur</span>
            ) : user ? (
              <Link to={`/orders/new/${item.uuid}`} className="btn btn-primary">
                <ShoppingBag size={17} /> Commander
              </Link>
            ) : (
              <Link to="/login" className="btn btn-primary">
                Se connecter pour commander
              </Link>
            )}
          </div>
        </GlassCard>
      </div>
    </div>
  );
}
