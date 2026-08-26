import { useState, type FormEvent } from 'react';
import { ShieldCheck, Pencil, Trash2, Plus } from 'lucide-react';
import { GlassCard } from '../components/ui/GlassCard';
import { Button } from '../components/ui/Button';
import { Field } from '../components/ui/Field';
import { Modal } from '../components/ui/Modal';
import { ConfirmDialog } from '../components/ui/ConfirmDialog';
import { EmptyState } from '../components/ui/EmptyState';
import { Skeleton } from '../components/ui/Skeleton';
import { useToast } from '../components/ui/useToast';
import { useAdminUsers, useCreateUser, useUpdateUser, useDeleteUser } from '../hooks/useSeller';
import type { AdminUserData } from '../api/types';

interface UserFormState {
  email: string;
  password: string;
  roles: string[];
  isVerified: boolean;
}

export function AdminUsersPage() {
  const toast = useToast();
  const { data, isLoading, isError } = useAdminUsers();
  const createUser = useCreateUser();
  const updateUser = useUpdateUser();
  const deleteUser = useDeleteUser();

  const [modalOpen, setModalOpen] = useState(false);
  const [editing, setEditing] = useState<AdminUserData | null>(null);
  const [deleting, setDeleting] = useState<AdminUserData | null>(null);
  const [form, setForm] = useState<UserFormState>({ email: '', password: '', roles: ['ROLE_USER'], isVerified: true });

  const openCreate = () => {
    setEditing(null);
    setForm({ email: '', password: '', roles: ['ROLE_USER'], isVerified: true });
    setModalOpen(true);
  };

  const openEdit = (user: AdminUserData) => {
    setEditing(user);
    setForm({ email: user.email, password: '', roles: user.roles, isVerified: user.isVerified });
    setModalOpen(true);
  };

  const toggleRole = (role: 'ROLE_USER' | 'ROLE_ADMIN') => {
    setForm((current) => ({
      ...current,
      roles: current.roles.includes(role)
        ? current.roles.filter((r) => r !== role)
        : [...current.roles, role],
    }));
  };

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault();
    try {
      if (editing) {
        await updateUser.mutateAsync({
          uuid: editing.uuid,
          payload: { email: form.email, roles: form.roles, isVerified: form.isVerified, password: form.password || null },
        });
        toast.success('Utilisateur mis à jour.');
      } else {
        await createUser.mutateAsync({ email: form.email, password: form.password, roles: form.roles, isVerified: form.isVerified });
        toast.success('Utilisateur créé.');
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
      await deleteUser.mutateAsync(deleting.uuid);
      toast.success('Utilisateur supprimé.');
    } catch (error) {
      toast.error(error instanceof Error ? error.message : 'Suppression impossible.');
    } finally {
      setDeleting(null);
    }
  };

  const users = data?.users ?? [];

  return (
    <div style={{ maxWidth: 860 }}>
      <div className="flex-between" style={{ alignItems: 'flex-end', flexWrap: 'wrap', gap: '0.8rem' }}>
        <div>
          <h1 className="page-title" style={{ marginBottom: '0.3rem' }}>
            Utilisateurs
          </h1>
          <p className="text-muted text-small">{users.length} comptes</p>
        </div>
        <Button variant="glass" onClick={openCreate}>
          <Plus size={16} /> Créer un utilisateur
        </Button>
      </div>

      {isError && (
        <div className="glass-card" style={{ padding: '1.2rem', textAlign: 'center' }}>
          <p className="text-muted">Impossible de charger les utilisateurs.</p>
        </div>
      )}

      {isLoading ? (
        <div style={{ display: 'flex', flexDirection: 'column', gap: '0.8rem', marginTop: '1.2rem' }}>
          {Array.from({ length: 4 }, (_, index) => (
            <div key={index} className="glass-card" style={{ padding: '1rem' }}>
              <Skeleton height={18} width="50%" />
            </div>
          ))}
        </div>
      ) : users.length === 0 ? (
        <EmptyState icon={<ShieldCheck size={28} />} title="Aucun utilisateur" />
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: '0.8rem', marginTop: '1.2rem' }}>
          {users.map((user) => (
            <GlassCard key={user.uuid} style={{ padding: '1rem 1.2rem' }}>
              <div className="flex-between" style={{ gap: '1rem' }}>
                <div style={{ minWidth: 0 }}>
                  <p className="fw-bold" style={{ overflow: 'hidden', textOverflow: 'ellipsis' }}>
                    {user.email}
                  </p>
                  <div style={{ display: 'flex', gap: '0.4rem', marginTop: '0.3rem', flexWrap: 'wrap' }}>
                    {user.roles.map((role) => (
                      <span key={role} className={`badge ${role === 'ROLE_ADMIN' ? 'badge-mauve' : 'badge-pink'}`}>
                        {role === 'ROLE_ADMIN' ? 'Admin' : 'Utilisateur'}
                      </span>
                    ))}
                    {!user.isVerified && <span className="badge badge-amber">Non vérifié</span>}
                  </div>
                </div>
                <div style={{ display: 'flex', gap: '0.5rem', flexShrink: 0 }}>
                  <Button variant="ghost" size="sm" onClick={() => openEdit(user)} aria-label={`Modifier ${user.email}`}>
                    <Pencil size={15} />
                  </Button>
                  <Button variant="ghost" size="sm" onClick={() => setDeleting(user)} aria-label={`Supprimer ${user.email}`}>
                    <Trash2 size={15} style={{ color: '#be123c' }} />
                  </Button>
                </div>
              </div>
            </GlassCard>
          ))}
        </div>
      )}

      <Modal
        open={modalOpen}
        title={editing ? 'Modifier l’utilisateur' : 'Nouvel utilisateur'}
        onClose={() => setModalOpen(false)}
        footer={
          <>
            <Button variant="ghost" onClick={() => setModalOpen(false)}>
              Annuler
            </Button>
            <Button type="submit" form="user-form">
              Enregistrer
            </Button>
          </>
        }
      >
        <form id="user-form" onSubmit={handleSubmit}>
          <Field label="Email" htmlFor="user-email">
            <input
              id="user-email"
              type="email"
              value={form.email}
              onChange={(event) => setForm({ ...form, email: event.target.value })}
              required
            />
          </Field>
          <Field
            label={editing ? 'Nouveau mot de passe (optionnel)' : 'Mot de passe'}
            htmlFor="user-password"
          >
            <input
              id="user-password"
              type="password"
              minLength={8}
              value={form.password}
              onChange={(event) => setForm({ ...form, password: event.target.value })}
              required={!editing}
              placeholder="8 caractères minimum"
            />
          </Field>
          <div className="field">
            <span className="field-label" style={{ fontSize: '0.85rem', fontWeight: 700, color: 'var(--ink-muted)' }}>
              Rôles
            </span>
            <div style={{ display: 'flex', gap: '1.2rem', marginTop: '0.3rem' }}>
              <label style={{ display: 'flex', alignItems: 'center', gap: '0.4rem', fontSize: '0.9rem' }}>
                <input
                  type="checkbox"
                  checked={form.roles.includes('ROLE_ADMIN')}
                  onChange={() => toggleRole('ROLE_ADMIN')}
                />
                Admin
              </label>
              <label style={{ display: 'flex', alignItems: 'center', gap: '0.4rem', fontSize: '0.9rem' }}>
                <input
                  type="checkbox"
                  checked={form.roles.includes('ROLE_USER')}
                  onChange={() => toggleRole('ROLE_USER')}
                />
                Utilisateur
              </label>
            </div>
          </div>
          <label style={{ display: 'flex', alignItems: 'center', gap: '0.4rem', fontSize: '0.9rem' }}>
            <input
              type="checkbox"
              checked={form.isVerified}
              onChange={(event) => setForm({ ...form, isVerified: event.target.checked })}
            />
            Email vérifié
          </label>
        </form>
      </Modal>

      <ConfirmDialog
        open={deleting !== null}
        title="Supprimer cet utilisateur ?"
        message={deleting ? `Le compte « ${deleting.email} » sera définitivement supprimé.` : ''}
        confirmLabel="Supprimer"
        danger
        onConfirm={handleDelete}
        onCancel={() => setDeleting(null)}
      />
    </div>
  );
}
