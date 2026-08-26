import { describe, expect, it } from 'vitest';
import { render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { OrderCard } from './OrderCard';
import type { OrderData } from '../../api/types';

const order: OrderData = {
  uuid: 'order-1',
  reference: 'REF-ABC123',
  status: 'shipped',
  statusLabel: 'Expédié',
  totalPrice: '25.50',
  shippingFee: '3.50',
  trackingNumber: 'TS-42',
  shippingProvider: 'Colissimo',
  createdAt: '2026-08-10T10:00:00+00:00',
  item: {
    uuid: 'item-1',
    title: 'Robe vintage',
    price: '22.00',
    availableCount: 1,
    isSold: true,
    medias: ['/uploads/demo.svg'],
    category: { uuid: 'cat-1', title: 'Mode' },
    seller: { uuid: 'seller-1', displayName: 'Sam', avatarUrl: null },
  },
  seller: { uuid: 'seller-1', displayName: 'Sam', avatarUrl: null },
  shippingAddress: null,
  allowedTransitions: [{ value: 'delivered', label: 'Livré' }],
  canUpdateStatus: true,
  canPay: false,
  canReview: false,
  hasReview: false,
};

describe('OrderCard', () => {
  it('affiche la référence, le statut et le total', () => {
    render(
      <MemoryRouter>
        <OrderCard order={order} />
      </MemoryRouter>,
    );

    expect(screen.getByText('REF-ABC123')).toBeInTheDocument();
    expect(screen.getByText('Expédié')).toBeInTheDocument();
    expect(screen.getByText('Robe vintage')).toBeInTheDocument();
    expect(screen.getByText('25,50 €')).toBeInTheDocument();
    expect(screen.getByRole('link')).toHaveAttribute('href', '/orders/order-1');
  });
});
