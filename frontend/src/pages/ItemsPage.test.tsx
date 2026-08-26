import { describe, expect, it, vi } from 'vitest';
import { render, screen, fireEvent, waitFor, within } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { AuthProvider } from '../auth/AuthContext';
import { ToastProvider } from '../components/ui/Toast';
import { ItemsPage } from './ItemsPage';
import { jsonResponse } from '../test/setup';
import type { CategoryData, ItemCardData } from '../api/types';

const category1: CategoryData = {
  uuid: 'cat-1',
  title: 'Vêtements',
  description: null,
  itemCount: 2,
};
const category2: CategoryData = {
  uuid: 'cat-2',
  title: 'Mode',
  description: null,
  itemCount: 1,
};

const item: ItemCardData = {
  uuid: 'item-1',
  title: 'Veste en jean',
  price: '12.00',
  availableCount: 1,
  isSold: false,
  medias: ['/uploads/demo.svg'],
  category: { uuid: 'cat-1', title: 'Vêtements' },
  seller: { uuid: 'perf-1', displayName: 'Sam', avatarUrl: null },
};

function renderItems() {
  const queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });
  return render(
    <QueryClientProvider client={queryClient}>
      <MemoryRouter>
        <AuthProvider>
          <ToastProvider>
            <ItemsPage />
          </ToastProvider>
        </AuthProvider>
      </MemoryRouter>
    </QueryClientProvider>,
  );
}

describe('ItemsPage', () => {
  it('affiche les articles, la pagination et le filtre de catégorie', async () => {
    const fetchMock = vi.fn(async (input: RequestInfo | URL) => {
      const url = String(input);
      if (url.includes('/api/me')) {
        return jsonResponse({ user: null, isSeller: false, isCustomer: false });
      }
      if (url.includes('/api/items')) {
        return jsonResponse({
          items: [item],
          page: 1,
          limit: 12,
          total: 14,
          pages: 2,
        });
      }
      if (url.includes('/api/categories')) {
        return jsonResponse({ categories: [category1, category2] });
      }
      return jsonResponse({ error: { message: 'Not Found' } }, 404);
    });
    vi.stubGlobal('fetch', fetchMock);

    renderItems();

    expect(await screen.findByText('Veste en jean')).toBeInTheDocument();
    expect(screen.getByText('14 articles disponibles')).toBeInTheDocument();
    expect(screen.getByLabelText('Page suivante')).toBeEnabled();
    // La catégorie apparaît dans la sidebar de filtres et dans les chips mobiles.
    expect(screen.getAllByText('Mode').length).toBeGreaterThanOrEqual(1);
  });

  it('recharge la liste quand on change de catégorie', async () => {
    const fetchMock = vi.fn(async (input: RequestInfo | URL) => {
      const url = String(input);
      if (url.includes('/api/me')) {
        return jsonResponse({ user: null, isSeller: false, isCustomer: false });
      }
      if (url.includes('/api/items')) {
        return jsonResponse({ items: [item], page: 1, limit: 12, total: 14, pages: 2 });
      }
      if (url.includes('/api/categories')) {
        return jsonResponse({ categories: [category1, category2] });
      }
      return jsonResponse({ error: { message: 'Not Found' } }, 404);
    });
    vi.stubGlobal('fetch', fetchMock);

    renderItems();
    // Attend le chargement initial de la liste.
    expect(await screen.findByText('Veste en jean')).toBeInTheDocument();

    // Clique sur la catégorie « Mode » dans la sidebar de filtres.
    const filters = screen.getByRole('complementary', { name: 'Filtres' });
    fireEvent.click(within(filters).getByRole('button', { name: /Mode/ }));

    const itemsCalls = fetchMock.mock.calls.filter(([input]) =>
      String(input).includes('/api/items'),
    );
    await waitFor(() => {
      expect(itemsCalls.length).toBeGreaterThanOrEqual(2);
    });
    expect(String(itemsCalls.at(-1)?.[0])).toContain('category=cat-2');
  });
});
