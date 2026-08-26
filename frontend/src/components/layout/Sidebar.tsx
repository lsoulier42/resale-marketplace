import { NavLink } from 'react-router-dom';
import {
  Home,
  ShoppingBag,
  Tags,
  Users,
  Star,
  UserCircle,
  Package,
  MapPin,
  ImageIcon,
  ShieldCheck,
  LogIn,
  LogOut,
  PlusCircle,
} from 'lucide-react';
import { useAuth, useIsAdmin } from '../../auth/useAuth';
import { ApiError } from '../../api/client';

interface SidebarProps {
  open: boolean;
  onClose: () => void;
}

export function Sidebar({ open, onClose }: SidebarProps) {
  const { user, isSeller, logout } = useAuth();
  const isAdmin = useIsAdmin();

  const handleLogout = async () => {
    try {
      await logout();
    } catch (error) {
      if (error instanceof ApiError && error.status !== 401) {
        console.error('Échec de la déconnexion', error);
      }
    } finally {
      onClose();
    }
  };

  const linkClass = ({ isActive }: { isActive: boolean }) =>
    `pill-link${isActive ? ' pill-link--active' : ''}`;

  return (
    <>
      {open && <div className="sidebar-backdrop sidebar-backdrop--visible" onClick={onClose} />}
      <aside className={`sidebar${open ? ' sidebar--open' : ''}`} aria-label="Navigation principale">
        <NavLink to="/" className="sidebar-brand" onClick={onClose}>
          <img src="/favicon.svg" alt="" />
          <span>Resale Marketplace</span>
        </NavLink>

        <p className="sidebar-section-title">Boutique</p>
        <NavLink to="/" end className={linkClass} onClick={onClose}>
          <Home size={18} /> Accueil
        </NavLink>
        <NavLink to="/items" className={linkClass} onClick={onClose}>
          <ShoppingBag size={18} /> Articles
        </NavLink>
        <NavLink to="/categories" className={linkClass} onClick={onClose}>
          <Tags size={18} /> Catégories
        </NavLink>
        <NavLink to="/sellers" className={linkClass} onClick={onClose}>
          <Users size={18} /> Vendeur·ses
        </NavLink>
        <NavLink to="/reviews" className={linkClass} onClick={onClose}>
          <Star size={18} /> Avis
        </NavLink>

        {user && (
          <>
            <p className="sidebar-section-title">Mon espace</p>
            {isSeller && (
              <NavLink to="/items/new" className={linkClass} onClick={onClose}>
                <PlusCircle size={18} /> Publier un article
              </NavLink>
            )}
            <NavLink to="/profile" className={linkClass} onClick={onClose}>
              <UserCircle size={18} /> Mon profil
            </NavLink>
            <NavLink to="/orders" className={linkClass} onClick={onClose}>
              <Package size={18} /> Mes commandes
            </NavLink>
            <NavLink to="/addresses" className={linkClass} onClick={onClose}>
              <MapPin size={18} /> Mes adresses
            </NavLink>
            <NavLink to="/medias" className={linkClass} onClick={onClose}>
              <ImageIcon size={18} /> Médias
            </NavLink>
          </>
        )}

        {isAdmin && (
          <>
            <p className="sidebar-section-title">Administration</p>
            <NavLink to="/admin/users" className={linkClass} onClick={onClose}>
              <ShieldCheck size={18} /> Utilisateurs
            </NavLink>
            <NavLink to="/admin/customers" className={linkClass} onClick={onClose}>
              <ShieldCheck size={18} /> Acheteur·ses
            </NavLink>
          </>
        )}

        <div className="sidebar-spacer" />

        {user ? (
          <div className="sidebar-user">
            <div className="avatar" aria-hidden="true">
              {user.email.charAt(0).toUpperCase()}
            </div>
            <div className="user-meta">
              <div className="user-email">{user.email}</div>
              <button type="button" className="btn btn-ghost btn-sm mt-1" onClick={handleLogout}>
                <LogOut size={14} /> Déconnexion
              </button>
            </div>
          </div>
        ) : (
          <NavLink to="/login" className="btn btn-primary" onClick={onClose}>
            <LogIn size={16} /> Connexion
          </NavLink>
        )}
      </aside>
    </>
  );
}
