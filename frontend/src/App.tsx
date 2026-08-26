import { lazy, Suspense } from 'react';
import { BrowserRouter, Route, Routes } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { AuthProvider } from './auth/AuthContext';
import { ToastProvider } from './components/ui/Toast';
import { RequireAuth } from './auth/RequireAuth';
import { RequireAdmin } from './auth/RequireAdmin';
import { AppLayout } from './components/layout/AppLayout';
import { LoginPage } from './pages/LoginPage';
import { RegisterPage } from './pages/RegisterPage';
import { SellerRegisterPage } from './pages/SellerRegisterPage';
import { ConfirmEmailPage } from './pages/ConfirmEmailPage';
import { NotFoundPage } from './pages/NotFoundPage';
import { Skeleton } from './components/ui/Skeleton';

// Code-splitting : chaque page est chargée à la demande.
const HomePage = lazy(() => import('./pages/HomePage').then((m) => ({ default: m.HomePage })));
const ItemsPage = lazy(() => import('./pages/ItemsPage').then((m) => ({ default: m.ItemsPage })));
const ItemDetailPage = lazy(() =>
  import('./pages/ItemDetailPage').then((m) => ({ default: m.ItemDetailPage })),
);
const ItemFormPage = lazy(() =>
  import('./pages/ItemFormPage').then((m) => ({ default: m.ItemFormPage })),
);
const CategoriesPage = lazy(() =>
  import('./pages/CategoriesPage').then((m) => ({ default: m.CategoriesPage })),
);
const CategoryDetailPage = lazy(() =>
  import('./pages/CategoryDetailPage').then((m) => ({ default: m.CategoryDetailPage })),
);
const SellersPage = lazy(() =>
  import('./pages/SellersPage').then((m) => ({ default: m.SellersPage })),
);
const SellerDetailPage = lazy(() =>
  import('./pages/SellerDetailPage').then((m) => ({ default: m.SellerDetailPage })),
);
const ReviewsPage = lazy(() =>
  import('./pages/ReviewsPage').then((m) => ({ default: m.ReviewsPage })),
);
const OrdersPage = lazy(() => import('./pages/OrdersPage').then((m) => ({ default: m.OrdersPage })));
const OrderDetailPage = lazy(() =>
  import('./pages/OrderDetailPage').then((m) => ({ default: m.OrderDetailPage })),
);
const OrderNewPage = lazy(() =>
  import('./pages/OrderNewPage').then((m) => ({ default: m.OrderNewPage })),
);
const ProfilePage = lazy(() =>
  import('./pages/ProfilePage').then((m) => ({ default: m.ProfilePage })),
);
const AddressesPage = lazy(() =>
  import('./pages/AddressesPage').then((m) => ({ default: m.AddressesPage })),
);
const MediasPage = lazy(() => import('./pages/MediasPage').then((m) => ({ default: m.MediasPage })));
const AdminUsersPage = lazy(() =>
  import('./pages/AdminUsersPage').then((m) => ({ default: m.AdminUsersPage })),
);
const AdminCustomersPage = lazy(() =>
  import('./pages/AdminCustomersPage').then((m) => ({ default: m.AdminCustomersPage })),
);

function PageFallback() {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
      <Skeleton height={28} width={240} />
      <Skeleton height={160} borderRadius={18} />
      <Skeleton height={160} borderRadius={18} />
    </div>
  );
}

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      staleTime: 30_000,
      refetchOnWindowFocus: false,
    },
  },
});

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <AuthProvider>
          <ToastProvider>
            <Suspense fallback={<PageFallback />}>
              <Routes>
                <Route path="/login" element={<LoginPage />} />
                <Route path="/register" element={<RegisterPage />} />
                <Route path="/register/seller" element={<SellerRegisterPage />} />
                <Route path="/confirm-email" element={<ConfirmEmailPage />} />
                <Route element={<AppLayout />}>
                  {/* Vitrine publique */}
                  <Route path="/" element={<HomePage />} />
                  <Route path="/items" element={<ItemsPage />} />
                  <Route path="/items/:uuid" element={<ItemDetailPage />} />
                  <Route path="/categories" element={<CategoriesPage />} />
                  <Route path="/categories/:uuid" element={<CategoryDetailPage />} />
                  <Route path="/sellers" element={<SellersPage />} />
                  <Route path="/sellers/:uuid" element={<SellerDetailPage />} />
                  <Route path="/reviews" element={<ReviewsPage />} />

                  {/* Espace client */}
                  <Route
                    path="/orders"
                    element={
                      <RequireAuth>
                        <OrdersPage />
                      </RequireAuth>
                    }
                  />
                  <Route
                    path="/orders/:uuid"
                    element={
                      <RequireAuth>
                        <OrderDetailPage />
                      </RequireAuth>
                    }
                  />
                  <Route
                    path="/orders/new/:itemUuid"
                    element={
                      <RequireAuth>
                        <OrderNewPage />
                      </RequireAuth>
                    }
                  />
                  <Route
                    path="/profile"
                    element={
                      <RequireAuth>
                        <ProfilePage />
                      </RequireAuth>
                    }
                  />
                  <Route
                    path="/addresses"
                    element={
                      <RequireAuth>
                        <AddressesPage />
                      </RequireAuth>
                    }
                  />

                  {/* Vendeur·se & admin */}
                  <Route
                    path="/items/new"
                    element={
                      <RequireAuth>
                        <ItemFormPage />
                      </RequireAuth>
                    }
                  />
                  <Route
                    path="/items/:uuid/edit"
                    element={
                      <RequireAuth>
                        <ItemFormPage />
                      </RequireAuth>
                    }
                  />
                  <Route
                    path="/medias"
                    element={
                      <RequireAuth>
                        <MediasPage />
                      </RequireAuth>
                    }
                  />
                  <Route
                    path="/admin/users"
                    element={
                      <RequireAdmin>
                        <AdminUsersPage />
                      </RequireAdmin>
                    }
                  />
                  <Route
                    path="/admin/customers"
                    element={
                      <RequireAdmin>
                        <AdminCustomersPage />
                      </RequireAdmin>
                    }
                  />
                  <Route path="*" element={<NotFoundPage />} />
                </Route>
              </Routes>
            </Suspense>
          </ToastProvider>
        </AuthProvider>
      </BrowserRouter>
    </QueryClientProvider>
  );
}
