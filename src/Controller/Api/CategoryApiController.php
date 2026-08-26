<?php

namespace App\Controller\Api;

use App\Entity\Category;
use App\Entity\Item;
use App\Repository\CategoryRepository;
use App\Repository\ItemRepository;
use App\Service\CatalogPresenter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('PUBLIC_ACCESS')]
#[Route(path: '/api/categories')]
class CategoryApiController extends ApiController
{
    #[Route(path: '', name: 'api_categories_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository, CatalogPresenter $presenter): JsonResponse
    {
        return $this->jsonResponse([
            'categories' => array_map(
                fn (Category $category): array => $presenter->categoryCard($category),
                $categoryRepository->findAll()
            ),
        ]);
    }

    #[Route(path: '/{uuid}', name: 'api_categories_show', methods: ['GET'])]
    public function show(
        string $uuid,
        CategoryRepository $categoryRepository,
        ItemRepository $itemRepository,
        CatalogPresenter $presenter
    ): JsonResponse {
        $category = $categoryRepository->findByUuid($uuid);
        if ($category === null) {
            return $this->jsonError('Catégorie introuvable.', Response::HTTP_NOT_FOUND);
        }

        $qb = $itemRepository->createQueryBuilder('i')
            ->leftJoin('i.order', 'o')
            ->andWhere('i.availableCount > 0')
            ->andWhere('o.id IS NULL')
            ->andWhere('i.category = :category')
            ->setParameter('category', $category)
            ->orderBy('i.createdAt', 'DESC');

        return $this->jsonResponse([
            'category' => $presenter->categoryCard($category),
            'items' => array_map(
                fn (Item $item): array => $presenter->itemCard($item),
                $qb->getQuery()->getResult()
            ),
        ]);
    }
}
