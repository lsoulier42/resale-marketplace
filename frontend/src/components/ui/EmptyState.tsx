import type { ReactNode } from 'react';

interface EmptyStateProps {
  icon?: ReactNode;
  title: string;
  description?: string;
  action?: ReactNode;
}

/** État vide : icône dans un halo + titre + description + action. */
export function EmptyState({ icon, title, description, action }: EmptyStateProps) {
  return (
    <div className="glass-card empty-state">
      {icon && <div className="empty-icon">{icon}</div>}
      <h3 style={{ fontSize: '1.05rem' }}>{title}</h3>
      {description && <p className="text-muted text-small mt-1">{description}</p>}
      {action && <div className="mt-2">{action}</div>}
    </div>
  );
}
