/**
 * Client HTTP de l'API Resale Marketplace.
 *
 * - Envoie « Accept: application/json » sur toutes les requêtes.
 * - Récupère le jeton CSRF via GET /api/csrf-token puis l'envoie
 *   dans le header « X-CSRF-Token » sur chaque mutation.
 * - Normalise les erreurs JSON de l'API ({ error: { message } }).
 */

export class ApiError extends Error {
  readonly status: number;

  constructor(status: number, message: string) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
  }
}

let csrfToken: string | null = null;

const MUTATING_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

export async function fetchCsrfToken(): Promise<string> {
  const res = await fetch('/api/csrf-token', { headers: { Accept: 'application/json' } });
  if (!res.ok) {
    throw new ApiError(res.status, 'Impossible de récupérer le jeton CSRF.');
  }
  const data = (await res.json()) as { token: string };
  csrfToken = data.token;
  return csrfToken;
}

export async function apiFetch<T>(path: string, options: RequestInit = {}): Promise<T> {
  const headers = new Headers(options.headers);
  headers.set('Accept', 'application/json');

  const method = (options.method ?? 'GET').toUpperCase();
  const isMutation = MUTATING_METHODS.includes(method);

  if (isMutation && typeof options.body === 'string' && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json');
  }
  if (isMutation) {
    if (!csrfToken) {
      await fetchCsrfToken();
    }
    headers.set('X-CSRF-Token', csrfToken ?? '');
  }

  const res = await fetch(path, { ...options, headers });
  if (!res.ok) {
    throw await toApiError(res);
  }
  if (res.status === 204) {
    return undefined as T;
  }
  return (await res.json()) as T;
}

async function toApiError(res: Response): Promise<ApiError> {
  let message = `Erreur ${res.status}.`;
  try {
    const body = (await res.json()) as { error?: { message?: string } | string };
    if (typeof body.error === 'string') {
      message = body.error;
    } else if (body.error?.message) {
      message = body.error.message;
    }
  } catch {
    // corps non JSON : message générique
  }
  return new ApiError(res.status, message);
}

/**
 * Connexion (json_login Symfony).
 *
 * Envoie un corps JSON { email, password } + le jeton CSRF. Après un login
 * réussi, la migration anti-fixation de session invalide l'ancien jeton :
 * on re-récupère donc un jeton frais.
 */
export async function apiLogin(email: string, password: string): Promise<void> {
  if (!csrfToken) {
    await fetchCsrfToken();
  }
  const res = await fetch('/api/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-CSRF-Token': csrfToken ?? '',
    },
    body: JSON.stringify({ email, password }),
  });
  if (!res.ok) {
    throw await toApiError(res);
  }
  await fetchCsrfToken();
}

export async function apiLogout(): Promise<void> {
  await apiFetch('/api/logout', { method: 'POST' });
  csrfToken = null;
}
