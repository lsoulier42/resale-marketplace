<?php

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\Category;
use App\Entity\Customer;
use App\Entity\Item;
use App\Entity\Media;
use App\Entity\Order;
use App\Entity\Profile;
use App\Entity\Review;
use App\Entity\Seller;
use App\Entity\User;
use App\Enum\OrderStatus;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends AbstractFixtures
{
    public const DEMO_PASSWORD = 'demo1234';

    /**
     * @var array<string, string> titre => description
     */
    private const CATEGORIES = [
        'Vêtements' => 'Mode et vêtements d’occasion en bon état.',
        'Chaussures & accessoires' => 'Chaussures, sacs, ceintures et accessoires.',
        'Maison & déco' => 'Objets déco, vaisselle et petits meubles.',
        'High-tech' => 'Smartphones, ordinateurs et objets connectés.',
        'Loisirs' => 'Sport, jeux, musique et hobbies.',
        'Livres & médias' => 'Livres, BD, disques et jeux vidéo.',
    ];

    /**
     * @var list<array{email: string, display_name: string, bio: string, avatar: string}>
     */
    private const SELLERS = [
        [
            'email' => 'alex@example.test',
            'display_name' => 'Alex',
            'bio' => 'Vendeur régulier, envoi soigné sous 48h.',
            'avatar' => 'alex',
        ],
        [
            'email' => 'sam@example.test',
            'display_name' => 'Sam',
            'bio' => 'Boutique variée : qualité et bonnes affaires.',
            'avatar' => 'sam',
        ],
        [
            'email' => 'jordan@example.test',
            'display_name' => 'Jordan',
            'bio' => 'Articles triés avec soin, vente entre particuliers.',
            'avatar' => 'jordan',
        ],
    ];

    /**
     * @var list<array{email: string, name: string, city: string, zip: string}>
     */
    private const CUSTOMERS = [
        ['email' => 'camille@example.test', 'name' => 'Camille R.', 'city' => 'Lyon', 'zip' => '69003'],
        ['email' => 'julien@example.test', 'name' => 'Julien M.', 'city' => 'Paris', 'zip' => '75011'],
        ['email' => 'sophie@example.test', 'name' => 'Sophie L.', 'city' => 'Marseille', 'zip' => '13006'],
    ];

    /**
     * Photos libres (licence Pexels) par article, stockées dans
     * src/DataFixtures/medias/ (voir CREDITS.md pour les sources).
     *
     * @var array<string, list<string>>
     */
    private const ITEM_PHOTOS = [
        'Robe vintage taille 38' => ['robe-vintage-1.jpg', 'robe-vintage-2.jpg'],
        'Veste en jean légère' => ['veste-jean-1.jpg', 'veste-jean-2.jpg'],
        'Sac à main en cuir' => ['sac-cuir-1.jpg', 'sac-cuir-2.jpg'],
        'Lampe design années 50' => ['lampe-1.jpg', 'lampe-2.jpg'],
        'Chaise scandinave en bois' => ['chaise-1.jpg', 'chaise-2.jpg'],
        'Casque audio sans fil' => ['casque-1.jpg', 'casque-2.jpg'],
        'Trottinette électrique' => ['trottinette-1.jpg', 'trottinette-2.jpg'],
        'Console de jeux rétro' => ['console-1.jpg', 'console-2.jpg'],
        'Montre connectée' => ['montre-1.jpg', 'montre-2.jpg'],
        'Tasse céramique artisanale' => ['tasse-1.jpg', 'tasse-2.jpg'],
        'Recueil de nouvelles SF' => ['livres-sf-1.jpg', 'livres-sf-2.jpg'],
        'Vinyle jazz des années 60' => ['vinyle-1.jpg', 'vinyle-2.jpg'],
    ];

    /**
     * @var list<array{title: string, category: string, price: float, available: int, seller: int}>
     */
    private const ITEMS = [
        [
            'title' => 'Robe vintage taille 38',
            'category' => 'Vêtements',
            'price' => 39.90,
            'available' => 1,
            'seller' => 0,
        ],
        [
            'title' => 'Veste en jean légère',
            'category' => 'Vêtements',
            'price' => 32.00,
            'available' => 2,
            'seller' => 2,
        ],
        [
            'title' => 'Sac à main en cuir',
            'category' => 'Chaussures & accessoires',
            'price' => 45.00,
            'available' => 1,
            'seller' => 1,
        ],
        [
            'title' => 'Lampe design années 50',
            'category' => 'Maison & déco',
            'price' => 28.50,
            'available' => 1,
            'seller' => 0,
        ],
        [
            'title' => 'Chaise scandinave en bois',
            'category' => 'Maison & déco',
            'price' => 25.00,
            'available' => 3,
            'seller' => 2,
        ],
        [
            'title' => 'Casque audio sans fil',
            'category' => 'High-tech',
            'price' => 35.00,
            'available' => 1,
            'seller' => 1,
        ],
        [
            'title' => 'Trottinette électrique',
            'category' => 'Loisirs',
            'price' => 42.00,
            'available' => 1,
            'seller' => 0,
        ],
        [
            'title' => 'Console de jeux rétro',
            'category' => 'Loisirs',
            'price' => 30.00,
            'available' => 2,
            'seller' => 2,
        ],
        [
            'title' => 'Montre connectée',
            'category' => 'High-tech',
            'price' => 55.00,
            'available' => 1,
            'seller' => 1,
        ],
        [
            'title' => 'Tasse céramique artisanale',
            'category' => 'Maison & déco',
            'price' => 27.00,
            'available' => 1,
            'seller' => 0,
        ],
        [
            'title' => 'Recueil de nouvelles SF',
            'category' => 'Livres & médias',
            'price' => 22.00,
            'available' => 4,
            'seller' => 2,
        ],
        [
            'title' => 'Vinyle jazz des années 60',
            'category' => 'Livres & médias',
            'price' => 33.00,
            'available' => 1,
            'seller' => 1,
        ],
    ];

    /**
     * @var list<array{item: int, customer: int, status: OrderStatus, tracking?: string}>
     */
    private const ORDERS = [
        ['item' => 1, 'customer' => 0, 'status' => OrderStatus::DELIVERED, 'tracking' => '7L12345678901'],
        ['item' => 4, 'customer' => 1, 'status' => OrderStatus::SHIPPED, 'tracking' => '7L98765432109'],
        ['item' => 8, 'customer' => 2, 'status' => OrderStatus::PAID],
        ['item' => 2, 'customer' => 0, 'status' => OrderStatus::PENDING_PAYMENT],
        ['item' => 5, 'customer' => 1, 'status' => OrderStatus::DELIVERED, 'tracking' => '7L55555555555'],
        ['item' => 9, 'customer' => 2, 'status' => OrderStatus::PENDING_PAYMENT],
        ['item' => 11, 'customer' => 0, 'status' => OrderStatus::CANCELLED],
    ];

    /**
     * @var list<array{order: int, star: int, comment: string}>
     */
    private const REVIEWS = [
        [
            'order' => 0,
            'star' => 5,
            'comment' => 'Envoi rapide et article conforme à la description. Merci !',
        ],
        [
            'order' => 4,
            'star' => 4,
            'comment' => 'Très bonne expérience, je recommande.',
        ],
    ];

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadAdmin($manager);
        $medias = $this->loadMedias($manager);
        $sellers = $this->loadSellers($manager, $medias);
        $customers = $this->loadCustomers($manager);
        $categories = $this->loadCategories($manager);
        $items = $this->loadItems($manager, $sellers, $categories, $medias);
        $this->loadOrdersAndReviews($manager, $customers, $items);

        $manager->flush();
    }

    private function loadAdmin(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@example.test')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->passwordHasher->hashPassword($admin, self::DEMO_PASSWORD))
            ->setIsVerified(true);
        $manager->persist($admin);
    }

    /**
     * @param array<string, Media> $medias
     * @return list<Seller>
     */
    private function loadSellers(ObjectManager $manager, array $medias): array
    {
        $sellers = [];
        foreach (self::SELLERS as $data) {
            $user = new User();
            $user->setEmail($data['email'])
                ->setRoles(['ROLE_USER'])
                ->setPassword($this->passwordHasher->hashPassword($user, self::DEMO_PASSWORD))
                ->setIsVerified(true);

            $profile = new Profile();
            $profile->setUser($user)
                ->setDisplayName($data['display_name'])
                ->setBio($data['bio'])
                ->setAvatar($medias[sprintf('avatar-%s.jpg', $data['avatar'])] ?? null);

            $seller = new Seller();
            $seller->setUser($user);

            $manager->persist($user);
            $manager->persist($profile);
            $manager->persist($seller);
            $sellers[] = $seller;
        }

        return $sellers;
    }

    /**
     * @return list<array{customer: Customer, address: Address}>
     */
    private function loadCustomers(ObjectManager $manager): array
    {
        $customers = [];
        foreach (self::CUSTOMERS as $data) {
            $user = new User();
            $user->setEmail($data['email'])
                ->setRoles(['ROLE_USER'])
                ->setPassword($this->passwordHasher->hashPassword($user, self::DEMO_PASSWORD))
                ->setIsVerified(true);

            $customer = new Customer();
            $customer->setUser($user);

            $address = new Address();
            $address->setUser($user)
                ->setName($data['name'])
                ->setAddressLine($this->faker->streetAddress())
                ->setCity($data['city'])
                ->setZipCode($data['zip'])
                ->setCountry('France');

            $manager->persist($user);
            $manager->persist($customer);
            $manager->persist($address);
            $customers[] = ['customer' => $customer, 'address' => $address];
        }

        return $customers;
    }

    /**
     * @return array<string, Category>
     */
    private function loadCategories(ObjectManager $manager): array
    {
        $categories = [];
        foreach (self::CATEGORIES as $title => $description) {
            $category = new Category();
            $category->setTitle($title)->setDescription($description);
            $manager->persist($category);
            $categories[$title] = $category;
        }

        return $categories;
    }

    /**
     * Copie les photos libres versionnées (src/DataFixtures/medias) vers
     * public/uploads (non versionné) et les enregistre en base.
     *
     * @return array<string, Media> fichier => Media
     */
    private function loadMedias(ObjectManager $manager): array
    {
        $sourceDir = dirname(__DIR__, 2) . '/src/DataFixtures/medias';
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $medias = [];
        foreach (glob($sourceDir . '/*.jpg') ?: [] as $source) {
            $filename = basename($source);
            copy($source, $uploadDir . '/' . $filename);

            $media = new Media();
            $media->setFile($filename)
                ->setFileSize((int) filesize($uploadDir . '/' . $filename))
                ->setFileType('image/jpeg');
            $manager->persist($media);
            $medias[$filename] = $media;
        }

        return $medias;
    }

    /**
     * @param list<Seller> $sellers
     * @param array<string, Category> $categories
     * @param array<string, Media> $medias
     * @return list<Item>
     */
    private function loadItems(ObjectManager $manager, array $sellers, array $categories, array $medias): array
    {
        $items = [];
        foreach (self::ITEMS as $data) {
            $item = new Item();
            $item->setTitle($data['title'])
                ->setDescription($this->faker->sentence(10))
                ->setPrice(number_format($data['price'], 2, '.', ''))
                ->setAvailableCount($data['available'])
                ->setCategory($categories[$data['category']])
                ->setSeller($sellers[$data['seller']]);

            // Deux photos libres correspondant à l'article.
            foreach (self::ITEM_PHOTOS[$data['title']] as $photo) {
                if (isset($medias[$photo])) {
                    $item->addMedia($medias[$photo]);
                }
            }

            $manager->persist($item);
            $items[] = $item;
        }

        return $items;
    }

    /**
     * @param list<array{customer: Customer, address: Address}> $customers
     * @param list<Item> $items
     */
    private function loadOrdersAndReviews(ObjectManager $manager, array $customers, array $items): void
    {
        $deliveredOrders = [];
        foreach (self::ORDERS as $index => $data) {
            /** @var Item $item */
            $item = $items[$data['item']];
            $item->setAvailableCount(0);

            $customerData = $customers[$data['customer']];
            $customer = $customerData['customer'];

            $order = new Order();
            $order->setReference(sprintf('REF-DEMO-%03d', $index + 1))
                ->setItem($item)
                ->setCustomer($customer)
                ->setSeller($item->getSeller())
                ->setStatus($data['status'])
                ->setShippingFee('4.90')
                ->setTotalPrice(number_format((float) $item->getPrice() + 4.90, 2, '.', ''))
                ->setShippingAddress($customerData['address']);

            if (isset($data['tracking'])) {
                $order->setTrackingNumber($data['tracking'])
                    ->setShippingProvider('Colissimo');
            }

            $manager->persist($order);

            if ($data['status'] === OrderStatus::DELIVERED) {
                $deliveredOrders[$index] = $order;
            }
        }

        foreach (self::REVIEWS as $data) {
            /** @var Order $order */
            $order = $deliveredOrders[$data['order']];
            $review = new Review();
            $review->setOrder($order)
                ->setFromUser($order->getCustomer())
                ->setToUser($order->getSeller())
                ->setStar($data['star'])
                ->setComment($data['comment']);
            $manager->persist($review);
        }
    }
}
