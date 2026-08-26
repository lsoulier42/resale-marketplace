<?php

namespace App\Controller\Api;

use App\Dto\Api\RegisterPayload;
use App\Entity\Seller;
use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\SellerRepository;
use App\Repository\UserRepository;
use App\Service\RegistrationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/api')]
class AuthApiController extends ApiController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly CustomerRepository $customerRepository,
        private readonly SellerRepository $sellerRepository,
        private readonly RegistrationService $registrationService,
    ) {
    }

    #[IsGranted('PUBLIC_ACCESS')]
    #[Route(path: '/csrf-token', name: 'api_csrf_token', methods: ['GET'])]
    public function csrfToken(CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        return $this->jsonResponse([
            'token' => $csrfTokenManager->getToken('api')->getValue(),
        ]);
    }

    #[IsGranted('PUBLIC_ACCESS')]
    #[Route(path: '/login', name: 'api_login', methods: ['POST'])]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        // json_login authentifie la requête avant l'exécution du contrôleur
        // (onAuthenticationSuccess renvoie null → la requête continue).
        return $this->userResponse($user);
    }

    #[IsGranted('PUBLIC_ACCESS')]
    #[Route(path: '/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, ValidatorInterface $validator): JsonResponse
    {
        return $this->registerAccount($request, $validator, false);
    }

    #[IsGranted('PUBLIC_ACCESS')]
    #[Route(path: '/register/seller', name: 'api_register_seller', methods: ['POST'])]
    public function registerSeller(Request $request, ValidatorInterface $validator): JsonResponse
    {
        return $this->registerAccount($request, $validator, true);
    }

    /**
     * Confirme l'adresse email d'un compte via le jeton reçu par mail.
     */
    #[IsGranted('PUBLIC_ACCESS')]
    #[Route(path: '/register/confirm/{token}', name: 'api_register_confirm', methods: ['GET'])]
    public function confirmEmail(string $token): JsonResponse
    {
        $user = $this->userRepository->findOneBy(['confirmationToken' => $token]);
        $expiresAt = $user?->getConfirmationTokenExpiresAt();

        if ($user === null || $expiresAt === null || $expiresAt < new \DateTimeImmutable()) {
            return $this->jsonError('Lien de confirmation invalide ou expiré.', Response::HTTP_BAD_REQUEST);
        }

        $user->setIsVerified(true)->clearConfirmationToken();
        $this->userRepository->createOrUpdate($user);

        return $this->jsonResponse(['confirmed' => true]);
    }

    /**
     * Renvoie l'email de confirmation (réponse générique : ne révèle pas
     * si le compte existe ou est déjà vérifié).
     */
    #[IsGranted('PUBLIC_ACCESS')]
    #[Route(path: '/register/resend-confirmation', name: 'api_register_resend', methods: ['POST'])]
    public function resendConfirmation(Request $request): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true);
        $email = (string) ($data['email'] ?? '');

        $user = $this->userRepository->findOneBy(['email' => $email]);
        if ($user !== null && !$user->isVerified()) {
            $this->registrationService->resendConfirmation($user);
        }

        return $this->jsonResponse(['sent' => true]);
    }

    /**
     * Transforme le compte connecté en vendeur·se (création de l'entité Seller).
     */
    #[IsGranted('ROLE_USER')]
    #[Route(path: '/me/seller', name: 'api_me_seller', methods: ['POST'])]
    public function becomeSeller(#[CurrentUser] User $user): JsonResponse
    {
        if ($this->sellerRepository->findByUser($user) !== null) {
            return $this->jsonError('Vous êtes déjà vendeur·se.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $seller = new Seller();
        $seller->setUser($user);
        $this->sellerRepository->createOrUpdate($seller);

        return $this->jsonResponse(['isSeller' => true], Response::HTTP_CREATED);
    }

    #[IsGranted('PUBLIC_ACCESS')]
    #[Route(path: '/me', name: 'api_me', methods: ['GET'])]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        return $this->userResponse($user);
    }

    #[IsGranted('ROLE_USER')]
    #[Route(path: '/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(
        Request $request,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session
    ): JsonResponse {
        $tokenStorage->setToken(null);
        $session->invalidate();

        return $this->jsonResponse(['loggedOut' => true], Response::HTTP_OK);
    }

    private function registerAccount(Request $request, ValidatorInterface $validator, bool $asSeller): JsonResponse
    {
        $payload = $this->decodeRegisterPayload($request, $validator);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        if ($this->userRepository->findOneBy(['email' => $payload->email]) !== null) {
            return $this->jsonError('Cet email est déjà utilisé.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $this->registrationService->register($payload, $asSeller);

        return $this->jsonResponse(
            ['user' => ['uuid' => (string) $user->getUuid(), 'email' => $user->getEmail()]],
            Response::HTTP_CREATED
        );
    }

    /**
     * @return RegisterPayload|JsonResponse
     */
    private function decodeRegisterPayload(
        Request $request,
        ValidatorInterface $validator
    ): RegisterPayload|JsonResponse {
        $data = json_decode((string) $request->getContent(), true);
        $payload = new RegisterPayload();
        $payload->email = (string) ($data['email'] ?? '');
        $payload->password = (string) ($data['password'] ?? '');
        $payload->displayName = (string) ($data['displayName'] ?? '');

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

    private function userResponse(?User $user): JsonResponse
    {
        if ($user === null) {
            return $this->jsonResponse(['user' => null, 'isSeller' => false, 'isCustomer' => false]);
        }

        return $this->jsonResponse([
            'user' => [
                'uuid' => $user->getUuid(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'isVerified' => $user->isVerified(),
            ],
            'isSeller' => $this->sellerRepository->findByUser($user) !== null,
            'isCustomer' => $this->customerRepository->findByUser($user) !== null,
        ]);
    }
}
