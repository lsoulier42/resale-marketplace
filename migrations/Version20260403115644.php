<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260403115644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE address_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE app_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE customer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE item_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE media_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE performer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE profile_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE review_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE shop_order_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE address (address_line VARCHAR(255) NOT NULL, city VARCHAR(100) NOT NULL, zip_code VARCHAR(20) NOT NULL, country VARCHAR(100) NOT NULL, name VARCHAR(100) NOT NULL, id INT NOT NULL, uuid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, user_id INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D4E6F81D17F50A6 ON address (uuid)');
        $this->addSql('CREATE INDEX IDX_D4E6F81A76ED395 ON address (user_id)');
        $this->addSql('CREATE TABLE app_user (email VARCHAR(180) NOT NULL, is_verified BOOLEAN NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, id INT NOT NULL, uuid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_88BDF3E9E7927C74 ON app_user (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_88BDF3E9D17F50A6 ON app_user (uuid)');
        $this->addSql('CREATE TABLE category (title VARCHAR(150) NOT NULL, description TEXT DEFAULT NULL, id INT NOT NULL, uuid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_64C19C1D17F50A6 ON category (uuid)');
        $this->addSql('CREATE TABLE customer (id INT NOT NULL, uuid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, user_id INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_81398E09D17F50A6 ON customer (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_81398E09A76ED395 ON customer (user_id)');
        $this->addSql('CREATE TABLE customer_media (customer_id INT NOT NULL, media_id INT NOT NULL, PRIMARY KEY (customer_id, media_id))');
        $this->addSql('CREATE INDEX IDX_A419D4379395C3F3 ON customer_media (customer_id)');
        $this->addSql('CREATE INDEX IDX_A419D437EA9FDD75 ON customer_media (media_id)');
        $this->addSql('CREATE TABLE item (title VARCHAR(200) NOT NULL, description TEXT DEFAULT NULL, price NUMERIC(10, 2) NOT NULL, payment_url VARCHAR(255) DEFAULT NULL, available_count INT NOT NULL, id INT NOT NULL, uuid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, category_id INT NOT NULL, performer_id INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1F1B251ED17F50A6 ON item (uuid)');
        $this->addSql('CREATE INDEX IDX_1F1B251E12469DE2 ON item (category_id)');
        $this->addSql('CREATE INDEX IDX_1F1B251E6C6B33F3 ON item (performer_id)');
        $this->addSql('CREATE TABLE item_media (item_id INT NOT NULL, media_id INT NOT NULL, PRIMARY KEY (item_id, media_id))');
        $this->addSql('CREATE INDEX IDX_408BBADC126F525E ON item_media (item_id)');
        $this->addSql('CREATE INDEX IDX_408BBADCEA9FDD75 ON item_media (media_id)');
        $this->addSql('CREATE TABLE media (file VARCHAR(255) NOT NULL, file_size INT NOT NULL, file_type VARCHAR(128) NOT NULL, id INT NOT NULL, uuid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6A2CA10CD17F50A6 ON media (uuid)');
        $this->addSql('CREATE TABLE performer (id INT NOT NULL, uuid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, user_id INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_17210BEBD17F50A6 ON performer (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_17210BEBA76ED395 ON performer (user_id)');
        $this->addSql('CREATE TABLE performer_media (performer_id INT NOT NULL, media_id INT NOT NULL, PRIMARY KEY (performer_id, media_id))');
        $this->addSql('CREATE INDEX IDX_EAA0EA6E6C6B33F3 ON performer_media (performer_id)');
        $this->addSql('CREATE INDEX IDX_EAA0EA6EEA9FDD75 ON performer_media (media_id)');
        $this->addSql('CREATE TABLE profile (display_name VARCHAR(100) NOT NULL, gender VARCHAR(30) DEFAULT NULL, age INT NOT NULL, bio TEXT DEFAULT NULL, id INT NOT NULL, uuid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, avatar_id INT DEFAULT NULL, user_id INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8157AA0FD17F50A6 ON profile (uuid)');
        $this->addSql('CREATE INDEX IDX_8157AA0F86383B10 ON profile (avatar_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8157AA0FA76ED395 ON profile (user_id)');
        $this->addSql('CREATE TABLE review (star INT NOT NULL, comment TEXT DEFAULT NULL, id INT NOT NULL, uuid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, order_id INT NOT NULL, from_customer_id INT NOT NULL, to_performer_id INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_794381C6D17F50A6 ON review (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_794381C68D9F6D38 ON review (order_id)');
        $this->addSql('CREATE INDEX IDX_794381C6DDAF296 ON review (from_customer_id)');
        $this->addSql('CREATE INDEX IDX_794381C6B8FE902F ON review (to_performer_id)');
        $this->addSql('CREATE TABLE shop_order (reference VARCHAR(64) NOT NULL, tracking_number VARCHAR(100) DEFAULT NULL, shipping_provider VARCHAR(100) DEFAULT NULL, status VARCHAR(30) NOT NULL, shipping_fee NUMERIC(10, 2) NOT NULL, total_price NUMERIC(10, 2) NOT NULL, id INT NOT NULL, uuid UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, item_id INT NOT NULL, customer_id INT NOT NULL, performer_id INT NOT NULL, shipping_address_id INT DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_323FC9CAAEA34913 ON shop_order (reference)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_323FC9CAD17F50A6 ON shop_order (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_323FC9CA126F525E ON shop_order (item_id)');
        $this->addSql('CREATE INDEX IDX_323FC9CA9395C3F3 ON shop_order (customer_id)');
        $this->addSql('CREATE INDEX IDX_323FC9CA6C6B33F3 ON shop_order (performer_id)');
        $this->addSql('CREATE INDEX IDX_323FC9CA4D4CFF2B ON shop_order (shipping_address_id)');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F81A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE customer_media ADD CONSTRAINT FK_A419D4379395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE customer_media ADD CONSTRAINT FK_A419D437EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E6C6B33F3 FOREIGN KEY (performer_id) REFERENCES performer (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE item_media ADD CONSTRAINT FK_408BBADC126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item_media ADD CONSTRAINT FK_408BBADCEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE performer ADD CONSTRAINT FK_17210BEBA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE performer_media ADD CONSTRAINT FK_EAA0EA6E6C6B33F3 FOREIGN KEY (performer_id) REFERENCES performer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE performer_media ADD CONSTRAINT FK_EAA0EA6EEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0F86383B10 FOREIGN KEY (avatar_id) REFERENCES media (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0FA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C68D9F6D38 FOREIGN KEY (order_id) REFERENCES shop_order (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6DDAF296 FOREIGN KEY (from_customer_id) REFERENCES customer (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6B8FE902F FOREIGN KEY (to_performer_id) REFERENCES performer (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE shop_order ADD CONSTRAINT FK_323FC9CA126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE RESTRICT NOT DEFERRABLE');
        $this->addSql('ALTER TABLE shop_order ADD CONSTRAINT FK_323FC9CA9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) ON DELETE RESTRICT NOT DEFERRABLE');
        $this->addSql('ALTER TABLE shop_order ADD CONSTRAINT FK_323FC9CA6C6B33F3 FOREIGN KEY (performer_id) REFERENCES performer (id) ON DELETE RESTRICT NOT DEFERRABLE');
        $this->addSql('ALTER TABLE shop_order ADD CONSTRAINT FK_323FC9CA4D4CFF2B FOREIGN KEY (shipping_address_id) REFERENCES address (id) ON DELETE SET NULL NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE address_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE app_user_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE customer_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE item_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE media_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE performer_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE profile_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE review_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE shop_order_id_seq CASCADE');
        $this->addSql('ALTER TABLE address DROP CONSTRAINT FK_D4E6F81A76ED395');
        $this->addSql('ALTER TABLE customer DROP CONSTRAINT FK_81398E09A76ED395');
        $this->addSql('ALTER TABLE customer_media DROP CONSTRAINT FK_A419D4379395C3F3');
        $this->addSql('ALTER TABLE customer_media DROP CONSTRAINT FK_A419D437EA9FDD75');
        $this->addSql('ALTER TABLE item DROP CONSTRAINT FK_1F1B251E12469DE2');
        $this->addSql('ALTER TABLE item DROP CONSTRAINT FK_1F1B251E6C6B33F3');
        $this->addSql('ALTER TABLE item_media DROP CONSTRAINT FK_408BBADC126F525E');
        $this->addSql('ALTER TABLE item_media DROP CONSTRAINT FK_408BBADCEA9FDD75');
        $this->addSql('ALTER TABLE performer DROP CONSTRAINT FK_17210BEBA76ED395');
        $this->addSql('ALTER TABLE performer_media DROP CONSTRAINT FK_EAA0EA6E6C6B33F3');
        $this->addSql('ALTER TABLE performer_media DROP CONSTRAINT FK_EAA0EA6EEA9FDD75');
        $this->addSql('ALTER TABLE profile DROP CONSTRAINT FK_8157AA0F86383B10');
        $this->addSql('ALTER TABLE profile DROP CONSTRAINT FK_8157AA0FA76ED395');
        $this->addSql('ALTER TABLE review DROP CONSTRAINT FK_794381C68D9F6D38');
        $this->addSql('ALTER TABLE review DROP CONSTRAINT FK_794381C6DDAF296');
        $this->addSql('ALTER TABLE review DROP CONSTRAINT FK_794381C6B8FE902F');
        $this->addSql('ALTER TABLE shop_order DROP CONSTRAINT FK_323FC9CA126F525E');
        $this->addSql('ALTER TABLE shop_order DROP CONSTRAINT FK_323FC9CA9395C3F3');
        $this->addSql('ALTER TABLE shop_order DROP CONSTRAINT FK_323FC9CA6C6B33F3');
        $this->addSql('ALTER TABLE shop_order DROP CONSTRAINT FK_323FC9CA4D4CFF2B');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE app_user');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE customer_media');
        $this->addSql('DROP TABLE item');
        $this->addSql('DROP TABLE item_media');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE performer');
        $this->addSql('DROP TABLE performer_media');
        $this->addSql('DROP TABLE profile');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE shop_order');
    }
}
