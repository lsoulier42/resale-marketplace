<?php

namespace App\Controller\Api;

use App\Entity\Item;
use App\Entity\Review;
use App\Entity\Seller;
use App\Repository\ItemRepository;
use App\Repository\SellerRepository;
use App\Service\CatalogPresenter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('PUBLIC_ACCESS')]
#[Route(path: '/api/sellers')]
class SellerApiController extends ApiController
{
    #[Route(path: '', name: 'api_sellers_index', methods: ['GET'])]
    public function index(SellerRepository $sellerRepository, CatalogPresenter $presenter): JsonResponse
    {
        return $this->jsonResponse([
            'sellers' => array_map(
                fn (Seller $seller): array => $presenter->sellerCard($seller),
                $sellerRepository->findAll()
            ),
        ]);
    }

    #[Route(path: '/{uuid}', name: 'api_sellers_show', methods: ['GET'])]
    public function show(
        string $uuid,
        SellerRepository $sellerRepository,
        ItemRepository $itemRepository,
        CatalogPresenter $presenter
    ): JsonResponse {
        $seller = $sellerRepository->findByUuid($uuid);
        if ($seller === null) {
            return $this->jsonError('Vendeur·se introuvable.', Response::HTTP_NOT_FOUND);
        }

        $qb = $itemRepository->createQueryBuilder('i')
            ->leftJoin('i.order', 'o')
            ->andWhere('i.availableCount > 0')
            ->andWhere('o.id IS NULL')
            ->andWhere('i.seller = :seller')
            ->setParameter('seller', $seller)
            ->orderBy('i.createdAt', 'DESC');

        return $this->jsonResponse([
            'seller' => $presenter->sellerCard($seller),
            'items' => array_map(
                fn (Item $item): array => $presenter->itemCard($item),
                $qb->getQuery()->getResult()
            ),
            'reviews' => array_map(
                fn (Review $review): array => $presenter->reviewCard($review),
                $seller->getReviews()->toArray()
            ),
        ]);
    }
}
