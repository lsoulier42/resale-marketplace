import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { customerApi } from '../api/endpoints';

export function useOrders() {
  return useQuery({ queryKey: ['orders'], queryFn: customerApi.orders });
}

export function useOrder(uuid: string) {
  return useQuery({
    queryKey: ['order', uuid],
    queryFn: () => customerApi.order(uuid),
    enabled: uuid.length > 0,
  });
}

export function useAddresses() {
  return useQuery({ queryKey: ['addresses'], queryFn: customerApi.addresses });
}

export function useProfile() {
  return useQuery({ queryKey: ['profile'], queryFn: customerApi.profile });
}

export function useCreateOrder() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: customerApi.createOrder,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['orders'] });
      void queryClient.invalidateQueries({ queryKey: ['items'] });
    },
  });
}

export function useCheckout() {
  return useMutation({
    mutationFn: customerApi.checkout,
  });
}

export function useUpdateOrderStatus(uuid: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (payload: { status: string; trackingNumber?: string; shippingProvider?: string }) =>
      customerApi.updateOrderStatus(uuid, payload),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['order', uuid] });
      void queryClient.invalidateQueries({ queryKey: ['orders'] });
    },
  });
}

export function useCreateAddress() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: customerApi.createAddress,
    onSuccess: () => void queryClient.invalidateQueries({ queryKey: ['addresses'] }),
  });
}

export function useUpdateAddress() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ uuid, payload }: { uuid: string; payload: Parameters<typeof customerApi.updateAddress>[1] }) =>
      customerApi.updateAddress(uuid, payload),
    onSuccess: () => void queryClient.invalidateQueries({ queryKey: ['addresses'] }),
  });
}

export function useDeleteAddress() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: customerApi.deleteAddress,
    onSuccess: () => void queryClient.invalidateQueries({ queryKey: ['addresses'] }),
  });
}

export function useUpdateProfile() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: customerApi.updateProfile,
    onSuccess: () => void queryClient.invalidateQueries({ queryKey: ['profile'] }),
  });
}

export function useCreateReview(orderUuid: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (payload: { star: number; comment?: string | null }) =>
      customerApi.createReview({ orderUuid, ...payload }),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['order', orderUuid] });
      void queryClient.invalidateQueries({ queryKey: ['reviews'] });
    },
  });
}
