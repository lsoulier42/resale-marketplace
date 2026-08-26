import { useEffect, useState, type FormEvent } from 'react';
import { Link, useParams, useSearchParams } from 'react-router-dom';
import { ArrowLeft, CreditCard, MapPin, Truck } from 'lucide-react';
import { Card } from '../components/ui/Card';
import { StatusBadge } from '../components/ui/StatusBadge';
import { Button } from '../components/ui/Button';
import { Field } from '../components/ui/Field';
import { Rating } from '../components/ui/Rating';
import { ItemImage } from '../components/domain/ItemImage';
import { Skeleton } from '../components/ui/Skeleton';
import { useToast } from '../components/ui/useToast';
import { useCheckout, useCreateReview, useOrder, useUpdateOrderStatus } from '../hooks/useCustomer';
import { formatDate, formatPrice } from '../utils/format';
import type { OrderStatus } from '../api/types';

const STATUS_STEPS: OrderStatus[] = ['pending_payment', 'paid', 'shipped', 'delivered'];

export function OrderDetailPage() {
  const { uuid = '' } = useParams();
  const [searchParams, setSearchParams] = useSearchParams();
  const toast = useToast();
  const { data, isLoading, isError } = useOrder(uuid);
  const updateStatus = useUpdateOrderStatus(uuid);
  const createReview = useCreateReview(uuid);
  const checkout = useCheckout();

  // Retour depuis la page Stripe (success/cancel) ou tentative d'onboarding
  // non finalisée — on informe l'utilisateur·ice et on nettoie l'URL.
  useEffect(() => {
    const outcome = searchParams.get('stripe');
    if (outcome === null) {
      return;
    }
    if (outcome === 'success') {
      toast.success('Paiement reçu ! Le statut de la commande va être mis à jour.');
    } else if (outcome === 'cancel') {
      toast.error('Paiement annulé — vous pouvez réessayer.');
    } else if (outcome === 'refresh') {
      toast.error('Onboarding Stripe interrompu — réessayez.');
    }
    setSearchParams({}, { replace: true });
  }, [searchParams, setSearchParams, toast]);

  const [nextStatus, setNextStatus] = useState('');
  const [trackingNumber, setTrackingNumber] = useState('');
  const [shippingProvider, setShippingProvider] = useState('');
  const [reviewStar, setReviewStar] = useState(5);
  const [reviewComment, setReviewComment] = useState('');

  const order = data?.order;

  if (isLoading) {
    return (
      <div className="card" style={{ padding: '1.6rem', display: 'flex', flexDirection: 'column', gap: '1rem' }}>
        <Skeleton height={24} width={240} />
        <Skeleton height={120} borderRadius={16} />
      </div>
    );
  }

  if (isError || !order) {
    return (
      <Card style={{ padding: '2rem', textAlign: 'center' }}>
        <p className="text-muted">Commande introuvable.</p>
        <Link to="/orders" className="btn btn-secondary mt-2">
          <ArrowLeft size={16} /> Retour à mes commandes
        </Link>
      </Card>
    );
  }

  const handleStatusSubmit = async (event: FormEvent) => {
    event.preventDefault();
    try {
      await updateStatus.mutateAsync({
        status: nextStatus,
        trackingNumber: trackingNumber.trim() !== '' ? trackingNumber.trim() : undefined,
        shippingProvider: shippingProvider.trim() !== '' ? shippingProvider.trim() : undefined,
      });
      toast.success('Statut mis à jour.');
      setNextStatus('');
    } catch (error) {
      toast.error(error instanceof Error ? error.message : 'Mise à jour impossible.');
    }
  };

  const handlePay = async () => {
    try {
      const { checkoutUrl } = await checkout.mutateAsync(uuid);
      if (checkoutUrl) {
        window.location.assign(checkoutUrl);
        return;
      }
      toast.error('Le paiement en ligne n\'est pas disponible pour le moment.');
    } catch (error) {
      toast.error(error instanceof Error ? error.message : 'Paiement impossible.');
    }
  };

  const handleReviewSubmit = async (event: FormEvent) => {
    event.preventDefault();
    try {
      await createReview.mutateAsync({ star: reviewStar, comment: reviewComment });
      toast.success('Merci pour votre avis !');
      setReviewComment('');
    } catch (error) {
      toast.error(error instanceof Error ? error.message : 'Envoi impossible.');
    }
  };

  const currentStep = STATUS_STEPS.indexOf(order.status);

  return (
    <div>
      <Link to="/orders" className="btn btn-ghost btn-sm mb-2">
        <ArrowLeft size={15} /> Retour à mes commandes
      </Link>

      <Card style={{ padding: '1.8rem', marginBottom: '1.4rem' }}>
        <div className="flex-between" style={{ alignItems: 'flex-start', flexWrap: 'wrap', gap: '0.8rem' }}>
          <div>
            <h1 className="page-title" style={{ marginBottom: '0.3rem' }}>
              {order.reference}
            </h1>
            <p className="text-muted text-small">{formatDate(order.createdAt)}</p>
          </div>
          <StatusBadge status={order.status} />
        </div>

        {/* Timeline des statuts */}
        <div className="order-timeline mt-3" aria-label="Avancement de la commande">
          {STATUS_STEPS.map((step, index) => (
            <div
              key={step}
              className={`order-timeline-step${index <= currentStep ? ' order-timeline-step--done' : ''}`}
            >
              <span className="order-timeline-dot" />
              <span className="order-timeline-label">{step === 'pending_payment' ? 'Commandée' : labelOf(step)}</span>
            </div>
          ))}
        </div>
      </Card>

      <div className="item-detail" style={{ marginBottom: '1.4rem' }}>
        <Card style={{ padding: '1.5rem' }}>
          <div className="order-detail-item">
            <ItemImage src={order.item.medias[0] ?? ''} alt={order.item.title} className="order-card-image" />
            <div style={{ flex: 1 }}>
              <Link to={`/items/${order.item.uuid}`} className="order-card-title" style={{ fontWeight: 700 }}>
                {order.item.title}
              </Link>
              <p className="text-muted text-small mt-1">
                Vendeur·se : {order.seller.displayName ?? 'Vendeur·se'}
              </p>
              <p className="item-card-price mt-1">{formatPrice(order.item.price)}</p>
            </div>
          </div>

          <div className="order-detail-lines mt-2">
            <div className="flex-between">
              <span className="text-muted">Sous-total</span>
              <span>{formatPrice(order.item.price)}</span>
            </div>
            <div className="flex-between">
              <span className="text-muted">Livraison</span>
              <span>{formatPrice(order.shippingFee)}</span>
            </div>
            <div className="flex-between fw-bold">
              <span>Total</span>
              <span>{formatPrice(order.totalPrice)}</span>
            </div>
          </div>

          {/* Paiement Stripe */}
          {order.canPay && (
            <Button
              onClick={handlePay}
              className="mt-3"
              style={{ width: '100%' }}
              disabled={checkout.isPending}
            >
              <CreditCard size={16} />
              {checkout.isPending ? 'Redirection vers Stripe…' : 'Payer avec Stripe'}
            </Button>
          )}
        </Card>

        <div style={{ display: 'flex', flexDirection: 'column', gap: '1.4rem' }}>
          <Card style={{ padding: '1.5rem' }}>
            <h2 style={{ fontSize: '1.05rem', display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
              <MapPin size={18} style={{ color: 'var(--color-accent)' }} /> Livraison
            </h2>
            {order.shippingAddress ? (
              <div className="text-muted mt-2">
                <p className="fw-bold" style={{ color: 'var(--color-text)' }}>{order.shippingAddress.name}</p>
                <p>{order.shippingAddress.addressLine}</p>
                <p>
                  {order.shippingAddress.zipCode} {order.shippingAddress.city}
                </p>
                <p>{order.shippingAddress.country}</p>
              </div>
            ) : (
              <p className="text-muted mt-2">À convenir avec le vendeur·se.</p>
            )}
            {order.trackingNumber && (
              <p className="text-muted text-small mt-2" style={{ display: 'flex', alignItems: 'center', gap: '0.4rem' }}>
                <Truck size={14} />
                {order.shippingProvider ?? 'Transporteur'} — {order.trackingNumber}
              </p>
            )}
          </Card>

          {/* Changement de statut (vendeur·se / admin) */}
          {order.canUpdateStatus && order.allowedTransitions.length > 0 && (
            <Card style={{ padding: '1.5rem' }}>
              <h2 style={{ fontSize: '1.05rem' }}>Mettre à jour le statut</h2>
              <form onSubmit={handleStatusSubmit} className="mt-2">
                <Field label="Nouveau statut" htmlFor="order-next-status">
                  <select
                    id="order-next-status"
                    value={nextStatus}
                    onChange={(event) => setNextStatus(event.target.value)}
                    required
                  >
                    <option value="" disabled>
                      Choisir…
                    </option>
                    {order.allowedTransitions.map((transition) => (
                      <option key={transition.value} value={transition.value}>
                        {transition.label}
                      </option>
                    ))}
                  </select>
                </Field>
                {nextStatus === 'shipped' && (
                  <>
                    <Field label="Numéro de suivi" htmlFor="order-tracking">
                      <input
                        id="order-tracking"
                        value={trackingNumber}
                        onChange={(event) => setTrackingNumber(event.target.value)}
                        placeholder="Ex. TS-1234"
                      />
                    </Field>
                    <Field label="Transporteur" htmlFor="order-provider">
                      <input
                        id="order-provider"
                        value={shippingProvider}
                        onChange={(event) => setShippingProvider(event.target.value)}
                        placeholder="Ex. Colissimo"
                      />
                    </Field>
                  </>
                )}
                <Button type="submit" disabled={updateStatus.isPending}>
                  {updateStatus.isPending ? 'Mise à jour…' : 'Enregistrer'}
                </Button>
              </form>
            </Card>
          )}

          {/* Avis client */}
          {order.canReview && (
            <Card style={{ padding: '1.5rem' }}>
              <h2 style={{ fontSize: '1.05rem' }}>Laisser un avis</h2>
              <form onSubmit={handleReviewSubmit} className="mt-2">
                <Field label="Note" htmlFor="review-star">
                  <Rating value={reviewStar} onChange={setReviewStar} />
                </Field>
                <Field label="Commentaire" htmlFor="review-comment">
                  <textarea
                    id="review-comment"
                    rows={3}
                    value={reviewComment}
                    onChange={(event) => setReviewComment(event.target.value)}
                    placeholder="Votre expérience…"
                  />
                </Field>
                <Button type="submit" disabled={createReview.isPending}>
                  {createReview.isPending ? 'Envoi…' : 'Publier mon avis'}
                </Button>
              </form>
            </Card>
          )}

          {order.hasReview && (
            <Card style={{ padding: '1.2rem 1.5rem' }}>
              <p className="text-muted">✓ Merci ! Vous avez déjà laissé un avis pour cette commande.</p>
            </Card>
          )}
        </div>
      </div>
    </div>
  );
}

function labelOf(status: OrderStatus): string {
  switch (status) {
    case 'pending_payment':
      return 'Commandée';
    case 'paid':
      return 'Payée';
    case 'shipped':
      return 'Expédiée';
    case 'delivered':
      return 'Livrée';
    case 'cancelled':
      return 'Annulée';
  }
}
