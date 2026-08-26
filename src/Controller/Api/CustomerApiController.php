<?php

namespace App\Controller\Api;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Repository\ProfileRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/api/customers')]
class CustomerApiController extends ApiController
{
    public function __construct(
        private readonly ProfileRepository $profileRepository,
    ) {
    }

    #[Route(path: '', name: 'api_customers_index', methods: ['GET'])]
    public function index(CustomerRepository $customerRepository): JsonResponse
    {
        return $this->jsonResponse([
            'customers' => array_map(
                fn (Customer $customer): array => $this->present($customer),
                $customerRepository->findAll()
            ),
        ]);
    }

    #[Route(path: '/{uuid}', name: 'api_customers_delete', methods: ['DELETE'])]
    public function delete(string $uuid, CustomerRepository $customerRepository): JsonResponse
    {
        $customer = $customerRepository->findByUuid($uuid);
        if ($customer === null) {
            return $this->jsonError('Acheteur·se introuvable.', Response::HTTP_NOT_FOUND);
        }

        $customerRepository->remove($customer);

        return $this->jsonResponse(['deleted' => true], Response::HTTP_OK);
    }

    /** @return array<string, mixed> */
    private function present(Customer $customer): array
    {
        $profile = $this->profileRepository->findByUser($customer->getUser());

        return [
            'uuid' => (string) $customer->getUuid(),
            'email' => $customer->getUser()->getEmail(),
            'displayName' => $profile?->getDisplayName(),
            'orderCount' => $customer->getOrders()->count(),
        ];
    }
}
