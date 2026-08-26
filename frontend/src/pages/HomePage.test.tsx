import { describe, expect, it, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { AuthProvider } from '../auth/AuthContext';
import { ToastProvider } from '../components/ui/Toast';
import { HomePage } from './HomePage';
import { jsonResponse } from '../test/setup';
import type { HomeData } from '../api/types';

const homeData: HomeData = {
  featuredItems: [
    {
      uuid: 'item-1',
      title: 'Veste en jean',
      price: '12.00',
      availableCount: 1,
      isSold: false,
      medias: ['/uploads/demo.svg'],
      category: { uuid: 'cat-1', title: 'Vêtements' },
      seller: { uuid: 'perf-1', displayName: 'Sam', avatarUrl: null },
    },
  ],
  categories: [
    { uuid: 'cat-1', title: 'Vêtements', description: 'Mode d’occasion', itemCount: 3 },
  ],
};

function renderHome() {
  const queryClient = new QueryClient({
    defaultOptions: { queries: { retry: false } },
  });
  return render(
    <QueryClientProvider client={queryClient}>
      <MemoryRouter>
        <AuthProvider>
          <ToastProvider>
            <HomePage />
          </ToastProvider>
        </AuthProvider>
      </MemoryRouter>
    </QueryClientProvider>,
  );
}

describe('HomePage', () => {
  it('affiche les articles vedette et les catégories chargés depuis l’API', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn(async (input: RequestInfo | URL) => {
        const url = String(input);
        if (url.includes('/api/me')) {
          return jsonResponse({ user: null });
        }
        if (url.includes('/api/home')) {
          return jsonResponse(homeData);
        }
        return jsonResponse({ error: { message: 'Not Found' } }, 404);
      }),
    );

    renderHome();

    expect(await screen.findByText('Veste en jean')).toBeInTheDocument();
    expect(screen.getAllByText('Vêtements').length).toBeGreaterThanOrEqual(2);
    expect(screen.getByText('Articles en vedette')).toBeInTheDocument();
  });

  it('affiche un état d’erreur si l’API échoue', async () => {
    vi.stubGlobal('fetch', vi.fn(async () => jsonResponse({ error: { message: 'Erreur' } }, 500)));

    renderHome();

    expect(
      await screen.findByText('Impossible de charger la boutique pour le moment.'),
    ).toBeInTheDocument();
  });
});
