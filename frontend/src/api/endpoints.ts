import { apiFetch } from './client';
import type {
  AddressData,
  AdminCustomerData,
  AdminUserData,
  CategoryData,
  CategoryDetailData,
  HomeData,
  ItemCardData,
  ItemDetailData,
  MediaData,
  OrderCheckoutResponse,
  OrderData,
  Paged,
  ProfileData,
  SellerData,
  SellerDetailData,
  ReviewData,
  StripeStatusData,
} from './types';

export type ItemSort = 'recent' | 'price_asc' | 'price_desc';

interface ItemsParams {
  page?: number;
  limit?: number;
  category?: string;
  q?: string;
  sort?: ItemSort;
}

/** Appels API de la vitrine publique. */
export const catalogApi = {
  home: () => apiFetch<HomeData>('/api/home'),

  items: ({ page, limit, category, q, sort }: ItemsParams = {}) => {
    const qs = new URLSearchParams();
    if (page !== undefined) qs.set('page', String(page));
    if (limit !== undefined) qs.set('limit', String(limit));
    if (category !== undefined && category !== '') qs.set('category', category);
    if (q !== undefined && q !== '') qs.set('q', q);
    if (sort !== undefined && sort !== 'recent') qs.set('sort', sort);
    const query = qs.toString();

    return apiFetch<Paged<ItemCardData>>(`/api/items${query ? `?${query}` : ''}`);
  },

  item: (uuid: string) =>
    apiFetch<{ item: ItemDetailData }>(`/api/items/${encodeURIComponent(uuid)}`),

  categories: () => apiFetch<{ categories: CategoryData[] }>('/api/categories'),

  category: (uuid: string) =>
    apiFetch<CategoryDetailData>(`/api/categories/${encodeURIComponent(uuid)}`),

  sellers: () => apiFetch<{ sellers: SellerData[] }>('/api/sellers'),

  seller: (uuid: string) =>
    apiFetch<SellerDetailData>(`/api/sellers/${encodeURIComponent(uuid)}`),

  reviews: () => apiFetch<{ reviews: ReviewData[] }>('/api/reviews'),
};

interface CreateOrderPayload {
  itemUuid: string;
  addressUuid?: string;
  shippingFee?: string;
}

interface UpdateOrderStatusPayload {
  status: string;
  trackingNumber?: string;
  shippingProvider?: string;
}

interface AddressPayload {
  name: string;
  addressLine: string;
  city: string;
  zipCode: string;
  country: string;
}

interface ProfilePayload {
  displayName: string;
  bio?: string | null;
}

interface CreateReviewPayload {
  orderUuid: string;
  star: number;
  comment?: string | null;
}

/** Appels API de l'espace client. */
export const customerApi = {
  orders: () => apiFetch<{ orders: OrderData[] }>('/api/orders'),

  order: (uuid: string) =>
    apiFetch<{ order: OrderData }>(`/api/orders/${encodeURIComponent(uuid)}`),

  createOrder: (payload: CreateOrderPayload) =>
    apiFetch<OrderCheckoutResponse>('/api/orders', {
      method: 'POST',
      body: JSON.stringify(payload),
    }),

  checkout: (uuid: string) =>
    apiFetch<{ checkoutUrl: string | null }>(`/api/orders/${encodeURIComponent(uuid)}/checkout`, {
      method: 'POST',
    }),

  updateOrderStatus: (uuid: string, payload: UpdateOrderStatusPayload) =>
    apiFetch<{ order: OrderData }>(`/api/orders/${encodeURIComponent(uuid)}/status`, {
      method: 'PATCH',
      body: JSON.stringify(payload),
    }),

  addresses: () => apiFetch<{ addresses: AddressData[] }>('/api/addresses'),

  createAddress: (payload: AddressPayload) =>
    apiFetch<{ address: AddressData }>('/api/addresses', {
      method: 'POST',
      body: JSON.stringify(payload),
    }),

  updateAddress: (uuid: string, payload: AddressPayload) =>
    apiFetch<{ address: AddressData }>(`/api/addresses/${encodeURIComponent(uuid)}`, {
      method: 'PUT',
      body: JSON.stringify(payload),
    }),

  deleteAddress: (uuid: string) =>
    apiFetch<{ deleted: boolean }>(`/api/addresses/${encodeURIComponent(uuid)}`, {
      method: 'DELETE',
    }),

  profile: () => apiFetch<{ profile: ProfileData | null }>('/api/profile'),

  updateProfile: (payload: ProfilePayload) =>
    apiFetch<{ profile: ProfileData }>('/api/profile', {
      method: 'PUT',
      body: JSON.stringify(payload),
    }),

  createReview: (payload: CreateReviewPayload) =>
    apiFetch<{ review: ReviewData }>('/api/reviews', {
      method: 'POST',
      body: JSON.stringify(payload),
    }),
};

interface ItemPayload {
  title: string;
  description?: string | null;
  price: string;
  availableCount: number;
  categoryUuid: string;
  mediaUuids?: string[];
}

interface RegisterPayload {
  email: string;
  password: string;
  displayName: string;
}

/** Appels API d'authentification & inscription. */
export const authApi = {
  register: (payload: RegisterPayload) =>
    apiFetch<{ user: { uuid: string; email: string } }>('/api/register', {
      method: 'POST',
      body: JSON.stringify(payload),
    }),

  registerSeller: (payload: RegisterPayload) =>
    apiFetch<{ user: { uuid: string; email: string } }>('/api/register/seller', {
      method: 'POST',
      body: JSON.stringify(payload),
    }),

  confirmEmail: (token: string) =>
    apiFetch<{ confirmed: boolean }>(`/api/register/confirm/${encodeURIComponent(token)}`),

  resendConfirmation: (email: string) =>
    apiFetch<{ sent: boolean }>('/api/register/resend-confirmation', {
      method: 'POST',
      body: JSON.stringify({ email }),
    }),

  becomeSeller: () =>
    apiFetch<{ isSeller: boolean }>('/api/me/seller', {
      method: 'POST',
    }),
};

interface AdminUserPayload {
  email: string;
  password?: string | null;
  roles: string[];
  isVerified?: boolean;
}

/** Appels API vendeur·se & admin. */
export const sellerApi = {
  createItem: (payload: ItemPayload) =>
    apiFetch<{ item: ItemDetailData }>('/api/items', {
      method: 'POST',
      body: JSON.stringify(payload),
    }),

  updateItem: (uuid: string, payload: ItemPayload) =>
    apiFetch<{ item: ItemDetailData }>(`/api/items/${encodeURIComponent(uuid)}`, {
      method: 'PUT',
      body: JSON.stringify(payload),
    }),

  deleteItem: (uuid: string) =>
    apiFetch<{ deleted: boolean }>(`/api/items/${encodeURIComponent(uuid)}`, {
      method: 'DELETE',
    }),

  medias: () => apiFetch<{ medias: MediaData[] }>('/api/medias'),

  uploadMedia: (file: File) => {
    const formData = new FormData();
    formData.append('file', file);
    return apiFetch<{ media: MediaData }>('/api/medias', {
      method: 'POST',
      body: formData,
    });
  },

  deleteMedia: (uuid: string) =>
    apiFetch<{ deleted: boolean }>(`/api/medias/${encodeURIComponent(uuid)}`, {
      method: 'DELETE',
    }),

  users: () => apiFetch<{ users: AdminUserData[] }>('/api/users'),

  createUser: (payload: AdminUserPayload) =>
    apiFetch<{ user: AdminUserData }>('/api/users', {
      method: 'POST',
      body: JSON.stringify(payload),
    }),

  updateUser: (uuid: string, payload: AdminUserPayload) =>
    apiFetch<{ user: AdminUserData }>(`/api/users/${encodeURIComponent(uuid)}`, {
      method: 'PUT',
      body: JSON.stringify(payload),
    }),

  deleteUser: (uuid: string) =>
    apiFetch<{ deleted: boolean }>(`/api/users/${encodeURIComponent(uuid)}`, {
      method: 'DELETE',
    }),

  customers: () => apiFetch<{ customers: AdminCustomerData[] }>('/api/customers'),

  deleteCustomer: (uuid: string) =>
    apiFetch<{ deleted: boolean }>(`/api/customers/${encodeURIComponent(uuid)}`, {
      method: 'DELETE',
    }),
};

/** Appels API du paiement Stripe (espace vendeur·se). */
export const stripeApi = {
  status: () => apiFetch<{ stripe: StripeStatusData }>('/api/me/stripe'),

  onboarding: () =>
    apiFetch<{ url: string }>('/api/me/stripe/onboarding', {
      method: 'POST',
    }),
};
