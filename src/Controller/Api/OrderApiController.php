<?php

namespace App\Controller\Api;

use App\Dto\Api\OrderCreatePayload;
use App\Dto\Api\OrderStatusPayload;
use App\Entity\Order;
use App\Entity\User;
use App\Enum\OrderStatus;
use App\Repository\AddressRepository;
use App\Repository\CustomerRepository;
use App\Repository\ItemRepository;
use App\Repository\OrderRepository;
use App\Repository\ReviewRepository;
use App\Repository\SellerRepository;
use App\Service\OrderPresenter;
use App\Service\StripeService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_USER')]
#[Route(path: '/api/orders')]
class OrderApiController extends ApiController
{
    public function __construct(
        private readonly OrderPresenter $orderPresenter,
        private readonly ReviewRepository $reviewRepository,
        private readonly StripeService $stripeService,
    ) {
    }

    #[Route(path: '', name: 'api_orders_index', methods: ['GET'])]
    public function index(
        OrderRepository $orderRepository,
        CustomerRepository $customerRepository,
        SellerRepository $sellerRepository,
        #[CurrentUser] User $user
    ): JsonResponse {
        $qb = $orderRepository->createQueryBuilder('o')->orderBy('o.createdAt', 'DESC');

        if (!$this->isGranted('ROLE_ADMIN')) {
            $qb->where('1 = 0');
            $customer = $customerRepository->findByUser($user);
            if ($customer !== null) {
                $qb->orWhere('o.customer = :customer')
                    ->setParameter('customer', $customer);
            }
            $seller = $sellerRepository->findByUser($user);
            if ($seller !== null) {
                $qb->orWhere('o.seller = :seller')
                    ->setParameter('seller', $seller);
            }
        }

        $orders = $qb->getQuery()->getResult();

        return $this->jsonResponse([
            'orders' => array_map(
                fn (Order $order): array => $this->orderDetail($order, $user),
                $orders
            ),
        ]);
    }

    #[Route(path: '/{uuid}', name: 'api_orders_show', methods: ['GET'])]
    public function show(
        string $uuid,
        OrderRepository $orderRepository,
        #[CurrentUser] User $user
    ): JsonResponse {
        $order = $orderRepository->findByUuid($uuid);
        if ($order === null || !$this->canAccess($order, $user)) {
            return $this->jsonError('Commande introuvable.', Response::HTTP_NOT_FOUND);
        }

        return $this->jsonResponse(['order' => $this->orderDetail($order, $user)]);
    }

    #[Route(path: '', name: 'api_orders_create', methods: ['POST'])]
    public function create(
        Request $request,
        ValidatorInterface $validator,
        ItemRepository $itemRepository,
        CustomerRepository $customerRepository,
        AddressRepository $addressRepository,
        OrderRepository $orderRepository,
        #[CurrentUser] User $user
    ): JsonResponse {
        $data = json_decode((string) $request->getContent(), true);
        $payload = new OrderCreatePayload();
        $payload->itemUuid = (string) ($data['itemUuid'] ?? '');
        $payload->addressUuid = isset($data['addressUuid']) ? (string) $data['addressUuid'] : null;
        $payload->shippingFee = isset($data['shippingFee']) ? (string) $data['shippingFee'] : null;

        $violations = $validator->validate($payload);
        if (count($violations) > 0) {
            return $this->jsonError(
                'Données invalides.',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $this->violationList($violations)
            );
        }

        $item = $itemRepository->findByUuid($payload->itemUuid);
        if ($item === null) {
            return $this->jsonError('Article introuvable.', Response::HTTP_NOT_FOUND);
        }
        if ($item->isSold()) {
            return $this->jsonError('Cet article n\'est plus disponible.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $customer = $customerRepository->findByUser($user);
        if ($customer === null) {
            return $this->jsonError(
                'Vous devez avoir un compte client pour passer commande.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ($this->stripeService->isConfigured() && $item->getSeller()->getStripeAccountId() === null) {
            return $this->jsonError(
                'Le vendeur·se n\'a pas encore configuré ses paiements en ligne.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $shippingAddress = null;
        if ($payload->addressUuid !== null && $payload->addressUuid !== '') {
            $shippingAddress = $addressRepository->findByUuid($payload->addressUuid);
            if ($shippingAddress === null || $shippingAddress->getUser() !== $user) {
                return $this->jsonError('Adresse de livraison invalide.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $shippingFee = $payload->shippingFee ?? '0.00';
        $totalPrice = number_format((float) $item->getPrice() + (float) $shippingFee, 2, '.', '');

        $order = new Order();
        $order->setItem($item)
            ->setCustomer($customer)
            ->setSeller($item->getSeller())
            ->setShippingAddress($shippingAddress)
            ->setShippingFee($shippingFee)
            ->setTotalPrice($totalPrice)
            ->setReference('REF-' . strtoupper(uniqid()));

        $item->setAvailableCount($item->getAvailableCount() - 1);
        $orderRepository->createOrUpdate($order);

        try {
            $checkoutUrl = $this->stripeService->createCheckoutSession($order);
        } catch (\Stripe\Exception\ApiErrorException) {
            return $this->jsonError(
                'Le paiement est momentanément indisponible, réessayez dans quelques minutes.',
                Response::HTTP_SERVICE_UNAVAILABLE
            );
        }
        if ($checkoutUrl !== null) {
            $orderRepository->createOrUpdate($order);
        }

        return $this->jsonResponse(
            [
                'order' => $this->orderDetail($order, $user),
                'checkoutUrl' => $checkoutUrl,
            ],
            Response::HTTP_CREATED
        );
    }

    #[Route(path: '/{uuid}/checkout', name: 'api_orders_checkout', methods: ['POST'])]
    public function checkout(
        string $uuid,
        OrderRepository $orderRepository,
        #[CurrentUser] User $user
    ): JsonResponse {
        $order = $orderRepository->findByUuid($uuid);
        if ($order === null) {
            return $this->jsonError('Commande introuvable.', Response::HTTP_NOT_FOUND);
        }
        if ($order->getCustomer()->getUser() !== $user) {
            return $this->jsonError('Action non autorisée.', Response::HTTP_FORBIDDEN);
        }
        if ($order->getStatus() !== OrderStatus::PENDING_PAYMENT) {
            return $this->jsonError(
                'Cette commande n\'est plus en attente de paiement.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if (!$this->stripeService->isConfigured()) {
            return $this->jsonError('Le paiement en ligne n\'est pas disponible.', Response::HTTP_SERVICE_UNAVAILABLE);
        }
        if ($order->getSeller()->getStripeAccountId() === null) {
            return $this->jsonError(
                'Le vendeur·se n\'a pas encore configuré ses paiements en ligne.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            $checkoutUrl = $this->stripeService->createCheckoutSession($order);
        } catch (\Stripe\Exception\ApiErrorException) {
            return $this->jsonError(
                'Le paiement est momentanément indisponible, réessayez dans quelques minutes.',
                Response::HTTP_SERVICE_UNAVAILABLE
            );
        }
        if ($checkoutUrl !== null) {
            $orderRepository->createOrUpdate($order);
        }

        return $this->jsonResponse(['checkoutUrl' => $checkoutUrl]);
    }

    #[Route(path: '/{uuid}/status', name: 'api_orders_status', methods: ['PATCH'])]
    public function updateStatus(
        Request $request,
        string $uuid,
        ValidatorInterface $validator,
        OrderRepository $orderRepository,
        #[CurrentUser] User $user
    ): JsonResponse {
        $order = $orderRepository->findByUuid($uuid);
        if ($order === null) {
            return $this->jsonError('Commande introuvable.', Response::HTTP_NOT_FOUND);
        }
        if (!$this->isGranted('ROLE_ADMIN') && $order->getSeller()->getUser() !== $user) {
            return $this->jsonError('Action non autorisée.', Response::HTTP_FORBIDDEN);
        }

        $data = json_decode((string) $request->getContent(), true);
        $payload = new OrderStatusPayload();
        $payload->status = (string) ($data['status'] ?? '');
        $payload->trackingNumber = isset($data['trackingNumber']) ? (string) $data['trackingNumber'] : null;
        $payload->shippingProvider = isset($data['shippingProvider']) ? (string) $data['shippingProvider'] : null;

        $violations = $validator->validate($payload);
        if (count($violations) > 0) {
            return $this->jsonError(
                'Données invalides.',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $this->violationList($violations)
            );
        }

        $target = OrderStatus::tryFrom($payload->status);
        if ($target === null || !in_array($target, OrderStatus::allowedTransitions($order->getStatus()), true)) {
            return $this->jsonError(
                sprintf(
                    'Transition %s → %s interdite.',
                    $order->getStatus()->label(),
                    $target?->label() ?? $payload->status
                ),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $order->setStatus($target);
        if ($payload->trackingNumber !== null) {
            $order->setTrackingNumber($payload->trackingNumber);
        }
        if ($payload->shippingProvider !== null) {
            $order->setShippingProvider($payload->shippingProvider);
        }
        $orderRepository->createOrUpdate($order);

        return $this->jsonResponse(['order' => $this->orderDetail($order, $user)]);
    }

    private function canAccess(Order $order, User $user): bool
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $order->getCustomer()->getUser() === $user
            || $order->getSeller()->getUser() === $user;
    }

    /** @return array<string, mixed> */
    private function orderDetail(Order $order, User $user): array
    {
        $extra = [
            'canUpdateStatus' => $this->isGranted('ROLE_ADMIN') || $order->getSeller()->getUser() === $user,
            'canPay' => $order->getCustomer()->getUser() === $user
                && $order->getStatus() === OrderStatus::PENDING_PAYMENT,
            'canReview' => false,
            'hasReview' => false,
        ];

        if ($order->getCustomer()->getUser() === $user) {
            $hasReview = $this->reviewRepository->findByOrder($order) !== null;
            $extra['canReview'] = $order->getStatus() === OrderStatus::DELIVERED && !$hasReview;
            $extra['hasReview'] = $hasReview;
        }

        return $this->orderPresenter->detail($order, $extra);
    }
}
