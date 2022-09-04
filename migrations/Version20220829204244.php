<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220829204244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE service_log (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', method VARCHAR(10) NOT NULL, status_code INT NOT NULL, slug VARCHAR(255) NOT NULL, version VARCHAR(20) NOT NULL, PRIMARY KEY(id), KEY `service_log_name_index` (`name`) USING BTREE, KEY `service_log_status_code_index` (`status_code`) USING BTREE, KEY `service_log_requested_at_index` (`requested_at`) USING BTREE, KEY `compound_status_code_name` (`status_code`,`name`) USING BTREE, KEY `compound_code_name_requested_at` (`name`,`status_code`,`requested_at`) USING BTREE) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE service_log');
    }
}
