import { useRef, useState, type ChangeEvent } from 'react';
import { ImagePlus, Trash2 } from 'lucide-react';
import { GlassCard } from '../components/ui/GlassCard';
import { Button } from '../components/ui/Button';
import { ConfirmDialog } from '../components/ui/ConfirmDialog';
import { EmptyState } from '../components/ui/EmptyState';
import { ItemImage } from '../components/domain/ItemImage';
import { useToast } from '../components/ui/useToast';
import { useMedias, useUploadMedia, useDeleteMedia } from '../hooks/useSeller';
import type { MediaData } from '../api/types';

export function MediasPage() {
  const toast = useToast();
  const { data, isLoading, isError } = useMedias();
  const uploadMedia = useUploadMedia();
  const deleteMedia = useDeleteMedia();
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [deleting, setDeleting] = useState<MediaData | null>(null);

  const handleFileChange = async (event: ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file) {
      return;
    }
    try {
      await uploadMedia.mutateAsync(file);
      toast.success('Média uploadé.');
    } catch (error) {
      toast.error(error instanceof Error ? error.message : 'Upload impossible.');
    } finally {
      event.target.value = '';
    }
  };

  const handleDelete = async () => {
    if (!deleting) {
      return;
    }
    try {
      await deleteMedia.mutateAsync(deleting.uuid);
      toast.success('Média supprimé.');
    } catch (error) {
      toast.error(error instanceof Error ? error.message : 'Suppression impossible.');
    } finally {
      setDeleting(null);
    }
  };

  const medias = data?.medias ?? [];

  return (
    <div style={{ maxWidth: 860 }}>
      <div className="flex-between" style={{ alignItems: 'flex-end', flexWrap: 'wrap', gap: '0.8rem' }}>
        <div>
          <h1 className="page-title" style={{ marginBottom: '0.3rem' }}>
            Médias
          </h1>
          <p className="text-muted text-small">
            Vos photos pour illustrer vos articles et votre profil.
          </p>
        </div>
        <input
          ref={fileInputRef}
          type="file"
          accept="image/*"
          hidden
          onChange={handleFileChange}
          aria-label="Choisir un fichier"
        />
        <Button variant="glass" onClick={() => fileInputRef.current?.click()} disabled={uploadMedia.isPending}>
          <ImagePlus size={16} /> {uploadMedia.isPending ? 'Upload…' : 'Uploader une photo'}
        </Button>
      </div>

      {isError && (
        <div className="glass-card" style={{ padding: '1.2rem', textAlign: 'center' }}>
          <p className="text-muted">Impossible de charger vos médias.</p>
        </div>
      )}

      {isLoading ? (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(160px, 1fr))', gap: '1rem', marginTop: '1.2rem' }}>
          {Array.from({ length: 4 }, (_, index) => (
            <div key={index} className="glass-card" style={{ height: 140 }} />
          ))}
        </div>
      ) : medias.length === 0 ? (
        <div className="mt-2">
          <EmptyState
            icon={<ImagePlus size={28} />}
            title="Aucun média"
            description="Uploadez vos premières photos."
            action={
              <Button size="sm" onClick={() => fileInputRef.current?.click()}>
                <ImagePlus size={14} /> Uploader
              </Button>
            }
          />
        </div>
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(160px, 1fr))', gap: '1rem', marginTop: '1.2rem' }}>
          {medias.map((media) => (
            <GlassCard key={media.uuid} hover style={{ overflow: 'hidden', position: 'relative' }}>
              <ItemImage src={media.fileUrl} alt="Média" className="media-grid-image" />
              <div className="media-grid-footer">
                <span className="text-muted text-small">{media.fileType}</span>
                <Button
                  variant="ghost"
                  size="sm"
                  onClick={() => setDeleting(media)}
                  aria-label="Supprimer le média"
                >
                  <Trash2 size={14} style={{ color: '#be123c' }} />
                </Button>
              </div>
            </GlassCard>
          ))}
        </div>
      )}

      <ConfirmDialog
        open={deleting !== null}
        title="Supprimer ce média ?"
        message="Il sera retiré de vos articles. Cette action est irréversible."
        confirmLabel="Supprimer"
        danger
        onConfirm={handleDelete}
        onCancel={() => setDeleting(null)}
      />
    </div>
  );
}
