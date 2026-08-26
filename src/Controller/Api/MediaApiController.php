<?php

namespace App\Controller\Api;

use App\Entity\Media;
use App\Entity\User;
use App\Repository\MediaRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[IsGranted('ROLE_USER')]
#[Route(path: '/api/medias')]
class MediaApiController extends ApiController
{
    #[Route(path: '', name: 'api_medias_index', methods: ['GET'])]
    public function index(MediaRepository $mediaRepository): JsonResponse
    {
        return $this->jsonResponse([
            'medias' => array_map(
                fn (Media $media): array => $this->present($media),
                $mediaRepository->findAll()
            ),
        ]);
    }

    #[Route(path: '', name: 'api_medias_upload', methods: ['POST'])]
    public function upload(
        Request $request,
        MediaRepository $mediaRepository,
        SluggerInterface $slugger,
        #[Autowire('%upload_directory%')] string $uploadDir
    ): JsonResponse {
        /** @var UploadedFile|null $uploadedFile */
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile instanceof UploadedFile || !$uploadedFile->isValid()) {
            return $this->jsonError('Fichier manquant ou invalide.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $safeFilename = $slugger->slug(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME));
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

        // Taille/type capturés AVANT move() : après déplacement, le fichier
        // temporaire n'existe plus et getSize() échoue.
        $fileSize = $uploadedFile->getSize() ?: 0;
        $fileType = $uploadedFile->getMimeType() ?: '';

        $uploadedFile->move($uploadDir, $newFilename);

        $media = new Media();
        $media->setFile($newFilename)
            ->setFileSize($fileSize)
            ->setFileType($fileType);
        $mediaRepository->createOrUpdate($media);

        return $this->jsonResponse(['media' => $this->present($media)], Response::HTTP_CREATED);
    }

    #[Route(path: '/{uuid}', name: 'api_medias_delete', methods: ['DELETE'])]
    public function delete(string $uuid, MediaRepository $mediaRepository, #[CurrentUser] User $user): JsonResponse
    {
        $media = $mediaRepository->findByUuid($uuid);
        if ($media === null) {
            return $this->jsonError('Média introuvable.', Response::HTTP_NOT_FOUND);
        }

        $mediaRepository->remove($media);

        return $this->jsonResponse(['deleted' => true], Response::HTTP_OK);
    }

    /** @return array<string, mixed> */
    private function present(Media $media): array
    {
        return [
            'uuid' => (string) $media->getUuid(),
            'fileUrl' => '/uploads/' . $media->getFile(),
            'fileSize' => $media->getFileSize(),
            'fileType' => $media->getFileType(),
        ];
    }
}
