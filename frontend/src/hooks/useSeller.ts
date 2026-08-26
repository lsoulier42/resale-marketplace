import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { sellerApi, stripeApi } from '../api/endpoints';

export function useStripeStatus() {
  return useQuery({ queryKey: ['stripe-status'], queryFn: stripeApi.status });
}

export function useStripeOnboarding() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: stripeApi.onboarding,
    onSuccess: () => void queryClient.invalidateQueries({ queryKey: ['stripe-status'] }),
  });
}

export function useStripeRefresh() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: stripeApi.refresh,
    onSuccess: () => void queryClient.invalidateQueries({ queryKey: ['stripe-status'] }),
  });
}

export function useMedias() {
  return useQuery({ queryKey: ['medias'], queryFn: sellerApi.medias });
}

export function useUploadMedia() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: sellerApi.uploadMedia,
    onSuccess: () => void queryClient.invalidateQueries({ queryKey: ['medias'] }),
  });
}

export function useDeleteMedia() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: sellerApi.deleteMedia,
    onSuccess: () => void queryClient.invalidateQueries({ queryKey: ['medias'] }),
  });
}

export function useCreateItem() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: sellerApi.createItem,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['items'] });
      void queryClient.invalidateQueries({ queryKey: ['home'] });
    },
  });
}

export function useUpdateItem(uuid: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (payload: Parameters<typeof sellerApi.updateItem>[1]) =>
      sellerApi.updateItem(uuid, payload),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['item', uuid] });
      void queryClient.invalidateQueries({ queryKey: ['items'] });
    },
  });
}

export function useDeleteItem() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: sellerApi.deleteItem,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['items'] });
      void queryClient.invalidateQueries({ queryKey: ['home'] });
    },
  });
}

export function useAdminUsers() {
  return useQuery({ queryKey: ['admin-users'], queryFn: sellerApi.users });
}

export function useCreateUser() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: sellerApi.createUser,
    onSuccess: () => void queryClient.invalidateQueries({ queryKey: ['admin-users'] }),
  });
}

export function useUpdateUser() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ uuid, payload }: { uuid: string; payload: Parameters<typeof sellerApi.updateUser>[1] }) =>
      sellerApi.updateUser(uuid, payload),
    onSuccess: () => void queryClient.invalidateQueries({ queryKey: ['admin-users'] }),
  });
}

export function useDeleteUser() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: sellerApi.deleteUser,
    onSuccess: () => void queryClient.invalidateQueries({ queryKey: ['admin-users'] }),
  });
}

export function useAdminCustomers() {
  return useQuery({ queryKey: ['admin-customers'], queryFn: sellerApi.customers });
}

export function useDeleteCustomer() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: sellerApi.deleteCustomer,
    onSuccess: () => void queryClient.invalidateQueries({ queryKey: ['admin-customers'] }),
  });
}
