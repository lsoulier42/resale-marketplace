<?php

namespace App\Controller\Api;

use App\Dto\Api\UserPayload;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/api/users')]
class UserApiController extends ApiController
{
    #[Route(path: '', name: 'api_users_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): JsonResponse
    {
        return $this->jsonResponse([
            'users' => array_map(
                fn (User $user): array => $this->present($user),
                $userRepository->findAll()
            ),
        ]);
    }

    #[Route(path: '', name: 'api_users_create', methods: ['POST'])]
    public function create(
        Request $request,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ): JsonResponse {
        $payload = $this->decodePayload($request, $validator, true);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        if ($userRepository->findOneBy(['email' => $payload->email]) !== null) {
            return $this->jsonError('Cet email est déjà utilisé.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = new User();
        $user->setEmail($payload->email)
            ->setRoles($payload->roles)
            ->setIsVerified($payload->isVerified)
            ->setPassword($passwordHasher->hashPassword($user, (string) $payload->password));
        $userRepository->createOrUpdate($user);

        return $this->jsonResponse(['user' => $this->present($user)], Response::HTTP_CREATED);
    }

    #[Route(path: '/{uuid}', name: 'api_users_update', methods: ['PUT'])]
    public function update(
        Request $request,
        string $uuid,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        #[CurrentUser] User $currentUser
    ): JsonResponse {
        $user = $userRepository->findByUuid($uuid);
        if ($user === null) {
            return $this->jsonError('Utilisateur introuvable.', Response::HTTP_NOT_FOUND);
        }

        $payload = $this->decodePayload($request, $validator, false);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        // Un admin ne peut pas se retirer le rôle admin lui-même.
        if ($user->getUuid() === $currentUser->getUuid() && !in_array('ROLE_ADMIN', $payload->roles, true)) {
            return $this->jsonError(
                'Vous ne pouvez pas retirer votre propre rôle admin.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $user->setEmail($payload->email)
            ->setRoles($payload->roles)
            ->setIsVerified($payload->isVerified);
        if ($payload->password !== null) {
            $user->setPassword($passwordHasher->hashPassword($user, $payload->password));
        }
        $userRepository->createOrUpdate($user);

        return $this->jsonResponse(['user' => $this->present($user)]);
    }

    #[Route(path: '/{uuid}', name: 'api_users_delete', methods: ['DELETE'])]
    public function delete(string $uuid, UserRepository $userRepository, #[CurrentUser] User $currentUser): JsonResponse
    {
        $user = $userRepository->findByUuid($uuid);
        if ($user === null) {
            return $this->jsonError('Utilisateur introuvable.', Response::HTTP_NOT_FOUND);
        }
        if ($user->getUuid() === $currentUser->getUuid()) {
            return $this->jsonError(
                'Vous ne pouvez pas supprimer votre propre compte.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $userRepository->remove($user);

        return $this->jsonResponse(['deleted' => true], Response::HTTP_OK);
    }

    /**
     * @return UserPayload|JsonResponse
     */
    private function decodePayload(
        Request $request,
        ValidatorInterface $validator,
        bool $isNew
    ): UserPayload|JsonResponse {
        $data = json_decode((string) $request->getContent(), true);
        $payload = new UserPayload();
        $payload->email = (string) ($data['email'] ?? '');
        $payload->isVerified = (bool) ($data['isVerified'] ?? false);
        $payload->roles = array_values(array_map('strval', (array) ($data['roles'] ?? ['ROLE_USER'])));
        $payload->password = isset($data['password']) && $data['password'] !== '' ? (string) $data['password'] : null;

        if ($isNew && $payload->password === null) {
            $violations = $validator->validate($payload);
            if (count($violations) > 0) {
                return $this->jsonError(
                    'Données invalides.',
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    $this->violationList($violations)
                );
            }

            return $this->jsonError('Le mot de passe est obligatoire.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

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

    /** @return array<string, mixed> */
    private function present(User $user): array
    {
        return [
            'uuid' => (string) $user->getUuid(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'isVerified' => $user->isVerified(),
            'createdAt' => $user->getCreatedAt()?->format('c'),
        ];
    }
}
