import type { OrderStatus } from '../../api/types';

const STATUS_CONFIG: Record<OrderStatus, { label: string; className: string }> = {
  pending_payment: { label: 'En attente de paiement', className: 'badge-warning' },
  paid: { label: 'Payé', className: 'badge-accent' },
  shipped: { label: 'Expédié', className: 'badge-accent' },
  delivered: { label: 'Livré', className: 'badge-success' },
  cancelled: { label: 'Annulé', className: 'badge-danger' },
};

interface StatusBadgeProps {
  status: OrderStatus;
}

/** Pastille pastel du statut de commande (libellés français). */
export function StatusBadge({ status }: StatusBadgeProps) {
  const config = STATUS_CONFIG[status];
  return <span className={`badge ${config.className}`}>{config.label}</span>;
}
