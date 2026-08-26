import type { HTMLAttributes, ReactNode } from 'react';

interface CardProps extends HTMLAttributes<HTMLDivElement> {
  hover?: boolean;
  children: ReactNode;
}

/** Carte blanche : fond surface, bordure fine, coins arrondis. */
export function Card({ hover = false, children, className = '', ...rest }: CardProps) {
  const classes = ['card', hover ? 'card--hover' : '', className].filter(Boolean).join(' ');
  return (
    <div className={classes} {...rest}>
      {children}
    </div>
  );
}
