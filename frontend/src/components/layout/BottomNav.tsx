import { NavLink, Link } from 'react-router-dom';
import { Home, LogIn, Plus, ShoppingBag, Tags, UserCircle } from 'lucide-react';
import { useAuth } from '../../auth/useAuth';

/** Bottom-nav mobile : accueil, articles, vendre (centre), catégories, profil. */
export function BottomNav() {
  const { user, isSeller } = useAuth();

  const linkClass = ({ isActive }: { isActive: boolean }) =>
    `bottom-nav-item${isActive ? ' bottom-nav-item--active' : ''}`;

  return (
    <nav className="bottom-nav" aria-label="Navigation mobile">
      <div className="bottom-nav-inner">
        <NavLink to="/" end className={linkClass}>
          <Home size={21} />
          <span>Accueil</span>
        </NavLink>
        <NavLink to="/items" className={linkClass}>
          <ShoppingBag size={21} />
          <span>Articles</span>
        </NavLink>

        {isSeller ? (
          <div className="bottom-nav-center">
            <Link to="/items/new" aria-label="Vendre un article" title="Vendre un article">
              <Plus size={26} />
            </Link>
          </div>
        ) : null}

        <NavLink to="/categories" className={linkClass}>
          <Tags size={21} />
          <span>Catégories</span>
        </NavLink>

        {user ? (
          <NavLink to="/profile" className={linkClass}>
            <UserCircle size={21} />
            <span>Profil</span>
          </NavLink>
        ) : (
          <NavLink to="/login" className={linkClass}>
            <LogIn size={21} />
            <span>Connexion</span>
          </NavLink>
        )}
      </div>
    </nav>
  );
}
