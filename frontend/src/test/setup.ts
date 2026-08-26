import { afterEach, beforeEach, vi } from 'vitest';
import '@testing-library/jest-dom/vitest';

/**
 * Mock fetch par défaut : /api/me → visiteur, tout le reste → 404.
 * Les tests qui ont besoin de données remplacent ce mock via vi.stubGlobal.
 */
beforeEach(() => {
  vi.stubGlobal('fetch', vi.fn(async (input: RequestInfo | URL) => {
    const url = String(input);
    if (url.includes('/api/me')) {
      return jsonResponse({ user: null });
    }
    return jsonResponse({ error: { message: 'Not Found' } }, 404);
  }));
});

afterEach(() => {
  vi.unstubAllGlobals();
});

export function jsonResponse(body: unknown, status = 200): Response {
  return new Response(JSON.stringify(body), {
    status,
    headers: { 'Content-Type': 'application/json' },
  });
}
