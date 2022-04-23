<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220423134107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE event_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE event (id INT NOT NULL, name VARCHAR(255) NOT NULL, status VARCHAR(255) DEFAULT NULL, organizer VARCHAR(255) DEFAULT NULL, test_name VARCHAR(255) DEFAULT NULL, test_type VARCHAR(255) DEFAULT NULL, regional_committee VARCHAR(255) DEFAULT NULL, discipline VARCHAR(255) DEFAULT NULL, distances INT DEFAULT NULL, date_from DATE NOT NULL, date_to DATE NOT NULL, city VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, phone_number VARCHAR(255) DEFAULT NULL, mail VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE event_id_seq CASCADE');
        $this->addSql('DROP TABLE event');
    }
}
