import type { OrderStatus } from '../../api/types';

const STATUS_CONFIG: Record<OrderStatus, { label: string; className: string }> = {
  pending_payment: { label: 'En attente de paiement', className: 'badge-amber' },
  paid: { label: 'Payé', className: 'badge-mauve' },
  shipped: { label: 'Expédié', className: 'badge-pink' },
  delivered: { label: 'Livré', className: 'badge-green' },
  cancelled: { label: 'Annulé', className: 'badge-red' },
};

interface StatusBadgeProps {
  status: OrderStatus;
}

/** Pastille pastel du statut de commande (libellés français). */
export function StatusBadge({ status }: StatusBadgeProps) {
  const config = STATUS_CONFIG[status];
  return <span className={`badge ${config.className}`}>{config.label}</span>;
}
