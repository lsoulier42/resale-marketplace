interface SkeletonProps {
  width?: string | number;
  height?: string | number;
  borderRadius?: string | number;
}

/** Placeholder de chargement gris neutre. */
export function Skeleton({ width = '100%', height = 16, borderRadius = 8 }: SkeletonProps) {
  return (
    <div
      className="skeleton"
      style={{ width, height, borderRadius }}
      aria-hidden="true"
      role="presentation"
    />
  );
}

/** Grille de squelettes (ex. cartes d'articles en chargement). */
export function SkeletonGrid({ count = 6 }: { count?: number }) {
  return (
    <div className="item-grid" aria-hidden="true">
      {Array.from({ length: count }, (_, index) => (
        <div key={index} className="skeleton-card">
          <Skeleton height={240} borderRadius={8} />
          <Skeleton height={14} width="75%" borderRadius={6} />
          <Skeleton height={16} width="45%" borderRadius={6} />
        </div>
      ))}
    </div>
  );
}
