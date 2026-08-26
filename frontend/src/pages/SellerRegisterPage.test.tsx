import { describe, expect, it, vi } from 'vitest';
import { act } from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { AuthProvider } from '../auth/AuthContext';
import { ToastProvider } from '../components/ui/Toast';
import { SellerRegisterPage } from './SellerRegisterPage';
import { jsonResponse } from '../test/setup';

function renderSellerRegister() {
  const queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });
  return render(
    <QueryClientProvider client={queryClient}>
      <MemoryRouter initialEntries={['/register/seller']}>
        <AuthProvider>
          <ToastProvider>
            <Routes>
              <Route path="/register/seller" element={<SellerRegisterPage />} />
            </Routes>
          </ToastProvider>
        </AuthProvider>
      </MemoryRouter>
    </QueryClientProvider>,
  );
}

describe('SellerRegisterPage', () => {
  it('affiche le formulaire d’inscription vendeur·se', async () => {
    renderSellerRegister();
    await act(async () => {});

    expect(screen.getByText('Lancez votre boutique')).toBeInTheDocument();
    expect(screen.getByLabelText('Pseudo')).toBeInTheDocument();
    expect(screen.getByLabelText('Email')).toBeInTheDocument();
    expect(screen.getByLabelText('Mot de passe')).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Créer mon compte vendeur·se' })).toBeInTheDocument();
  });

  it('envoie les données vers /api/register/seller puis affiche la confirmation', async () => {
    const fetchMock = vi.fn(async (input: RequestInfo | URL) => {
      const url = String(input);
      if (url.includes('/api/me')) {
        return jsonResponse({ user: null, isSeller: false, isCustomer: false });
      }
      if (url.includes('/api/csrf-token')) {
        return jsonResponse({ token: 'test-token' });
      }
      if (url.includes('/api/register/seller')) {
        return jsonResponse({ user: { uuid: 'u2', email: 'vendeur@example.test' } }, 201);
      }
      return jsonResponse({ error: { message: 'Not Found' } }, 404);
    });
    vi.stubGlobal('fetch', fetchMock);

    renderSellerRegister();
    await act(async () => {});

    fireEvent.change(screen.getByLabelText('Pseudo'), { target: { value: 'Vendeur' } });
    fireEvent.change(screen.getByLabelText('Email'), { target: { value: 'vendeur@example.test' } });
    fireEvent.change(screen.getByLabelText('Mot de passe'), { target: { value: 'secret123' } });
    fireEvent.click(screen.getByRole('button', { name: 'Créer mon compte vendeur·se' }));

    expect(await screen.findByText('Vérifiez votre boîte mail')).toBeInTheDocument();

    const registerCall = fetchMock.mock.calls.find(([input]) => String(input).includes('/api/register/seller'));
    expect(registerCall).toBeDefined();
    const options = (registerCall as unknown[])[1] as RequestInit;
    expect(JSON.parse(String(options.body))).toEqual({
      email: 'vendeur@example.test',
      password: 'secret123',
      displayName: 'Vendeur',
    });
  });
});
