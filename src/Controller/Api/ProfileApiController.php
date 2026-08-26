<?php

namespace App\Controller\Api;

use App\Dto\Api\ProfilePayload;
use App\Entity\Profile;
use App\Entity\User;
use App\Repository\ProfileRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_USER')]
#[Route(path: '/api/profile')]
class ProfileApiController extends ApiController
{
    #[Route(path: '', name: 'api_profile_show', methods: ['GET'])]
    public function show(ProfileRepository $profileRepository, #[CurrentUser] User $user): JsonResponse
    {
        return $this->jsonResponse([
            'profile' => $this->present($profileRepository->findByUser($user)),
        ]);
    }

    #[Route(path: '', name: 'api_profile_update', methods: ['PUT'])]
    public function update(
        Request $request,
        ValidatorInterface $validator,
        ProfileRepository $profileRepository,
        #[CurrentUser] User $user
    ): JsonResponse {
        $data = json_decode((string) $request->getContent(), true);
        $payload = new ProfilePayload();
        $payload->displayName = (string) ($data['displayName'] ?? '');
        $payload->bio = isset($data['bio']) && $data['bio'] !== '' ? (string) $data['bio'] : null;

        $violations = $validator->validate($payload);
        if (count($violations) > 0) {
            return $this->jsonError(
                'Données invalides.',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $this->violationList($violations)
            );
        }

        $profile = $profileRepository->findByUser($user) ?? (new Profile())->setUser($user);
        $profile->setDisplayName($payload->displayName)
            ->setBio($payload->bio);
        $profileRepository->createOrUpdate($profile);

        return $this->jsonResponse(['profile' => $this->present($profile)]);
    }

    /** @return array<string, mixed>|null */
    private function present(?Profile $profile): ?array
    {
        if ($profile === null) {
            return null;
        }

        return [
            'uuid' => (string) $profile->getUuid(),
            'displayName' => $profile->getDisplayName(),
            'bio' => $profile->getBio(),
            'avatarUrl' => $profile->getAvatar() !== null
                ? '/uploads/' . $profile->getAvatar()->getFile()
                : null,
        ];
    }
}
