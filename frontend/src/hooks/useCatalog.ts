import { useQuery } from '@tanstack/react-query';
import { catalogApi } from '../api/endpoints';

export const ITEMS_PER_PAGE = 12;

export function useHome() {
  return useQuery({ queryKey: ['home'], queryFn: catalogApi.home });
}

export function useItems(page: number, category?: string) {
  return useQuery({
    queryKey: ['items', page, category ?? 'all'],
    queryFn: () => catalogApi.items({ page, limit: ITEMS_PER_PAGE, category }),
  });
}

export function useItem(uuid: string) {
  return useQuery({
    queryKey: ['item', uuid],
    queryFn: () => catalogApi.item(uuid),
    enabled: uuid.length > 0,
  });
}

export function useCategories() {
  return useQuery({ queryKey: ['categories'], queryFn: catalogApi.categories });
}

export function useCategory(uuid: string) {
  return useQuery({
    queryKey: ['category', uuid],
    queryFn: () => catalogApi.category(uuid),
    enabled: uuid.length > 0,
  });
}

export function useSellers() {
  return useQuery({ queryKey: ['sellers'], queryFn: catalogApi.sellers });
}

export function useSeller(uuid: string) {
  return useQuery({
    queryKey: ['seller', uuid],
    queryFn: () => catalogApi.seller(uuid),
    enabled: uuid.length > 0,
  });
}

export function useReviews() {
  return useQuery({ queryKey: ['reviews'], queryFn: catalogApi.reviews });
}
