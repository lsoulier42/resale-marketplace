import { useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { ArrowLeft, ShoppingBag } from 'lucide-react';
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
      <div className="card item-detail-page" style={{ padding: '1.6rem', display: 'flex', flexDirection: 'column', gap: '1rem' }}>
        <Skeleton height={300} borderRadius={8} />
        <Skeleton height={22} width="60%" />
        <Skeleton height={22} width="35%" />
      </div>
    );
  }

  if (isError || !item) {
    return (
      <div className="item-detail-page">
        <p className="text-muted">Article introuvable.</p>
        <Link to="/items" className="btn btn-secondary mt-2">
          <ArrowLeft size={16} /> Retour aux articles
        </Link>
      </div>
    );
  }

  const medias = item.medias.length > 0 ? item.medias : [''];

  const buyCta = item.isSold ? (
    <span className="badge badge-neutral">Cet article a déjà trouvé preneur</span>
  ) : user ? (
    <Link to={`/orders/new/${item.uuid}`} className="btn btn-primary btn-lg btn-block">
      <ShoppingBag size={18} /> Acheter
    </Link>
  ) : (
    <Link to="/login" className="btn btn-primary btn-lg btn-block">
      Se connecter pour acheter
    </Link>
  );

  return (
    <div className="item-detail-page">
      <Link to="/items" className="btn btn-ghost btn-sm mb-2">
        <ArrowLeft size={15} /> Retour aux articles
      </Link>

      <div className="item-detail">
        <div className="item-detail-gallery">
          <div className="item-detail-main">
            <ItemImage
              src={medias[activeMedia] ?? ''}
              alt={item.title}
              className="item-detail-main-image"
            />
            {item.isSold && (
              <span className="badge badge-neutral item-card-sold">Vendu</span>
            )}
          </div>
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

        <div className="item-detail-info">
          <Link to={`/categories/${item.category.uuid}`} className="item-detail-category">
            {item.category.title}
          </Link>
          <h1 className="item-detail-title">{item.title}</h1>
          <p className="item-detail-price">{formatPrice(item.price)}</p>

          {item.description && (
            <p className="item-detail-description">{item.description}</p>
          )}

          <div className="item-detail-actions">{buyCta}</div>

          <Link to={`/sellers/${item.seller.uuid}`} className="item-detail-seller">
            <div className="item-detail-seller-avatar">
              <ItemImage
                src={item.seller.avatarUrl ?? ''}
                alt={item.seller.displayName ?? 'Vendeur·se'}
                className="seller-card-image"
              />
            </div>
            <div style={{ flex: 1, minWidth: 0 }}>
              <p className="item-detail-seller-name">
                {item.seller.displayName ?? 'Vendeur·se'}
              </p>
              <p className="item-detail-seller-meta">Voir son profil</p>
            </div>
            <ArrowLeft size={16} style={{ transform: 'rotate(180deg)', color: 'var(--color-text-faint)' }} />
          </Link>
        </div>
      </div>

      {/* Barre d'achat sticky (mobile) */}
      <div className="item-detail-mobile-bar">
        <span className="item-detail-price">{formatPrice(item.price)}</span>
        {item.isSold ? (
          <span className="badge badge-neutral" style={{ flex: 1, justifyContent: 'center', padding: '0.6rem' }}>
            Vendu
          </span>
        ) : (
          <Link
            to={user ? `/orders/new/${item.uuid}` : '/login'}
            className="btn btn-primary"
          >
            <ShoppingBag size={17} /> Acheter
          </Link>
        )}
      </div>
    </div>
  );
}
