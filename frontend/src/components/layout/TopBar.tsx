import { Menu, UserCircle } from 'lucide-react';
import { Link } from 'react-router-dom';
import { useAuth } from '../../auth/useAuth';

interface TopBarProps {
  title: string;
  onOpenSidebar: () => void;
}

export function TopBar({ title, onOpenSidebar }: TopBarProps) {
  const { user } = useAuth();

  return (
    <header className="topbar">
      <button
        type="button"
        className="topbar-burger"
        onClick={onOpenSidebar}
        aria-label="Ouvrir le menu"
      >
        <Menu size={22} />
      </button>
      <span className="topbar-title">{title}</span>
      <div className="topbar-actions">
        {user && (
          <Link to="/profile" className="pill-link" title={user.email}>
            <UserCircle size={20} />
            <span className="user-email">{user.email}</span>
          </Link>
        )}
      </div>
    </header>
  );
}
