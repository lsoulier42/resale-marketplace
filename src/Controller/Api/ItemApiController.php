<?php

namespace App\Controller\Api;

use App\Dto\Api\ItemPayload;
use App\Entity\Category;
use App\Entity\Item;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\ItemRepository;
use App\Repository\MediaRepository;
use App\Repository\SellerRepository;
use App\Service\CatalogPresenter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('PUBLIC_ACCESS')]
#[Route(path: '/api/items')]
class ItemApiController extends ApiController
{
    #[Route(path: '', name: 'api_items_index', methods: ['GET'])]
    public function index(
        Request $request,
        ItemRepository $itemRepository,
        CategoryRepository $categoryRepository,
        CatalogPresenter $presenter
    ): JsonResponse {
        $pagination = self::createPaginationDto($request);
        $qb = $itemRepository->createQueryBuilder('i')
            ->leftJoin('i.order', 'o')
            ->andWhere('i.availableCount > 0')
            ->andWhere('o.id IS NULL');

        // Recherche plein texte simple sur le titre.
        $q = trim((string) $request->query->get('q', ''));
        if ($q !== '') {
            $qb->andWhere('LOWER(i.title) LIKE :q')
                ->setParameter('q', '%' . mb_strtolower($q) . '%');
        }

        // Tri : récents (défaut), prix croissant ou décroissant.
        $sort = (string) $request->query->get('sort', 'recent');
        match ($sort) {
            'price_asc' => $qb->orderBy('i.price', 'ASC'),
            'price_desc' => $qb->orderBy('i.price', 'DESC'),
            default => $qb->orderBy('i.createdAt', 'DESC'),
        };

        $categoryUuid = (string) $request->query->get('category', '');
        if ($categoryUuid !== '') {
            $category = $categoryRepository->findByUuid($categoryUuid);
            if ($category === null) {
                return $this->jsonError('Catégorie introuvable.', Response::HTTP_NOT_FOUND);
            }
            $qb->andWhere('i.category = :category')
                ->setParameter('category', $category);
        }

        $pager = ItemRepository::findAllPaginated($pagination, $qb);

        return $this->jsonResponse([
            'items' => array_map(
                fn (Item $item): array => $presenter->itemCard($item),
                iterator_to_array($pager->getCurrentPageResults())
            ),
            'page' => $pager->getCurrentPage(),
            'limit' => $pager->getMaxPerPage(),
            'total' => $pager->count(),
            'pages' => $pager->getNbPages(),
        ]);
    }

    #[Route(path: '/{uuid}', name: 'api_items_show', methods: ['GET'])]
    public function show(string $uuid, ItemRepository $itemRepository, CatalogPresenter $presenter): JsonResponse
    {
        $item = $itemRepository->findByUuid($uuid);
        if ($item === null) {
            return $this->jsonError('Article introuvable.', Response::HTTP_NOT_FOUND);
        }

        return $this->jsonResponse(['item' => $presenter->itemDetail($item)]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route(path: '', name: 'api_items_create', methods: ['POST'])]
    public function create(
        Request $request,
        ValidatorInterface $validator,
        ItemRepository $itemRepository,
        CategoryRepository $categoryRepository,
        MediaRepository $mediaRepository,
        SellerRepository $sellerRepository,
        CatalogPresenter $presenter,
        #[CurrentUser] User $user
    ): JsonResponse {
        $seller = $sellerRepository->findByUser($user);
        if ($seller === null) {
            return $this->jsonError(
                'Vous devez être vendeur·se pour publier un article.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $payload = $this->decodePayload($request, $validator);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $category = $categoryRepository->findByUuid($payload->categoryUuid);
        if ($category === null) {
            return $this->jsonError('Catégorie introuvable.', Response::HTTP_NOT_FOUND);
        }

        $item = new Item();
        $item->setSeller($seller);
        $this->applyPayload($item, $payload, $category, $mediaRepository);
        $itemRepository->createOrUpdate($item);

        return $this->jsonResponse(['item' => $presenter->itemDetail($item)], Response::HTTP_CREATED);
    }

    #[IsGranted('ROLE_USER')]
    #[Route(path: '/{uuid}', name: 'api_items_update', methods: ['PUT'])]
    public function update(
        Request $request,
        string $uuid,
        ValidatorInterface $validator,
        ItemRepository $itemRepository,
        CategoryRepository $categoryRepository,
        MediaRepository $mediaRepository,
        CatalogPresenter $presenter,
        #[CurrentUser] User $user
    ): JsonResponse {
        $item = $itemRepository->findByUuid($uuid);
        if ($item === null) {
            return $this->jsonError('Article introuvable.', Response::HTTP_NOT_FOUND);
        }
        if (!$this->isGranted('ROLE_ADMIN') && $item->getSeller()->getUser() !== $user) {
            return $this->jsonError('Action non autorisée.', Response::HTTP_FORBIDDEN);
        }

        $payload = $this->decodePayload($request, $validator);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $category = $categoryRepository->findByUuid($payload->categoryUuid);
        if ($category === null) {
            return $this->jsonError('Catégorie introuvable.', Response::HTTP_NOT_FOUND);
        }

        $this->applyPayload($item, $payload, $category, $mediaRepository);
        $itemRepository->createOrUpdate($item);

        return $this->jsonResponse(['item' => $presenter->itemDetail($item)]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route(path: '/{uuid}', name: 'api_items_delete', methods: ['DELETE'])]
    public function delete(
        string $uuid,
        ItemRepository $itemRepository,
        #[CurrentUser] User $user
    ): JsonResponse {
        $item = $itemRepository->findByUuid($uuid);
        if ($item === null) {
            return $this->jsonError('Article introuvable.', Response::HTTP_NOT_FOUND);
        }
        if (!$this->isGranted('ROLE_ADMIN') && $item->getSeller()->getUser() !== $user) {
            return $this->jsonError('Action non autorisée.', Response::HTTP_FORBIDDEN);
        }
        if ($item->getOrder() !== null) {
            return $this->jsonError(
                'Un article commandé ne peut pas être supprimé.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $itemRepository->remove($item);

        return $this->jsonResponse(['deleted' => true], Response::HTTP_OK);
    }

    /**
     * @return ItemPayload|JsonResponse
     */
    private function decodePayload(Request $request, ValidatorInterface $validator): ItemPayload|JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true);
        $payload = new ItemPayload();
        $payload->title = (string) ($data['title'] ?? '');
        $payload->description = isset($data['description']) && $data['description'] !== ''
            ? (string) $data['description']
            : null;
        $payload->price = (string) ($data['price'] ?? '0.00');
        $payload->availableCount = (int) ($data['availableCount'] ?? 1);
        $payload->categoryUuid = (string) ($data['categoryUuid'] ?? '');
        $payload->mediaUuids = array_values(array_filter(
            array_map('strval', (array) ($data['mediaUuids'] ?? [])),
            static fn (string $uuid): bool => $uuid !== ''
        ));

        $violations = $validator->validate($payload);
        if (count($violations) > 0) {
            return $this->jsonError(
                'Données invalides.',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $this->violationList($violations)
            );
        }

        return $payload;
    }

    private function applyPayload(
        Item $item,
        ItemPayload $payload,
        Category $category,
        MediaRepository $mediaRepository
    ): void {
        $item->setTitle($payload->title)
            ->setDescription($payload->description)
            ->setPrice($payload->price)
            ->setAvailableCount($payload->availableCount)
            ->setCategory($category);

        foreach ($item->getMedias()->toArray() as $media) {
            $item->removeMedia($media);
        }
        foreach ($payload->mediaUuids as $mediaUuid) {
            $media = $mediaRepository->findByUuid($mediaUuid);
            if ($media !== null) {
                $item->addMedia($media);
            }
        }
    }
}
