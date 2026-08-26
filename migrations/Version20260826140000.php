<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Intégration Stripe Connect : compte vendeur (onboarding), session de
 * paiement sur les commandes, et suppression du lien de paiement externe
 * des articles (remplacé par la Checkout Session hébergée).
 */
final class Version20260826140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Stripe Connect fields (seller account, order checkout) and drop item.payment_url.';
    }

    public function up(Schema $schema): void
    {
        // Compte Stripe Connect du vendeur·se (créé lors de l'onboarding).
        $this->addSql('ALTER TABLE seller ADD stripe_account_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE seller ADD stripe_account_ready BOOLEAN DEFAULT false NOT NULL');
        // Pas de DEFAULT en base : il ne vient que du mapping Doctrine.
        $this->addSql('ALTER TABLE seller ALTER stripe_account_ready DROP DEFAULT');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FB1AD3FCE065F932 ON seller (stripe_account_id)');

        // Session de paiement Stripe liée à la commande.
        $this->addSql('ALTER TABLE shop_order ADD stripe_checkout_session_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE shop_order ADD stripe_payment_intent_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_323FC9CA5A18FBC7 ON shop_order (stripe_checkout_session_id)');

        // Le paiement passe désormais par Stripe : plus de lien externe.
        $this->addSql('ALTER TABLE item DROP payment_url');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE item ADD payment_url VARCHAR(255) DEFAULT NULL');

        $this->addSql('DROP INDEX UNIQ_323FC9CA5A18FBC7');
        $this->addSql('ALTER TABLE shop_order DROP stripe_payment_intent_id');
        $this->addSql('ALTER TABLE shop_order DROP stripe_checkout_session_id');

        $this->addSql('DROP INDEX UNIQ_FB1AD3FCE065F932');
        $this->addSql('ALTER TABLE seller DROP stripe_account_ready');
        $this->addSql('ALTER TABLE seller DROP stripe_account_id');
    }
}
