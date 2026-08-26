<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Renomme le modèle « Performer » en « Seller » (généricisation du domaine) :
 * tables, colonnes, contraintes et index. Aucun changement de structure.
 */
final class Version20260826120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename performer model to seller (tables, columns, constraints, indexes).';
    }

    public function up(Schema $schema): void
    {
        // Tables.
        $this->addSql('ALTER TABLE performer RENAME TO seller');
        $this->addSql('ALTER TABLE performer_media RENAME TO seller_media');

        // Séquence d'identité (PostgreSQL ne la renomme pas avec la table).
        $this->addSql('ALTER SEQUENCE performer_id_seq RENAME TO seller_id_seq');

        // Colonnes.
        $this->addSql('ALTER TABLE seller_media RENAME COLUMN performer_id TO seller_id');
        $this->addSql('ALTER TABLE item RENAME COLUMN performer_id TO seller_id');
        $this->addSql('ALTER TABLE shop_order RENAME COLUMN performer_id TO seller_id');
        $this->addSql('ALTER TABLE review RENAME COLUMN to_performer_id TO to_seller_id');

        // Contraintes & index (noms Doctrine basés sur crc32(table) + crc32(column)).
        $this->addSql('ALTER TABLE seller_media RENAME CONSTRAINT performer_media_pkey TO seller_media_pkey');
        $this->addSql('ALTER INDEX UNIQ_17210BEBA76ED395 RENAME TO UNIQ_FB1AD3FCA76ED395');
        $this->addSql('ALTER INDEX UNIQ_17210BEBD17F50A6 RENAME TO UNIQ_FB1AD3FCD17F50A6');
        $this->addSql('ALTER TABLE seller RENAME CONSTRAINT FK_17210BEBA76ED395 TO FK_FB1AD3FCA76ED395');
        $this->addSql('ALTER INDEX IDX_EAA0EA6E6C6B33F3 RENAME TO IDX_CA24BAB18DE820D9');
        $this->addSql('ALTER TABLE seller_media RENAME CONSTRAINT FK_EAA0EA6E6C6B33F3 TO FK_CA24BAB18DE820D9');
        $this->addSql('ALTER INDEX IDX_EAA0EA6EEA9FDD75 RENAME TO IDX_CA24BAB1EA9FDD75');
        $this->addSql('ALTER TABLE seller_media RENAME CONSTRAINT FK_EAA0EA6EEA9FDD75 TO FK_CA24BAB1EA9FDD75');
        $this->addSql('ALTER INDEX IDX_1F1B251E6C6B33F3 RENAME TO IDX_1F1B251E8DE820D9');
        $this->addSql('ALTER TABLE item RENAME CONSTRAINT FK_1F1B251E6C6B33F3 TO FK_1F1B251E8DE820D9');
        $this->addSql('ALTER INDEX IDX_323FC9CA6C6B33F3 RENAME TO IDX_323FC9CA8DE820D9');
        $this->addSql('ALTER TABLE shop_order RENAME CONSTRAINT FK_323FC9CA6C6B33F3 TO FK_323FC9CA8DE820D9');
        $this->addSql('ALTER INDEX IDX_794381C6B8FE902F RENAME TO IDX_794381C6BC4BF7A8');
        $this->addSql('ALTER TABLE review RENAME CONSTRAINT FK_794381C6B8FE902F TO FK_794381C6BC4BF7A8');

        // Champs âge/genre du profil (généricisation).
        $this->addSql('ALTER TABLE profile DROP COLUMN gender');
        $this->addSql('ALTER TABLE profile DROP COLUMN age');
    }

    public function down(Schema $schema): void
    {
        // Champs âge/genre du profil.
        $this->addSql('ALTER TABLE profile ADD age INT NOT NULL DEFAULT 18');
        $this->addSql('ALTER TABLE profile ADD gender VARCHAR(30) DEFAULT NULL');

        // Contraintes & index (retour aux noms d'origine).
        $this->addSql('ALTER TABLE review RENAME CONSTRAINT FK_794381C6BC4BF7A8 TO FK_794381C6B8FE902F');
        $this->addSql('ALTER INDEX IDX_794381C6BC4BF7A8 RENAME TO IDX_794381C6B8FE902F');
        $this->addSql('ALTER TABLE shop_order RENAME CONSTRAINT FK_323FC9CA8DE820D9 TO FK_323FC9CA6C6B33F3');
        $this->addSql('ALTER INDEX IDX_323FC9CA8DE820D9 RENAME TO IDX_323FC9CA6C6B33F3');
        $this->addSql('ALTER TABLE item RENAME CONSTRAINT FK_1F1B251E8DE820D9 TO FK_1F1B251E6C6B33F3');
        $this->addSql('ALTER INDEX IDX_1F1B251E8DE820D9 RENAME TO IDX_1F1B251E6C6B33F3');
        $this->addSql('ALTER TABLE seller_media RENAME CONSTRAINT FK_CA24BAB18DE820D9 TO FK_EAA0EA6E6C6B33F3');
        $this->addSql('ALTER INDEX IDX_CA24BAB18DE820D9 RENAME TO IDX_EAA0EA6E6C6B33F3');
        $this->addSql('ALTER TABLE seller_media RENAME CONSTRAINT FK_CA24BAB1EA9FDD75 TO FK_EAA0EA6EEA9FDD75');
        $this->addSql('ALTER INDEX IDX_CA24BAB1EA9FDD75 RENAME TO IDX_EAA0EA6EEA9FDD75');
        $this->addSql('ALTER TABLE seller RENAME CONSTRAINT FK_FB1AD3FCA76ED395 TO FK_17210BEBA76ED395');
        $this->addSql('ALTER INDEX UNIQ_FB1AD3FCD17F50A6 RENAME TO UNIQ_17210BEBD17F50A6');
        $this->addSql('ALTER INDEX UNIQ_FB1AD3FCA76ED395 RENAME TO UNIQ_17210BEBA76ED395');
        $this->addSql('ALTER TABLE seller_media RENAME CONSTRAINT seller_media_pkey TO performer_media_pkey');

        // Colonnes.
        $this->addSql('ALTER TABLE review RENAME COLUMN to_seller_id TO to_performer_id');
        $this->addSql('ALTER TABLE shop_order RENAME COLUMN seller_id TO performer_id');
        $this->addSql('ALTER TABLE item RENAME COLUMN seller_id TO performer_id');
        $this->addSql('ALTER TABLE seller_media RENAME COLUMN seller_id TO performer_id');

        // Tables.
        $this->addSql('ALTER TABLE seller_media RENAME TO performer_media');
        $this->addSql('ALTER TABLE seller RENAME TO performer');

        // Séquence d'identité.
        $this->addSql('ALTER SEQUENCE seller_id_seq RENAME TO performer_id_seq');
    }
}
