import { useState } from 'react';
import { Outlet, useLocation } from 'react-router-dom';
import { Sidebar } from './Sidebar';
import { TopBar } from './TopBar';

const PAGE_TITLES: Record<string, string> = {
  '/': 'Accueil',
  '/items': 'Articles',
  '/categories': 'Catégories',
  '/sellers': 'Vendeur·ses',
  '/reviews': 'Avis',
  '/profile': 'Mon profil',
  '/orders': 'Mes commandes',
  '/addresses': 'Mes adresses',
  '/medias': 'Médias',
};

export function AppLayout() {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const location = useLocation();

  const title = resolveTitle(location.pathname);

  return (
    <div className="app-shell">
      <Sidebar open={sidebarOpen} onClose={() => setSidebarOpen(false)} />
      <div className="app-main">
        <TopBar title={title} onOpenSidebar={() => setSidebarOpen(true)} />
        <main className="app-content">
          <Outlet />
        </main>
      </div>
    </div>
  );
}

function resolveTitle(pathname: string): string {
  if (PAGE_TITLES[pathname]) {
    return PAGE_TITLES[pathname];
  }
  if (pathname.startsWith('/items/')) {
    return 'Article';
  }
  if (pathname.startsWith('/categories/')) {
    return 'Catégorie';
  }
  if (pathname.startsWith('/sellers/')) {
    return 'Vendeur·se';
  }
  if (pathname.startsWith('/orders/')) {
    return 'Commande';
  }
  return 'Resale Marketplace';
}
