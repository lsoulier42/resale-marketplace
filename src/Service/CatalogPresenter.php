<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Item;
use App\Entity\Media;
use App\Entity\Review;
use App\Entity\Seller;
use App\Entity\User;
use App\Repository\ItemRepository;
use App\Repository\ProfileRepository;

/**
 * Construit les payloads JSON de la vitrine publique (lecture).
 *
 * Mapping explicite : jamais de sérialisation réflexive des entités
 * (évite les fuites — ex. password, comptes Stripe — et les références
 * circulaires Item ↔ Order).
 */
class CatalogPresenter
{
    public function __construct(
        private readonly ProfileRepository $profileRepository,
        private readonly ItemRepository $itemRepository,
    ) {
    }

    /** @return array<string, mixed> */
    public function itemCard(Item $item): array
    {
        return [
            'uuid' => (string) $item->getUuid(),
            'title' => $item->getTitle(),
            'price' => $item->getPrice(),
            'availableCount' => $item->getAvailableCount(),
            'isSold' => $item->isSold(),
            'medias' => $this->mediaUrls($item),
            'category' => $this->categoryRef($item->getCategory()),
            'seller' => $this->sellerRef($item->getSeller()),
        ];
    }

    /** @return array<string, mixed> */
    public function itemDetail(Item $item): array
    {
        $card = $this->itemCard($item);
        $card['description'] = $item->getDescription();
        $card['createdAt'] = $item->getCreatedAt()?->format('c');

        return $card;
    }

    /** @return array<string, mixed> */
    public function categoryCard(Category $category): array
    {
        return [
            'uuid' => (string) $category->getUuid(),
            'title' => $category->getTitle(),
            'description' => $category->getDescription(),
            'itemCount' => $this->itemRepository->countAvailableByCategory($category),
        ];
    }

    /** @return array<string, mixed> */
    public function sellerCard(Seller $seller): array
    {
        $reviews = $seller->getReviews();
        $reviewCount = $reviews->count();
        $reviewAvg = $reviewCount > 0
            ? round(array_sum(array_map(
                static fn (Review $review): int => $review->getStar(),
                $reviews->toArray()
            )) / $reviewCount, 1)
            : null;

        $profile = $this->profileRepository->findByUser($seller->getUser());

        return [
            'uuid' => (string) $seller->getUuid(),
            'displayName' => $profile?->getDisplayName(),
            'bio' => $profile?->getBio(),
            'avatarUrl' => $this->mediaUrl($profile?->getAvatar()),
            'itemCount' => $this->itemRepository->countAvailableBySeller($seller),
            'reviewCount' => $reviewCount,
            'reviewAvg' => $reviewAvg,
        ];
    }

    /** @return array<string, mixed> */
    public function reviewCard(Review $review): array
    {
        return [
            'uuid' => (string) $review->getUuid(),
            'star' => $review->getStar(),
            'comment' => $review->getComment(),
            'createdAt' => $review->getCreatedAt()?->format('c'),
            'fromUser' => $this->userRef($review->getFromUser()->getUser()),
            'toUser' => $this->userRef($review->getToUser()->getUser()),
            'orderReference' => $review->getOrder()->getReference(),
        ];
    }

    /** @return array{uuid: string, title: string} */
    private function categoryRef(Category $category): array
    {
        return [
            'uuid' => (string) $category->getUuid(),
            'title' => $category->getTitle(),
        ];
    }

    /** @return array{uuid: string, displayName: ?string, avatarUrl: ?string} */
    private function sellerRef(Seller $seller): array
    {
        $profile = $this->profileRepository->findByUser($seller->getUser());

        return [
            'uuid' => (string) $seller->getUuid(),
            'displayName' => $profile?->getDisplayName(),
            'avatarUrl' => $this->mediaUrl($profile?->getAvatar()),
        ];
    }

    /** @return array{uuid: string, displayName: ?string} */
    private function userRef(User $user): array
    {
        $profile = $this->profileRepository->findByUser($user);

        return [
            'uuid' => (string) $user->getUuid(),
            'displayName' => $profile?->getDisplayName(),
        ];
    }

    /** @return list<string> */
    private function mediaUrls(Item $item): array
    {
        return array_values(array_map(
            fn (Media $media): string => '/uploads/' . $media->getFile(),
            $item->getMedias()->toArray()
        ));
    }

    private function mediaUrl(?Media $media): ?string
    {
        return $media !== null ? '/uploads/' . $media->getFile() : null;
    }
}
