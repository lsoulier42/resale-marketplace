import { useState } from 'react';
import { ImageOff } from 'lucide-react';

interface ItemImageProps {
  src: string;
  alt: string;
  className?: string;
}

/**
 * Image avec repli élégant : si l'URL est vide ou le chargement échoue,
 * affiche un dégradé rose/mauve avec une icône.
 */
export function ItemImage({ src, alt, className = '' }: ItemImageProps) {
  const [failed, setFailed] = useState(false);

  if (!src || failed) {
    return (
      <div
        className={`item-image item-image--fallback ${className}`}
        role="img"
        aria-label={alt}
      >
        <ImageOff size={26} />
      </div>
    );
  }

  return (
    <img
      src={src}
      alt={alt}
      className={`item-image ${className}`}
      loading="lazy"
      onError={() => setFailed(true)}
    />
  );
}
