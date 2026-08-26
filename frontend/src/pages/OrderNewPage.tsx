import { useState, type FormEvent } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { ArrowLeft, ShoppingBag } from 'lucide-react';
import { GlassCard } from '../components/ui/GlassCard';
import { Button } from '../components/ui/Button';
import { Field } from '../components/ui/Field';
import { Skeleton } from '../components/ui/Skeleton';
import { ItemImage } from '../components/domain/ItemImage';
import { useToast } from '../components/ui/useToast';
import { useItem } from '../hooks/useCatalog';
import { useAddresses, useCreateOrder } from '../hooks/useCustomer';
import { formatPrice } from '../utils/format';

export function OrderNewPage() {
  const { itemUuid = '' } = useParams();
  const navigate = useNavigate();
  const toast = useToast();
  const { data: itemData, isLoading, isError } = useItem(itemUuid);
  const addressesQuery = useAddresses();
  const createOrder = useCreateOrder();

  const [addressUuid, setAddressUuid] = useState('');
  const [shippingFee, setShippingFee] = useState('');

  const item = itemData?.item;

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault();
    if (!item) {
      return;
    }
    try {
      const result = await createOrder.mutateAsync({
        itemUuid: item.uuid,
        addressUuid: addressUuid !== '' ? addressUuid : undefined,
        shippingFee: shippingFee.trim() !== '' ? shippingFee.trim() : undefined,
      });
      if (result.checkoutUrl) {
        // Paiement hébergé par Stripe : redirection complète hors SPA.
        window.location.assign(result.checkoutUrl);
        return;
      }
      toast.success('Commande passée !');
      navigate(`/orders/${result.order.uuid}`, { replace: true });
    } catch (error) {
      toast.error(error instanceof Error ? error.message : 'Commande impossible.');
    }
  };

  if (isLoading) {
    return (
      <div className="glass-card" style={{ padding: '1.6rem' }}>
        <Skeleton height={24} width={220} />
        <Skeleton height={140} borderRadius={16} />
      </div>
    );
  }

  if (isError || !item || item.isSold) {
    return (
      <GlassCard style={{ padding: '2rem', textAlign: 'center' }}>
        <p className="text-muted">Cet article n'est plus disponible.</p>
        <Link to="/items" className="btn btn-glass mt-2">
          <ArrowLeft size={16} /> Retour aux articles
        </Link>
      </GlassCard>
    );
  }

  const fee = Number.parseFloat(shippingFee);
  const feeValue = Number.isFinite(fee) && fee >= 0 ? fee : 0;
  const total = Number.parseFloat(item.price) + feeValue;

  return (
    <div>
      <Link to={`/items/${item.uuid}`} className="btn btn-ghost btn-sm mb-2">
        <ArrowLeft size={15} /> Retour à l'article
      </Link>

      <h1 className="page-title">Passer commande</h1>

      <div className="item-detail">
        <GlassCard style={{ padding: '1.5rem' }}>
          <div className="order-detail-item">
            <ItemImage src={item.medias[0] ?? ''} alt={item.title} className="order-card-image" />
            <div style={{ flex: 1 }}>
              <p className="order-card-title fw-bold">{item.title}</p>
              <p className="text-muted text-small">
                {item.seller.displayName ?? 'Vendeur·se'} · {item.category.title}
              </p>
              <p className="item-card-price mt-1">{formatPrice(item.price)}</p>
            </div>
          </div>

          <form onSubmit={handleSubmit} className="mt-3">
            <Field
              label="Adresse de livraison"
              htmlFor="order-address"
              hint="Optionnel — à convenir avec le vendeur·se."
            >
              <select
                id="order-address"
                value={addressUuid}
                onChange={(event) => setAddressUuid(event.target.value)}
              >
                <option value="">À convenir avec le vendeur·se</option>
                {(addressesQuery.data?.addresses ?? []).map((address) => (
                  <option key={address.uuid} value={address.uuid}>
                    {address.name} — {address.addressLine}, {address.zipCode} {address.city}
                  </option>
                ))}
              </select>
            </Field>
            <Field label="Frais de livraison (€)" htmlFor="order-fee" hint="Laissé vide si inclus.">
              <input
                id="order-fee"
                type="number"
                min="0"
                step="0.01"
                value={shippingFee}
                onChange={(event) => setShippingFee(event.target.value)}
                placeholder="0.00"
              />
            </Field>

            <div className="order-detail-lines">
              <div className="flex-between">
                <span className="text-muted">Sous-total</span>
                <span>{formatPrice(item.price)}</span>
              </div>
              <div className="flex-between">
                <span className="text-muted">Livraison</span>
                <span>{formatPrice(feeValue.toFixed(2))}</span>
              </div>
              <div className="flex-between fw-bold">
                <span>Total</span>
                <span>{formatPrice(total.toFixed(2))}</span>
              </div>
            </div>

            <Button type="submit" className="mt-3" style={{ width: '100%' }} disabled={createOrder.isPending}>
              <ShoppingBag size={17} />
              {createOrder.isPending ? 'Commande en cours…' : 'Confirmer la commande'}
            </Button>
            <p className="text-muted text-small mt-2" style={{ textAlign: 'center' }}>
              Le paiement est sécurisé par Stripe.
            </p>
          </form>
        </GlassCard>
      </div>
    </div>
  );
}
