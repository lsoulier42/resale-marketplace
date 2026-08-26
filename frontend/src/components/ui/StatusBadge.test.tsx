import { describe, expect, it } from 'vitest';
import { render, screen } from '@testing-library/react';
import { StatusBadge } from './StatusBadge';
import type { OrderStatus } from '../../api/types';

describe('StatusBadge', () => {
  const cases: Array<[OrderStatus, string]> = [
    ['pending_payment', 'En attente de paiement'],
    ['paid', 'Payé'],
    ['shipped', 'Expédié'],
    ['delivered', 'Livré'],
    ['cancelled', 'Annulé'],
  ];

  it.each(cases)('affiche le libellé français pour %s', (status, label) => {
    render(<StatusBadge status={status} />);
    expect(screen.getByText(label)).toBeInTheDocument();
  });
});
