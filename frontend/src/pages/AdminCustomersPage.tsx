import { useState } from 'react';
import { Trash2, Users } from 'lucide-react';
import { Card } from '../components/ui/Card';
import { Button } from '../components/ui/Button';
import { ConfirmDialog } from '../components/ui/ConfirmDialog';
import { EmptyState } from '../components/ui/EmptyState';
import { Skeleton } from '../components/ui/Skeleton';
import { useToast } from '../components/ui/useToast';
import { useAdminCustomers, useDeleteCustomer } from '../hooks/useSeller';
import type { AdminCustomerData } from '../api/types';

export function AdminCustomersPage() {
  const toast = useToast();
  const { data, isLoading, isError } = useAdminCustomers();
  const deleteCustomer = useDeleteCustomer();
  const [deleting, setDeleting] = useState<AdminCustomerData | null>(null);

  const handleDelete = async () => {
    if (!deleting) {
      return;
    }
    try {
      await deleteCustomer.mutateAsync(deleting.uuid);
      toast.success('Acheteur·se supprimé·e.');
    } catch (error) {
      toast.error(error instanceof Error ? error.message : 'Suppression impossible.');
    } finally {
      setDeleting(null);
    }
  };

  const customers = data?.customers ?? [];

  return (
    <div style={{ maxWidth: 860 }}>
      <h1 className="page-title">Acheteur·ses</h1>
      <p className="page-subtitle">Les comptes acheteur·ses de la boutique ({customers.length}).</p>

      {isError && (
        <div className="card" style={{ padding: '1.2rem', textAlign: 'center' }}>
          <p className="text-muted">Impossible de charger les acheteur·ses.</p>
        </div>
      )}

      {isLoading ? (
        <div style={{ display: 'flex', flexDirection: 'column', gap: '0.8rem' }}>
          {Array.from({ length: 3 }, (_, index) => (
            <div key={index} className="card" style={{ padding: '1rem' }}>
              <Skeleton height={18} width="50%" />
            </div>
          ))}
        </div>
      ) : customers.length === 0 ? (
        <EmptyState icon={<Users size={28} />} title="Aucun·e acheteur·se" />
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: '0.8rem' }}>
          {customers.map((customer) => (
            <Card key={customer.uuid} style={{ padding: '1rem 1.2rem' }}>
              <div className="flex-between" style={{ gap: '1rem' }}>
                <div style={{ minWidth: 0 }}>
                  <p className="fw-bold" style={{ overflow: 'hidden', textOverflow: 'ellipsis' }}>
                    {customer.displayName ?? 'Sans profil'}
                  </p>
                  <p className="text-muted text-small">{customer.email}</p>
                </div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '0.8rem', flexShrink: 0 }}>
                  <span className="badge badge-accent">
                    {customer.orderCount} commande{customer.orderCount > 1 ? 's' : ''}
                  </span>
                  <Button
                    variant="ghost"
                    size="sm"
                    onClick={() => setDeleting(customer)}
                    aria-label={`Supprimer ${customer.email}`}
                  >
                    <Trash2 size={15} style={{ color: 'var(--color-danger)' }} />
                  </Button>
                </div>
              </div>
            </Card>
          ))}
        </div>
      )}

      <ConfirmDialog
        open={deleting !== null}
        title="Supprimer cet·te acheteur·se ?"
        message={deleting ? `Le compte « ${deleting.email} » et ses commandes seront supprimés.` : ''}
        confirmLabel="Supprimer"
        danger
        onConfirm={handleDelete}
        onCancel={() => setDeleting(null)}
      />
    </div>
  );
}
