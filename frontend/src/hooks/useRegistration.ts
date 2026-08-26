import { useMutation } from '@tanstack/react-query';
import { authApi } from '../api/endpoints';

/** Inscription client (compte créé non vérifié : email de confirmation envoyé). */
export function useRegister() {
  return useMutation({ mutationFn: authApi.register });
}

/** Inscription vendeur·se (compte client + vendeur·se). */
export function useRegisterSeller() {
  return useMutation({ mutationFn: authApi.registerSeller });
}

/** Confirmation d'email via le jeton reçu par mail. */
export function useConfirmEmail() {
  return useMutation({ mutationFn: authApi.confirmEmail });
}

/** Renvoi de l'email de confirmation (réponse générique côté API). */
export function useResendConfirmation() {
  return useMutation({ mutationFn: authApi.resendConfirmation });
}

/** Transforme le compte connecté en vendeur·se. */
export function useBecomeSeller() {
  return useMutation({ mutationFn: authApi.becomeSeller });
}
