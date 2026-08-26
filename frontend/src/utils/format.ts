const euroFormatter = new Intl.NumberFormat('fr-FR', {
  style: 'currency',
  currency: 'EUR',
});

/** Formate un montant DECIMAL (string) en euros français. */
export function formatPrice(price: string): string {
  const value = Number.parseFloat(price);
  return Number.isFinite(value) ? euroFormatter.format(value) : price;
}

/** Formate une date ISO en date courte française (ex. 12 mars 2026). */
export function formatDate(iso: string | null): string {
  if (!iso) {
    return '';
  }
  const date = new Date(iso);
  return Number.isNaN(date.getTime())
    ? ''
    : new Intl.DateTimeFormat('fr-FR', { dateStyle: 'long' }).format(date);
}
