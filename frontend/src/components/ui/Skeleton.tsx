interface SkeletonProps {
  width?: string | number;
  height?: string | number;
  borderRadius?: string | number;
}

/** Placeholder de chargement avec effet de brillance. */
export function Skeleton({ width = '100%', height = 16, borderRadius = 12 }: SkeletonProps) {
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
    <div
      style={{
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fill, minmax(240px, 1fr))',
        gap: '1.2rem',
      }}
      aria-hidden="true"
    >
      {Array.from({ length: count }, (_, index) => (
        <div key={index} className="glass-card" style={{ padding: '1.2rem' }}>
          <Skeleton height={150} borderRadius={14} />
          <Skeleton height={18} width="70%" borderRadius={10} />
          <Skeleton height={18} width="45%" borderRadius={10} />
        </div>
      ))}
    </div>
  );
}
