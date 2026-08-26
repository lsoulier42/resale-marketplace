<?php

namespace App\Controller\Api;

use App\Enum\OrderStatus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('PUBLIC_ACCESS')]
#[Route(path: '/api/order-statuses')]
class OrderStatusApiController extends ApiController
{
    #[Route(path: '', name: 'api_order_statuses', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->jsonResponse([
            'statuses' => array_map(
                fn (OrderStatus $status): array => [
                    'value' => $status->value,
                    'label' => $status->label(),
                    'transitions' => array_map(
                        fn (OrderStatus $next): string => $next->value,
                        OrderStatus::allowedTransitions($status)
                    ),
                ],
                OrderStatus::cases()
            ),
        ]);
    }
}
