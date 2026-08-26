import { Link } from 'react-router-dom';
import { Tags } from 'lucide-react';
import { Card } from '../ui/Card';
import type { CategoryData } from '../../api/types';

/** Carte catégorie : titre, description, nombre d'articles disponibles. */
export function CategoryCard({ category }: { category: CategoryData }) {
  return (
    <Card hover className="category-card">
      <Link to={`/categories/${category.uuid}`} className="category-card-link">
        <div className="category-card-icon">
          <Tags size={22} />
        </div>
        <h3 className="category-card-title">{category.title}</h3>
        {category.description && (
          <p className="text-muted text-small category-card-description">{category.description}</p>
        )}
        <span className="badge badge-accent">
          {category.itemCount} article{category.itemCount > 1 ? 's' : ''}
        </span>
      </Link>
    </Card>
  );
}
