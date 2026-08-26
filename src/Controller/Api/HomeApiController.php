<?php

namespace App\Controller\Api;

use App\Entity\Category;
use App\Entity\Item;
use App\Repository\CategoryRepository;
use App\Repository\ItemRepository;
use App\Service\CatalogPresenter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('PUBLIC_ACCESS')]
#[Route(path: '/api/home')]
class HomeApiController extends ApiController
{
    #[Route(path: '', name: 'api_home', methods: ['GET'])]
    public function index(
        ItemRepository $itemRepository,
        CategoryRepository $categoryRepository,
        CatalogPresenter $presenter
    ): JsonResponse {
        $qb = $itemRepository->createQueryBuilder('i')
            ->leftJoin('i.order', 'o')
            ->andWhere('i.availableCount > 0')
            ->andWhere('o.id IS NULL');
        $featured = ItemRepository::addRandomElements($qb, 'i', 6)->getQuery()->getResult();

        return $this->jsonResponse([
            'featuredItems' => array_map(
                fn (Item $item): array => $presenter->itemCard($item),
                $featured
            ),
            'categories' => array_map(
                fn (Category $category): array => $presenter->categoryCard($category),
                $categoryRepository->findAll()
            ),
        ]);
    }
}
