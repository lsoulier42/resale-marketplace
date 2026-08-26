import { useState, type FormEvent } from 'react';
import { MapPin, Plus, Pencil, Trash2 } from 'lucide-react';
import { Card } from '../components/ui/Card';
import { Button } from '../components/ui/Button';
import { Field } from '../components/ui/Field';
import { Modal } from '../components/ui/Modal';
import { ConfirmDialog } from '../components/ui/ConfirmDialog';
import { EmptyState } from '../components/ui/EmptyState';
import { Skeleton } from '../components/ui/Skeleton';
import { useToast } from '../components/ui/useToast';
import {
  useAddresses,
  useCreateAddress,
  useUpdateAddress,
  useDeleteAddress,
} from '../hooks/useCustomer';
import type { AddressData } from '../api/types';

interface AddressFormState {
  name: string;
  addressLine: string;
  city: string;
  zipCode: string;
  country: string;
}

const EMPTY_FORM: AddressFormState = {
  name: '',
  addressLine: '',
  city: '',
  zipCode: '',
  country: '',
};

export function AddressesPage() {
  const toast = useToast();
  const { data, isLoading, isError } = useAddresses();
  const createAddress = useCreateAddress();
  const updateAddress = useUpdateAddress();
  const deleteAddress = useDeleteAddress();

  const [modalOpen, setModalOpen] = useState(false);
  const [editing, setEditing] = useState<AddressData | null>(null);
  const [deleting, setDeleting] = useState<AddressData | null>(null);
  const [form, setForm] = useState<AddressFormState>(EMPTY_FORM);

  const openCreate = () => {
    setEditing(null);
    setForm(EMPTY_FORM);
    setModalOpen(true);
  };

  const openEdit = (address: AddressData) => {
    setEditing(address);
    setForm({
      name: address.name,
      addressLine: address.addressLine,
      city: address.city,
      zipCode: address.zipCode,
      country: address.country,
    });
    setModalOpen(true);
  };

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault();
    try {
      if (editing) {
        await updateAddress.mutateAsync({ uuid: editing.uuid, payload: form });
        toast.success('Adresse mise à jour.');
      } else {
        await createAddress.mutateAsync(form);
        toast.success('Adresse ajoutée.');
      }
      setModalOpen(false);
    } catch (error) {
      toast.error(error instanceof Error ? error.message : 'Enregistrement impossible.');
    }
  };

  const handleDelete = async () => {
    if (!deleting) {
      return;
    }
    try {
      await deleteAddress.mutateAsync(deleting.uuid);
      toast.success('Adresse supprimée.');
    } catch (error) {
      toast.error(error instanceof Error ? error.message : 'Suppression impossible.');
    } finally {
      setDeleting(null);
    }
  };

  if (isLoading) {
    return (
      <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
        {Array.from({ length: 2 }, (_, index) => (
          <div key={index} className="card" style={{ padding: '1.2rem' }}>
            <Skeleton height={18} width="40%" />
            <Skeleton height={14} width="65%" />
          </div>
        ))}
      </div>
    );
  }

  if (isError) {
    return (
      <Card style={{ padding: '1.5rem', textAlign: 'center' }}>
        <p className="text-muted">Impossible de charger vos adresses.</p>
      </Card>
    );
  }

  const addresses = data?.addresses ?? [];

  return (
    <div style={{ maxWidth: 760 }}>
      <div className="flex-between" style={{ alignItems: 'flex-end', flexWrap: 'wrap', gap: '0.8rem' }}>
        <div>
          <h1 className="page-title" style={{ marginBottom: '0.3rem' }}>
            Mes adresses
          </h1>
          <p className="text-muted text-small">Utilisées pour la livraison de vos commandes.</p>
        </div>
        <Button variant="secondary" onClick={openCreate}>
          <Plus size={16} /> Ajouter
        </Button>
      </div>

      {addresses.length === 0 ? (
        <div className="mt-2">
          <EmptyState
            icon={<MapPin size={28} />}
            title="Aucune adresse"
            description="Ajoutez votre première adresse de livraison."
            action={
              <Button size="sm" onClick={openCreate}>
                <Plus size={14} /> Ajouter une adresse
              </Button>
            }
          />
        </div>
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem', marginTop: '1.2rem' }}>
          {addresses.map((address) => (
            <Card key={address.uuid} style={{ padding: '1.3rem 1.5rem' }}>
              <div className="flex-between" style={{ alignItems: 'flex-start', gap: '1rem' }}>
                <div>
                  <p className="fw-bold">{address.name}</p>
                  <p className="text-muted text-small">{address.addressLine}</p>
                  <p className="text-muted text-small">
                    {address.zipCode} {address.city} — {address.country}
                  </p>
                </div>
                <div style={{ display: 'flex', gap: '0.5rem' }}>
                  <Button variant="ghost" size="sm" onClick={() => openEdit(address)} aria-label={`Modifier ${address.name}`}>
                    <Pencil size={15} />
                  </Button>
                  <Button variant="ghost" size="sm" onClick={() => setDeleting(address)} aria-label={`Supprimer ${address.name}`}>
                    <Trash2 size={15} style={{ color: 'var(--color-danger)' }} />
                  </Button>
                </div>
              </div>
            </Card>
          ))}
        </div>
      )}

      <Modal
        open={modalOpen}
        title={editing ? 'Modifier l’adresse' : 'Nouvelle adresse'}
        onClose={() => setModalOpen(false)}
        footer={
          <>
            <Button variant="ghost" onClick={() => setModalOpen(false)}>
              Annuler
            </Button>
            <Button type="submit" form="address-form">
              Enregistrer
            </Button>
          </>
        }
      >
        <form id="address-form" onSubmit={handleSubmit}>
          <Field label="Nom" htmlFor="address-name">
            <input
              id="address-name"
              value={form.name}
              onChange={(event) => setForm({ ...form, name: event.target.value })}
              required
              placeholder="Ex. Camille R."
            />
          </Field>
          <Field label="Adresse" htmlFor="address-line">
            <input
              id="address-line"
              value={form.addressLine}
              onChange={(event) => setForm({ ...form, addressLine: event.target.value })}
              required
              placeholder="Ex. 12 rue des Lilas"
            />
          </Field>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 2fr', gap: '0.8rem' }}>
            <Field label="Code postal" htmlFor="address-zip">
              <input
                id="address-zip"
                value={form.zipCode}
                onChange={(event) => setForm({ ...form, zipCode: event.target.value })}
                required
                placeholder="75011"
              />
            </Field>
            <Field label="Ville" htmlFor="address-city">
              <input
                id="address-city"
                value={form.city}
                onChange={(event) => setForm({ ...form, city: event.target.value })}
                required
                placeholder="Paris"
              />
            </Field>
          </div>
          <Field label="Pays" htmlFor="address-country">
            <input
              id="address-country"
              value={form.country}
              onChange={(event) => setForm({ ...form, country: event.target.value })}
              required
              placeholder="France"
            />
          </Field>
        </form>
      </Modal>

      <ConfirmDialog
        open={deleting !== null}
        title="Supprimer cette adresse ?"
        message={deleting ? `« ${deleting.name} — ${deleting.addressLine} » sera définitivement supprimée.` : ''}
        confirmLabel="Supprimer"
        danger
        onConfirm={handleDelete}
        onCancel={() => setDeleting(null)}
      />
    </div>
  );
}
