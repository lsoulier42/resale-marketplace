import { describe, expect, it, vi } from 'vitest';
import { act } from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { AuthProvider } from '../auth/AuthContext';
import { ToastProvider } from '../components/ui/Toast';
import { RegisterPage } from './RegisterPage';
import { jsonResponse } from '../test/setup';

function renderRegister() {
  const queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });
  return render(
    <QueryClientProvider client={queryClient}>
      <MemoryRouter initialEntries={['/register']}>
        <AuthProvider>
          <ToastProvider>
            <Routes>
              <Route path="/register" element={<RegisterPage />} />
              <Route path="/login" element={<div>Page de connexion</div>} />
            </Routes>
          </ToastProvider>
        </AuthProvider>
      </MemoryRouter>
    </QueryClientProvider>,
  );
}

describe('RegisterPage', () => {
  it('affiche le formulaire d’inscription client', async () => {
    renderRegister();
    await act(async () => {});

    expect(screen.getByLabelText('Pseudo')).toBeInTheDocument();
    expect(screen.getByLabelText('Email')).toBeInTheDocument();
    expect(screen.getByLabelText('Mot de passe')).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Créer mon compte' })).toBeInTheDocument();
    expect(screen.getByText(/Créer un compte vendeur·se/)).toBeInTheDocument();
  });

  it('envoie les données puis affiche l’écran de confirmation email', async () => {
    const fetchMock = vi.fn(async (input: RequestInfo | URL) => {
      const url = String(input);
      if (url.includes('/api/me')) {
        return jsonResponse({ user: null, isSeller: false, isCustomer: false });
      }
      if (url.includes('/api/csrf-token')) {
        return jsonResponse({ token: 'test-token' });
      }
      if (url.includes('/api/register')) {
        return jsonResponse({ user: { uuid: 'u1', email: 'camille2@example.test' } }, 201);
      }
      return jsonResponse({ error: { message: 'Not Found' } }, 404);
    });
    vi.stubGlobal('fetch', fetchMock);

    renderRegister();
    await act(async () => {});

    fireEvent.change(screen.getByLabelText('Pseudo'), { target: { value: 'Camille 2' } });
    fireEvent.change(screen.getByLabelText('Email'), { target: { value: 'camille2@example.test' } });
    fireEvent.change(screen.getByLabelText('Mot de passe'), { target: { value: 'secret123' } });
    fireEvent.click(screen.getByRole('button', { name: 'Créer mon compte' }));

    expect(await screen.findByText('Vérifiez votre boîte mail')).toBeInTheDocument();

    const registerCall = fetchMock.mock.calls.find(([input]) => String(input).includes('/api/register'));
    expect(registerCall).toBeDefined();
    const options = (registerCall as unknown[])[1] as RequestInit;
    expect(JSON.parse(String(options.body))).toEqual({
      email: 'camille2@example.test',
      password: 'secret123',
      displayName: 'Camille 2',
    });
  });

  it('affiche une erreur si l’inscription échoue', async () => {
    const fetchMock = vi.fn(async (input: RequestInfo | URL) => {
      const url = String(input);
      if (url.includes('/api/me')) {
        return jsonResponse({ user: null, isSeller: false, isCustomer: false });
      }
      if (url.includes('/api/csrf-token')) {
        return jsonResponse({ token: 'test-token' });
      }
      if (url.includes('/api/register')) {
        return jsonResponse({ error: { message: 'Cet email est déjà utilisé.' } }, 422);
      }
      return jsonResponse({ error: { message: 'Not Found' } }, 404);
    });
    vi.stubGlobal('fetch', fetchMock);

    renderRegister();
    await act(async () => {});

    fireEvent.change(screen.getByLabelText('Pseudo'), { target: { value: 'Camille 2' } });
    fireEvent.change(screen.getByLabelText('Email'), { target: { value: 'prise@example.test' } });
    fireEvent.change(screen.getByLabelText('Mot de passe'), { target: { value: 'secret123' } });
    fireEvent.click(screen.getByRole('button', { name: 'Créer mon compte' }));

    expect(await screen.findByText('Cet email est déjà utilisé.')).toBeInTheDocument();
  });
});
