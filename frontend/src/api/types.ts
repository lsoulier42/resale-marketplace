/** Types des DTOs exposés par l'API JSON Resale Marketplace. */

export interface ApiUser {
  uuid: string;
  email: string;
  roles: string[];
  isVerified: boolean;
}

export interface ApiErrorPayload {
  error: {
    message: string;
    violations?: Array<{ property: string; message: string }>;
  };
}

/** Statuts de commande (miroir de src/Enum/OrderStatus.php). */
export type OrderStatus =
  | 'pending_payment'
  | 'paid'
  | 'shipped'
  | 'delivered'
  | 'cancelled';

/* ------------------------------------------------------------
   Vitrine publique (catalogue)
   ------------------------------------------------------------ */

export interface CategoryRef {
  uuid: string;
  title: string;
}

export interface SellerRef {
  uuid: string;
  displayName: string | null;
  avatarUrl: string | null;
}

export interface ItemCardData {
  uuid: string;
  title: string;
  price: string;
  availableCount: number;
  isSold: boolean;
  medias: string[];
  category: CategoryRef;
  seller: SellerRef;
}

export interface ItemDetailData extends ItemCardData {
  description: string | null;
  createdAt: string | null;
}

export interface CategoryData {
  uuid: string;
  title: string;
  description: string | null;
  itemCount: number;
}

export interface CategoryDetailData {
  category: CategoryData;
  items: ItemCardData[];
}

export interface SellerData {
  uuid: string;
  displayName: string | null;
  bio: string | null;
  avatarUrl: string | null;
  itemCount: number;
  reviewCount: number;
  reviewAvg: number | null;
}

export interface SellerDetailData {
  seller: SellerData;
  items: ItemCardData[];
  reviews: ReviewData[];
}

export interface ReviewUserRef {
  uuid: string;
  displayName: string | null;
}

export interface ReviewData {
  uuid: string;
  star: number;
  comment: string | null;
  createdAt: string | null;
  fromUser: ReviewUserRef;
  toUser: ReviewUserRef;
  orderReference: string;
}

export interface HomeData {
  featuredItems: ItemCardData[];
  categories: CategoryData[];
}

export interface Paged<T> {
  items: T[];
  page: number;
  limit: number;
  total: number;
  pages: number;
}

/* ------------------------------------------------------------
   Espace client (commandes, adresses, profil, avis)
   ------------------------------------------------------------ */

export interface OrderAddressData {
  uuid: string;
  name: string;
  addressLine: string;
  city: string;
  zipCode: string;
  country: string;
}

export interface StatusTransition {
  value: OrderStatus;
  label: string;
}

export interface OrderData {
  uuid: string;
  reference: string;
  status: OrderStatus;
  statusLabel: string;
  totalPrice: string;
  shippingFee: string;
  trackingNumber: string | null;
  shippingProvider: string | null;
  createdAt: string | null;
  item: ItemCardData;
  seller: SellerRef;
  shippingAddress: OrderAddressData | null;
  allowedTransitions: StatusTransition[];
  canUpdateStatus: boolean;
  canPay: boolean;
  canReview: boolean;
  hasReview: boolean;
}

export interface AddressData {
  uuid: string;
  name: string;
  addressLine: string;
  city: string;
  zipCode: string;
  country: string;
}

export interface ProfileData {
  uuid: string;
  displayName: string;
  bio: string | null;
  avatarUrl: string | null;
}

export interface OrderStatusOption {
  value: OrderStatus;
  label: string;
  transitions: OrderStatus[];
}

/* ------------------------------------------------------------
   Vendeur·se & admin (Phase 4)
   ------------------------------------------------------------ */

export interface MeResponse {
  user: ApiUser | null;
  isSeller: boolean;
  isCustomer: boolean;
}

export interface StripeStatusData {
  isSeller: boolean;
  onboarded: boolean;
  ready: boolean;
  configured: boolean;
}

export interface OrderCheckoutResponse {
  order: OrderData;
  checkoutUrl: string | null;
}

export interface MediaData {
  uuid: string;
  fileUrl: string;
  fileSize: number;
  fileType: string;
}

export interface AdminUserData {
  uuid: string;
  email: string;
  roles: string[];
  isVerified: boolean;
  createdAt: string | null;
}

export interface AdminCustomerData {
  uuid: string;
  email: string;
  displayName: string | null;
  orderCount: number;
}
