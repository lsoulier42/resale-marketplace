import type { ReactNode } from 'react';

interface EmptyStateProps {
  icon?: ReactNode;
  title: string;
  description?: string;
  action?: ReactNode;
}

/** État vide épuré : icône + titre + description + action, sans encadré. */
export function EmptyState({ icon, title, description, action }: EmptyStateProps) {
  return (
    <div className="empty-state">
      {icon && <div className="empty-icon">{icon}</div>}
      <h3 className="empty-title">{title}</h3>
      {description && <p className="text-muted text-small mt-1">{description}</p>}
      {action && <div className="mt-2">{action}</div>}
    </div>
  );
}
