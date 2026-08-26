import { describe, expect, it } from 'vitest';
import { render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { ItemCard } from './ItemCard';
import type { ItemCardData } from '../../api/types';

const item: ItemCardData = {
  uuid: 'aaa-bbb',
  title: 'Veste en jean',
  price: '15.50',
  availableCount: 2,
  isSold: false,
  medias: ['/uploads/demo.svg'],
  category: { uuid: 'cat-1', title: 'Mode' },
  seller: { uuid: 'perf-1', displayName: 'Sam', avatarUrl: null },
};

describe('ItemCard', () => {
  it('affiche le titre, le prix formaté et le vendeur·se', () => {
    render(
      <MemoryRouter>
        <ItemCard item={item} />
      </MemoryRouter>,
    );

    expect(screen.getByText('Veste en jean')).toBeInTheDocument();
    expect(screen.getByText('15,50 €')).toBeInTheDocument();
    expect(screen.getByText('Mode')).toBeInTheDocument();
    expect(screen.getByText('Sam')).toBeInTheDocument();
  });

  it('affiche un badge « Vendu » quand l’article est vendu', () => {
    render(
      <MemoryRouter>
        <ItemCard item={{ ...item, isSold: true }} />
      </MemoryRouter>,
    );

    expect(screen.getByText('Vendu')).toBeInTheDocument();
  });

  it('utilise l’uuid dans le lien vers la fiche article', () => {
    render(
      <MemoryRouter>
        <ItemCard item={item} />
      </MemoryRouter>,
    );

    expect(screen.getByRole('link')).toHaveAttribute('href', '/items/aaa-bbb');
  });
});
