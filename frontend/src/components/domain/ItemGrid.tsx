import { ShoppingBag } from 'lucide-react';
import { ItemCard } from './ItemCard';
import { SkeletonGrid } from '../ui/Skeleton';
import { EmptyState } from '../ui/EmptyState';
import type { ItemCardData } from '../../api/types';

interface ItemGridProps {
  items: ItemCardData[];
  loading: boolean;
  emptyTitle?: string;
  emptyDescription?: string;
}

/** Grille responsive de cartes articles (avec squelettes et état vide). */
export function ItemGrid({
  items,
  loading,
  emptyTitle = 'Aucun article',
  emptyDescription = 'La boutique se remplit — revenez bientôt !',
}: ItemGridProps) {
  if (loading) {
    return <SkeletonGrid count={6} />;
  }
  if (items.length === 0) {
    return (
      <EmptyState
        icon={<ShoppingBag size={28} />}
        title={emptyTitle}
        description={emptyDescription}
      />
    );
  }

  return (
    <div className="item-grid">
      {items.map((item) => (
        <ItemCard key={item.uuid} item={item} />
      ))}
    </div>
  );
}
