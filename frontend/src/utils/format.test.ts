import { describe, expect, it } from 'vitest';
import { formatDate, formatPrice } from './format';

// Intl.NumberFormat('fr-FR') sépare le montant de la devise par une
// espace insécable (U+00A0).
const NBSP = '\u00A0';

describe('formatPrice', () => {
  it('formate un montant DECIMAL en euros français', () => {
    expect(formatPrice('12.00')).toBe(`12,00${NBSP}€`);
    expect(formatPrice('39.90')).toBe(`39,90${NBSP}€`);
    expect(formatPrice('0.50')).toBe(`0,50${NBSP}€`);
  });

  it('retourne la valeur brute si le montant est invalide', () => {
    expect(formatPrice('abc')).toBe('abc');
    expect(formatPrice('')).toBe('');
  });
});

describe('formatDate', () => {
  it('formate une date ISO en français', () => {
    expect(formatDate('2026-08-10T10:00:00+00:00')).toBe('10 août 2026');
  });

  it('retourne une chaîne vide pour null ou invalide', () => {
    expect(formatDate(null)).toBe('');
    expect(formatDate('pas-une-date')).toBe('');
  });
});
