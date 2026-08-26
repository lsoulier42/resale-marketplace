import { ChevronLeft, ChevronRight } from 'lucide-react';

interface PaginationProps {
  page: number;
  pages: number;
  onChange: (page: number) => void;
}

const MAX_VISIBLE = 5;

/** Pagination en pilules verre, avec fenêtrage des numéros de page. */
export function Pagination({ page, pages, onChange }: PaginationProps) {
  if (pages <= 1) {
    return null;
  }

  const start = Math.max(1, Math.min(page - 2, pages - MAX_VISIBLE + 1));
  const end = Math.min(pages, start + MAX_VISIBLE - 1);
  const numbers: number[] = [];
  for (let i = start; i <= end; i++) {
    numbers.push(i);
  }

  return (
    <nav className="pagination" aria-label="Pagination">
      <button
        type="button"
        className="page-btn"
        disabled={page <= 1}
        onClick={() => onChange(page - 1)}
        aria-label="Page précédente"
      >
        <ChevronLeft size={17} />
      </button>
      {numbers.map((number) => (
        <button
          key={number}
          type="button"
          className={`page-btn${number === page ? ' page-btn--active' : ''}`}
          aria-current={number === page ? 'page' : undefined}
          onClick={() => onChange(number)}
        >
          {number}
        </button>
      ))}
      <button
        type="button"
        className="page-btn"
        disabled={page >= pages}
        onClick={() => onChange(page + 1)}
        aria-label="Page suivante"
      >
        <ChevronRight size={17} />
      </button>
    </nav>
  );
}
