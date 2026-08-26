import { useEffect, useState, type FormEvent } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { ArrowLeft, Plus } from 'lucide-react';
import { GlassCard } from '../components/ui/GlassCard';
import { Button } from '../components/ui/Button';
import { Field } from '../components/ui/Field';
import { Skeleton } from '../components/ui/Skeleton';
import { ItemImage } from '../components/domain/ItemImage';
import { useToast } from '../components/ui/useToast';
import { useAuth } from '../auth/useAuth';
import { useCategories, useItem } from '../hooks/useCatalog';
import { useCreateItem, useUpdateItem, useMedias } from '../hooks/useSeller';
import type { MediaData } from '../api/types';

export function ItemFormPage() {
  const { uuid = '' } = useParams();
  const isEdit = uuid !== '';
  const navigate = useNavigate();
  const toast = useToast();
  const { isSeller } = useAuth();

  const categoriesQuery = useCategories();
  const mediasQuery = useMedias();
  const { data: itemData, isLoading: itemLoading } = useItem(uuid);
  const createItem = useCreateItem();
  const updateItem = useUpdateItem(uuid);

  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [price, setPrice] = useState('');
  const [availableCount, setAvailableCount] = useState('1');
  const [categoryUuid, setCategoryUuid] = useState('');
  const [mediaUuids, setMediaUuids] = useState<string[]>([]);

  const item = itemData?.item;

  // Pré-remplissage en mode édition.
  useEffect(() => {
    if (isEdit && item) {
      setTitle(item.title);
      setDescription(item.description ?? '');
      setPrice(item.price);
      setAvailableCount(String(item.availableCount));
      setCategoryUuid(item.category.uuid);
      setMediaUuids([]);
    }
  }, [isEdit, item]);

  const toggleMedia = (mediaUuid: string) => {
    setMediaUuids((current) =>
      current.includes(mediaUuid)
        ? current.filter((uuid) => uuid !== mediaUuid)
        : [...current, mediaUuid],
    );
  };

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault();
    try {
      const payload = {
        title,
        description: description.trim() !== '' ? description : null,
        price,
        availableCount: Number.parseInt(availableCount, 10) || 1,
        categoryUuid,
        mediaUuids,
      };
      if (isEdit) {
        await updateItem.mutateAsync(payload);
        toast.success('Article mis à jour.');
        navigate(`/items/${uuid}`, { replace: true });
      } else {
        const result = await createItem.mutateAsync(payload);
        toast.success('Article publié !');
        navigate(`/items/${result.item.uuid}`, { replace: true });
      }
    } catch (error) {
      toast.error(error instanceof Error ? error.message : 'Enregistrement impossible.');
    }
  };

  if (!isSeller) {
    return (
      <GlassCard style={{ padding: '2rem', textAlign: 'center', maxWidth: 560, margin: '2rem auto' }}>
        <h1 className="page-title">Publier un article</h1>
        <p className="text-muted">
          Seul·es les vendeur·ses peuvent publier des articles. Contactez l'équipe pour ouvrir
          votre boutique.
        </p>
        <Link to="/" className="btn btn-glass mt-2">
          Retour à l'accueil
        </Link>
      </GlassCard>
    );
  }

  if (isEdit && itemLoading) {
    return (
      <div className="glass-card" style={{ padding: '1.6rem' }}>
        <Skeleton height={26} width={220} />
      </div>
    );
  }

  return (
    <div style={{ maxWidth: 720 }}>
      <Link to={isEdit ? `/items/${uuid}` : '/items'} className="btn btn-ghost btn-sm mb-2">
        <ArrowLeft size={15} /> Retour
      </Link>
      <h1 className="page-title">{isEdit ? 'Modifier l’article' : 'Publier un article'}</h1>

      <GlassCard style={{ padding: '1.8rem' }}>
        <form onSubmit={handleSubmit}>
          <Field label="Titre" htmlFor="item-title">
            <input
              id="item-title"
              value={title}
              onChange={(event) => setTitle(event.target.value)}
              required
              placeholder="Ex. Robe vintage taille 38"
            />
          </Field>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }}>
            <Field label="Prix (€)" htmlFor="item-price">
              <input
                id="item-price"
                type="number"
                min="0"
                step="0.01"
                value={price}
                onChange={(event) => setPrice(event.target.value)}
                required
                placeholder="0.00"
              />
            </Field>
            <Field label="Quantité disponible" htmlFor="item-count">
              <input
                id="item-count"
                type="number"
                min="0"
                value={availableCount}
                onChange={(event) => setAvailableCount(event.target.value)}
                required
              />
            </Field>
          </div>
          <Field label="Catégorie" htmlFor="item-category">
            <select
              id="item-category"
              value={categoryUuid}
              onChange={(event) => setCategoryUuid(event.target.value)}
              required
            >
              <option value="" disabled>
                Choisir…
              </option>
              {(categoriesQuery.data?.categories ?? []).map((category) => (
                <option key={category.uuid} value={category.uuid}>
                  {category.title}
                </option>
              ))}
            </select>
          </Field>
          <Field label="Description" htmlFor="item-description">
            <textarea
              id="item-description"
              rows={4}
              value={description}
              onChange={(event) => setDescription(event.target.value)}
              placeholder="État, défauts, dimensions…"
            />
          </Field>

          <Field label="Photos (médias)" htmlFor="item-medias" hint="Uploadez d'abord vos médias dans « Médias ».">
            {mediasQuery.isLoading ? (
              <Skeleton height={60} />
            ) : (mediasQuery.data?.medias ?? []).length === 0 ? (
              <p className="text-muted text-small">
                Aucun média disponible.{' '}
                <Link to="/medias">Uploadez vos photos ici</Link>.
              </p>
            ) : (
              <div className="media-picker">
                {(mediasQuery.data?.medias ?? []).map((media: MediaData) => {
                  const selected = mediaUuids.includes(media.uuid);
                  return (
                    <button
                      key={media.uuid}
                      type="button"
                      className={`media-picker-item${selected ? ' media-picker-item--selected' : ''}`}
                      onClick={() => toggleMedia(media.uuid)}
                      aria-pressed={selected}
                      aria-label={selected ? 'Retirer la photo' : 'Ajouter la photo'}
                    >
                      <ItemImage src={media.fileUrl} alt="Média" className="media-picker-image" />
                      {selected && <Plus size={16} className="media-picker-check" />}
                    </button>
                  );
                })}
              </div>
            )}
          </Field>

          <div style={{ display: 'flex', gap: '0.7rem', marginTop: '0.5rem' }}>
            <Button type="submit" disabled={createItem.isPending || updateItem.isPending}>
              {createItem.isPending || updateItem.isPending
                ? 'Enregistrement…'
                : isEdit
                  ? 'Enregistrer les modifications'
                  : 'Publier l’article'}
            </Button>
            <Link to={isEdit ? `/items/${uuid}` : '/items'} className="btn btn-ghost">
              Annuler
            </Link>
          </div>
        </form>
      </GlassCard>
    </div>
  );
}
