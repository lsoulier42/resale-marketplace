import { useCallback, useMemo, useRef, useState, type ReactNode } from 'react';
import { CheckCircle2, AlertTriangle, Info, XCircle, X } from 'lucide-react';
import { ToastContext, type Toast, type ToastType, type ToastContextValue } from './toastContext';

const TOAST_ICONS: Record<ToastType, ReactNode> = {
  success: <CheckCircle2 size={18} style={{ color: '#16a34a' }} />,
  error: <XCircle size={18} style={{ color: '#e11d48' }} />,
  warning: <AlertTriangle size={18} style={{ color: '#d97706' }} />,
  info: <Info size={18} style={{ color: '#7c3aed' }} />,
};

const TOAST_DURATION_MS = 4000;

export function ToastProvider({ children }: { children: ReactNode }) {
  const [toasts, setToasts] = useState<Toast[]>([]);
  const nextId = useRef(1);

  const dismiss = useCallback((id: number) => {
    setToasts((current) => current.filter((toast) => toast.id !== id));
  }, []);

  const push = useCallback(
    (type: ToastType, message: string) => {
      const id = nextId.current++;
      setToasts((current) => [...current, { id, type, message }]);
      window.setTimeout(() => dismiss(id), TOAST_DURATION_MS);
    },
    [dismiss],
  );

  const value = useMemo<ToastContextValue>(
    () => ({
      success: (message) => push('success', message),
      error: (message) => push('error', message),
      warning: (message) => push('warning', message),
      info: (message) => push('info', message),
    }),
    [push],
  );

  return (
    <ToastContext.Provider value={value}>
      {children}
      <div className="toast-stack" aria-live="polite">
        {toasts.map((toast) => (
          <div key={toast.id} className={`toast toast--${toast.type}`} role="status">
            {TOAST_ICONS[toast.type]}
            <span style={{ flex: 1 }}>{toast.message}</span>
            <button
              type="button"
              className="modal-close"
              onClick={() => dismiss(toast.id)}
              aria-label="Fermer la notification"
            >
              <X size={15} />
            </button>
          </div>
        ))}
      </div>
    </ToastContext.Provider>
  );
}
