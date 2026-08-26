<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute le jeton de confirmation d'email sur app_user.
 */
final class Version20260816000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add email confirmation token columns to app_user.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_user ADD confirmation_token VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE app_user ADD confirmation_token_expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql(
            'CREATE UNIQUE INDEX UNIQ_88BDF3E9C05FB297 ON app_user (confirmation_token)'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_88BDF3E9C05FB297');
        $this->addSql('ALTER TABLE app_user DROP confirmation_token_expires_at');
        $this->addSql('ALTER TABLE app_user DROP confirmation_token');
    }
}
