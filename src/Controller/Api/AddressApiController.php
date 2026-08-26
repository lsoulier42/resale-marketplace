<?php

namespace App\Controller\Api;

use App\Dto\Api\AddressPayload;
use App\Entity\Address;
use App\Entity\User;
use App\Repository\AddressRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_USER')]
#[Route(path: '/api/addresses')]
class AddressApiController extends ApiController
{
    #[Route(path: '', name: 'api_addresses_index', methods: ['GET'])]
    public function index(AddressRepository $addressRepository, #[CurrentUser] User $user): JsonResponse
    {
        return $this->jsonResponse([
            'addresses' => array_map(
                fn (Address $address): array => $this->present($address),
                $addressRepository->findByUser($user)->toArray()
            ),
        ]);
    }

    #[Route(path: '', name: 'api_addresses_create', methods: ['POST'])]
    public function create(
        Request $request,
        ValidatorInterface $validator,
        AddressRepository $addressRepository,
        #[CurrentUser] User $user
    ): JsonResponse {
        $payload = $this->decodePayload($request, $validator);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $address = new Address();
        $address->setUser($user);
        $this->applyPayload($address, $payload);
        $addressRepository->createOrUpdate($address);

        return $this->jsonResponse(['address' => $this->present($address)], Response::HTTP_CREATED);
    }

    #[Route(path: '/{uuid}', name: 'api_addresses_update', methods: ['PUT'])]
    public function update(
        Request $request,
        string $uuid,
        ValidatorInterface $validator,
        AddressRepository $addressRepository,
        #[CurrentUser] User $user
    ): JsonResponse {
        $address = $addressRepository->findByUuid($uuid);
        if ($address === null || $address->getUser() !== $user) {
            return $this->jsonError('Adresse introuvable.', Response::HTTP_NOT_FOUND);
        }

        $payload = $this->decodePayload($request, $validator);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $this->applyPayload($address, $payload);
        $addressRepository->createOrUpdate($address);

        return $this->jsonResponse(['address' => $this->present($address)]);
    }

    #[Route(path: '/{uuid}', name: 'api_addresses_delete', methods: ['DELETE'])]
    public function delete(string $uuid, AddressRepository $addressRepository, #[CurrentUser] User $user): JsonResponse
    {
        $address = $addressRepository->findByUuid($uuid);
        if ($address === null || $address->getUser() !== $user) {
            return $this->jsonError('Adresse introuvable.', Response::HTTP_NOT_FOUND);
        }

        $addressRepository->remove($address);

        return $this->jsonResponse(['deleted' => true], Response::HTTP_OK);
    }

    /** @return AddressPayload|JsonResponse */
    private function decodePayload(Request $request, ValidatorInterface $validator): AddressPayload|JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true);
        $payload = new AddressPayload();
        $payload->name = (string) ($data['name'] ?? '');
        $payload->addressLine = (string) ($data['addressLine'] ?? '');
        $payload->city = (string) ($data['city'] ?? '');
        $payload->zipCode = (string) ($data['zipCode'] ?? '');
        $payload->country = (string) ($data['country'] ?? '');

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

    private function applyPayload(Address $address, AddressPayload $payload): void
    {
        $address->setName($payload->name)
            ->setAddressLine($payload->addressLine)
            ->setCity($payload->city)
            ->setZipCode($payload->zipCode)
            ->setCountry($payload->country);
    }

    /** @return array<string, string> */
    private function present(Address $address): array
    {
        return [
            'uuid' => (string) $address->getUuid(),
            'name' => $address->getName(),
            'addressLine' => $address->getAddressLine(),
            'city' => $address->getCity(),
            'zipCode' => $address->getZipCode(),
            'country' => $address->getCountry(),
        ];
    }
}
