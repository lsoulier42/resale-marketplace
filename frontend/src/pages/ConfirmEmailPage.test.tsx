import { describe, expect, it, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ConfirmEmailPage } from './ConfirmEmailPage';
import { jsonResponse } from '../test/setup';

function renderConfirmEmail(token: string | null) {
  const queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });
  const query = token !== null ? `?token=${token}` : '';
  return render(
    <QueryClientProvider client={queryClient}>
      <MemoryRouter initialEntries={[`/confirm-email${query}`]}>
        <Routes>
          <Route path="/confirm-email" element={<ConfirmEmailPage />} />
          <Route path="/login" element={<div>Page de connexion</div>} />
        </Routes>
      </MemoryRouter>
    </QueryClientProvider>,
  );
}

describe('ConfirmEmailPage', () => {
  it('confirme le compte avec un jeton valide', async () => {
    const fetchMock = vi.fn(async (input: RequestInfo | URL) => {
      const url = String(input);
      if (url.includes('/api/register/confirm/')) {
        return jsonResponse({ confirmed: true });
      }
      return jsonResponse({ error: { message: 'Not Found' } }, 404);
    });
    vi.stubGlobal('fetch', fetchMock);

    renderConfirmEmail('token-valide-123');

    expect(await screen.findByText('Compte confirmé !')).toBeInTheDocument();
    expect(screen.getByRole('link', { name: 'Se connecter' })).toHaveAttribute('href', '/login');
  });

  it('affiche une erreur si le jeton est invalide', async () => {
    const fetchMock = vi.fn(async (input: RequestInfo | URL) => {
      const url = String(input);
      if (url.includes('/api/register/confirm/')) {
        return jsonResponse({ error: { message: 'Lien de confirmation invalide ou expiré.' } }, 400);
      }
      return jsonResponse({ error: { message: 'Not Found' } }, 404);
    });
    vi.stubGlobal('fetch', fetchMock);

    renderConfirmEmail('mauvais-jeton');

    expect(await screen.findByText('Lien invalide ou expiré')).toBeInTheDocument();
    expect(screen.getByLabelText('Votre email')).toBeInTheDocument();
  });

  it('affiche une erreur si aucun jeton', async () => {
    renderConfirmEmail(null);

    expect(await screen.findByText('Lien invalide ou expiré')).toBeInTheDocument();
  });

  it('renvoie un email de confirmation depuis l’écran d’erreur', async () => {
    const fetchMock = vi.fn(async (input: RequestInfo | URL) => {
      const url = String(input);
      if (url.includes('/api/register/confirm/')) {
        return jsonResponse({ error: { message: 'Lien de confirmation invalide ou expiré.' } }, 400);
      }
      if (url.includes('/api/csrf-token')) {
        return jsonResponse({ token: 'test-token' });
      }
      if (url.includes('/api/register/resend-confirmation')) {
        return jsonResponse({ sent: true });
      }
      return jsonResponse({ error: { message: 'Not Found' } }, 404);
    });
    vi.stubGlobal('fetch', fetchMock);

    renderConfirmEmail('mauvais-jeton');

    fireEvent.change(await screen.findByLabelText('Votre email'), {
      target: { value: 'attente@example.test' },
    });
    fireEvent.click(screen.getByRole('button', { name: "Renvoyer l'email de confirmation" }));

    expect(
      await screen.findByText('Email renvoyé si un compte en attente de confirmation existe.'),
    ).toBeInTheDocument();

    const resendCall = fetchMock.mock.calls.find(([input]) =>
      String(input).includes('/api/register/resend-confirmation'),
    );
    expect(resendCall).toBeDefined();
    const options = (resendCall as unknown[])[1] as RequestInit;
    expect(JSON.parse(String(options.body))).toEqual({ email: 'attente@example.test' });
  });
});
