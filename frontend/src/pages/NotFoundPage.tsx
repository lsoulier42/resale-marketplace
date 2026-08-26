import { Link } from 'react-router-dom';
import { Card } from '../components/ui/Card';

export function NotFoundPage() {
  return (
    <Card style={{ padding: '3rem', textAlign: 'center', maxWidth: 520, margin: '4rem auto' }}>
      <p style={{ fontSize: '3rem', fontWeight: 800, background: 'var(--color-accent)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent' }}>
        404
      </p>
      <h1 className="mt-2" style={{ fontSize: '1.3rem' }}>Page introuvable</h1>
      <p className="text-muted mt-1">Cette page n'existe pas (encore ?).</p>
      <Link to="/" className="btn btn-primary mt-3">
        Retour à l'accueil
      </Link>
    </Card>
  );
}
