import { useState } from 'react';
import { Star } from 'lucide-react';

interface RatingProps {
  value: number;
  onChange?: (value: number) => void;
  readOnly?: boolean;
  max?: number;
}

/** Étoiles de notation : affichage (readOnly) ou saisie interactive. */
export function Rating({ value, onChange, readOnly = false, max = 5 }: RatingProps) {
  const [hovered, setHovered] = useState(0);
  const interactive = !readOnly && onChange !== undefined;

  return (
    <div
      className="rating"
      data-readonly={readOnly || undefined}
      role={interactive ? 'radiogroup' : 'img'}
      aria-label={`${value} sur ${max} étoiles`}
    >
      {Array.from({ length: max }, (_, index) => {
        const starValue = index + 1;
        const filled = starValue <= (hovered || value);
        return (
          <button
            key={index}
            type="button"
            className={`rating-star${filled ? ' rating-star--filled' : ''}${
              interactive && hovered >= starValue ? ' rating-star--hover' : ''
            }`}
            disabled={!interactive}
            aria-label={`${starValue} étoile${starValue > 1 ? 's' : ''}`}
            onClick={() => onChange?.(starValue)}
            onMouseEnter={interactive ? () => setHovered(starValue) : undefined}
            onMouseLeave={interactive ? () => setHovered(0) : undefined}
          >
            <Star size={20} fill={filled ? 'currentColor' : 'none'} />
          </button>
        );
      })}
    </div>
  );
}
