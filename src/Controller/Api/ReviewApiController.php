<?php

namespace App\Controller\Api;

use App\Dto\Api\ReviewPayload;
use App\Entity\Review;
use App\Entity\User;
use App\Enum\OrderStatus;
use App\Repository\CustomerRepository;
use App\Repository\OrderRepository;
use App\Repository\ReviewRepository;
use App\Service\CatalogPresenter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/api/reviews')]
class ReviewApiController extends ApiController
{
    #[IsGranted('PUBLIC_ACCESS')]
    #[Route(path: '', name: 'api_reviews_index', methods: ['GET'])]
    public function index(ReviewRepository $reviewRepository, CatalogPresenter $presenter): JsonResponse
    {
        $qb = $reviewRepository->createQueryBuilder('r')
            ->orderBy('r.createdAt', 'DESC');

        return $this->jsonResponse([
            'reviews' => array_map(
                fn (Review $review): array => $presenter->reviewCard($review),
                $qb->getQuery()->getResult()
            ),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route(path: '', name: 'api_reviews_create', methods: ['POST'])]
    public function create(
        Request $request,
        ValidatorInterface $validator,
        OrderRepository $orderRepository,
        CustomerRepository $customerRepository,
        ReviewRepository $reviewRepository,
        CatalogPresenter $presenter,
        #[CurrentUser] User $user
    ): JsonResponse {
        $data = json_decode((string) $request->getContent(), true);
        $payload = new ReviewPayload();
        $payload->orderUuid = (string) ($data['orderUuid'] ?? '');
        $payload->star = (int) ($data['star'] ?? 5);
        $payload->comment = isset($data['comment']) && $data['comment'] !== '' ? (string) $data['comment'] : null;

        $violations = $validator->validate($payload);
        if (count($violations) > 0) {
            return $this->jsonError(
                'Données invalides.',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $this->violationList($violations)
            );
        }

        $order = $orderRepository->findByUuid($payload->orderUuid);
        if ($order === null || $order->getCustomer()->getUser() !== $user) {
            return $this->jsonError('Commande introuvable.', Response::HTTP_NOT_FOUND);
        }
        if ($order->getStatus() !== OrderStatus::DELIVERED) {
            return $this->jsonError(
                'Vous ne pouvez laisser un avis que sur une commande livrée.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if ($reviewRepository->findByOrder($order) !== null) {
            return $this->jsonError(
                'Vous avez déjà laissé un avis pour cette commande.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $customer = $customerRepository->findByUser($user);
        if ($customer === null) {
            return $this->jsonError('Compte client requis.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $review = new Review();
        $review->setOrder($order)
            ->setFromUser($customer)
            ->setToUser($order->getSeller())
            ->setStar($payload->star)
            ->setComment($payload->comment);
        $reviewRepository->createOrUpdate($review);

        return $this->jsonResponse(
            ['review' => $presenter->reviewCard($review)],
            Response::HTTP_CREATED
        );
    }
}
