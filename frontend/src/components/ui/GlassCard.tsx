import type { HTMLAttributes, ReactNode } from 'react';

interface GlassCardProps extends HTMLAttributes<HTMLDivElement> {
  hover?: boolean;
  children: ReactNode;
}

/** Carte en verre dépoli, coins arrondis, ombre douce. */
export function GlassCard({ hover = false, children, className = '', ...rest }: GlassCardProps) {
  const classes = ['glass-card', hover ? 'glass-card--hover' : '', className].filter(Boolean).join(' ');
  return (
    <div className={classes} {...rest}>
      {children}
    </div>
  );
}
