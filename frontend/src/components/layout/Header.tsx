import { useEffect, useRef, useState, type FormEvent } from 'react';
import { NavLink, Link, useNavigate } from 'react-router-dom';
import {
  ChevronDown,
  Home,
  ImageIcon,
  LogIn,
  LogOut,
  MapPin,
  Menu,
  Package,
  PlusCircle,
  Search,
  ShieldCheck,
  ShoppingBag,
  Star,
  Tags,
  UserCircle,
  Users,
  X,
} from 'lucide-react';
import { useAuth, useIsAdmin } from '../../auth/useAuth';
import { ApiError } from '../../api/client';

interface HeaderProps {
  drawerOpen: boolean;
  onOpenDrawer: () => void;
  onCloseDrawer: () => void;
}

/** En-tête marketplace : logo, recherche, actions compte, nav + drawer mobile. */
export function Header({ drawerOpen, onOpenDrawer, onCloseDrawer }: HeaderProps) {
  const { user, isSeller, logout } = useAuth();
  const isAdmin = useIsAdmin();
  const navigate = useNavigate();
  const [query, setQuery] = useState('');
  const [menuOpen, setMenuOpen] = useState(false);
  const menuRef = useRef<HTMLDivElement>(null);

  // Ferme le menu compte au clic extérieur ou à Échap.
  useEffect(() => {
    if (!menuOpen) {
      return;
    }
    const onPointerDown = (event: PointerEvent) => {
      if (menuRef.current && !menuRef.current.contains(event.target as Node)) {
        setMenuOpen(false);
      }
    };
    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        setMenuOpen(false);
      }
    };
    document.addEventListener('pointerdown', onPointerDown);
    document.addEventListener('keydown', onKeyDown);
    return () => {
      document.removeEventListener('pointerdown', onPointerDown);
      document.removeEventListener('keydown', onKeyDown);
    };
  }, [menuOpen]);

  const handleSearch = (event: FormEvent) => {
    event.preventDefault();
    const q = query.trim();
    navigate(q !== '' ? `/items?q=${encodeURIComponent(q)}` : '/items');
    setQuery('');
  };

  const handleLogout = async () => {
    setMenuOpen(false);
    try {
      await logout();
    } catch (error) {
      if (error instanceof ApiError && error.status !== 401) {
        console.error('Échec de la déconnexion', error);
      }
    } finally {
      onCloseDrawer();
      navigate('/');
    }
  };

  const navLinkClass = ({ isActive }: { isActive: boolean }) =>
    `nav-link${isActive ? ' nav-link--active' : ''}`;

  const drawerFooter = user ? (
    <>
      <div className="drawer-user">
        <div className="account-avatar" aria-hidden="true">
          {user.email.charAt(0).toUpperCase()}
        </div>
        <span className="user-email" title={user.email}>
          {user.email}
        </span>
      </div>
      <button type="button" className="btn btn-secondary btn-block" onClick={handleLogout}>
        <LogOut size={15} /> Déconnexion
      </button>
    </>
  ) : (
    <>
      <Link to="/login" className="btn btn-primary btn-block" onClick={onCloseDrawer}>
        <LogIn size={15} /> Connexion
      </Link>
      <Link to="/register" className="btn btn-secondary btn-block" onClick={onCloseDrawer}>
        Créer un compte
      </Link>
    </>
  );

  return (
    <header className="site-header">
      <div className="header-row">
        <button
          type="button"
          className="header-burger"
          onClick={onOpenDrawer}
          aria-label="Ouvrir le menu"
        >
          <Menu size={22} />
        </button>

        <Link to="/" className="header-brand" onClick={onCloseDrawer}>
          <img src="/favicon.svg" alt="" />
          <span>Resale Marketplace</span>
        </Link>

        <form className="header-search" role="search" onSubmit={handleSearch}>
          <Search size={17} aria-hidden="true" />
          <input
            type="search"
            placeholder="Rechercher un article…"
            aria-label="Rechercher un article"
            value={query}
            onChange={(event) => setQuery(event.target.value)}
          />
        </form>

        <div className="header-actions">
          {user ? (
            <div className="account-menu-wrap" ref={menuRef}>
              <button
                type="button"
                className="account-trigger"
                onClick={() => setMenuOpen((open) => !open)}
                aria-expanded={menuOpen}
                aria-haspopup="menu"
              >
                <span className="account-avatar" aria-hidden="true">
                  {user.email.charAt(0).toUpperCase()}
                </span>
                <ChevronDown size={15} />
              </button>
              {menuOpen && (
                <div className="account-menu" role="menu" aria-label="Mon compte">
                  <p className="account-menu-email">{user.email}</p>
                  <Link to="/profile" className="account-menu-link" onClick={() => setMenuOpen(false)}>
                    <UserCircle size={16} /> Mon profil
                  </Link>
                  <Link to="/orders" className="account-menu-link" onClick={() => setMenuOpen(false)}>
                    <Package size={16} /> Mes commandes
                  </Link>
                  <Link to="/addresses" className="account-menu-link" onClick={() => setMenuOpen(false)}>
                    <MapPin size={16} /> Mes adresses
                  </Link>
                  <Link to="/medias" className="account-menu-link" onClick={() => setMenuOpen(false)}>
                    <ImageIcon size={16} /> Médias
                  </Link>
                  {isAdmin && (
                    <>
                      <div className="account-menu-separator" />
                      <Link
                        to="/admin/users"
                        className="account-menu-link"
                        onClick={() => setMenuOpen(false)}
                      >
                        <ShieldCheck size={16} /> Utilisateurs
                      </Link>
                      <Link
                        to="/admin/customers"
                        className="account-menu-link"
                        onClick={() => setMenuOpen(false)}
                      >
                        <ShieldCheck size={16} /> Acheteur·ses
                      </Link>
                    </>
                  )}
                  <div className="account-menu-separator" />
                  <button type="button" className="account-menu-link" onClick={handleLogout}>
                    <LogOut size={16} /> Déconnexion
                  </button>
                </div>
              )}
            </div>
          ) : (
            <>
              <Link to="/login" className="btn btn-ghost">
                Connexion
              </Link>
              <Link to="/register/seller" className="btn btn-primary">
                <PlusCircle size={16} /> Vendre un article
              </Link>
            </>
          )}
          {user && isSeller && (
            <Link to="/items/new" className="btn btn-primary">
              <PlusCircle size={16} /> Vendre un article
            </Link>
          )}
        </div>
      </div>

      <nav className="site-nav" aria-label="Navigation principale">
        <NavLink to="/" end className={navLinkClass}>
          <Home size={16} /> Accueil
        </NavLink>
        <NavLink to="/items" className={navLinkClass}>
          <ShoppingBag size={16} /> Articles
        </NavLink>
        <NavLink to="/categories" className={navLinkClass}>
          <Tags size={16} /> Catégories
        </NavLink>
        <NavLink to="/sellers" className={navLinkClass}>
          <Users size={16} /> Vendeur·ses
        </NavLink>
        <NavLink to="/reviews" className={navLinkClass}>
          <Star size={16} /> Avis
        </NavLink>
        {isAdmin && (
          <>
            <NavLink to="/admin/users" className={navLinkClass}>
              <ShieldCheck size={16} /> Utilisateurs
            </NavLink>
            <NavLink to="/admin/customers" className={navLinkClass}>
              <ShieldCheck size={16} /> Acheteur·ses
            </NavLink>
          </>
        )}
      </nav>

      {drawerOpen && (
        <div className="drawer-backdrop drawer-backdrop--visible" onClick={onCloseDrawer} />
      )}
      <aside
        className={`drawer${drawerOpen ? ' drawer--open' : ''}`}
        aria-label="Navigation mobile"
      >
        <div className="drawer-header">
          <Link to="/" className="header-brand" onClick={onCloseDrawer}>
            <img src="/favicon.svg" alt="" />
            <span>Resale Marketplace</span>
          </Link>
          <button type="button" className="drawer-close" onClick={onCloseDrawer} aria-label="Fermer le menu">
            <X size={20} />
          </button>
        </div>
        <div className="drawer-body">
          <p className="drawer-section-title">Boutique</p>
          <NavLink to="/" end className={navLinkClass} onClick={onCloseDrawer}>
            <Home size={18} /> Accueil
          </NavLink>
          <NavLink to="/items" className={navLinkClass} onClick={onCloseDrawer}>
            <ShoppingBag size={18} /> Articles
          </NavLink>
          <NavLink to="/categories" className={navLinkClass} onClick={onCloseDrawer}>
            <Tags size={18} /> Catégories
          </NavLink>
          <NavLink to="/sellers" className={navLinkClass} onClick={onCloseDrawer}>
            <Users size={18} /> Vendeur·ses
          </NavLink>
          <NavLink to="/reviews" className={navLinkClass} onClick={onCloseDrawer}>
            <Star size={18} /> Avis
          </NavLink>

          {user && (
            <>
              <p className="drawer-section-title">Mon espace</p>
              {isSeller && (
                <NavLink to="/items/new" className={navLinkClass} onClick={onCloseDrawer}>
                  <PlusCircle size={18} /> Publier un article
                </NavLink>
              )}
              <NavLink to="/profile" className={navLinkClass} onClick={onCloseDrawer}>
                <UserCircle size={18} /> Mon profil
              </NavLink>
              <NavLink to="/orders" className={navLinkClass} onClick={onCloseDrawer}>
                <Package size={18} /> Mes commandes
              </NavLink>
              <NavLink to="/addresses" className={navLinkClass} onClick={onCloseDrawer}>
                <MapPin size={18} /> Mes adresses
              </NavLink>
              <NavLink to="/medias" className={navLinkClass} onClick={onCloseDrawer}>
                <ImageIcon size={18} /> Médias
              </NavLink>
            </>
          )}

          {isAdmin && (
            <>
              <p className="drawer-section-title">Administration</p>
              <NavLink to="/admin/users" className={navLinkClass} onClick={onCloseDrawer}>
                <ShieldCheck size={18} /> Utilisateurs
              </NavLink>
              <NavLink to="/admin/customers" className={navLinkClass} onClick={onCloseDrawer}>
                <ShieldCheck size={18} /> Acheteur·ses
              </NavLink>
            </>
          )}

          <div className="drawer-spacer" />
        </div>
        <div className="drawer-footer">{drawerFooter}</div>
      </aside>
    </header>
  );
}
