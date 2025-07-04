<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250704082337 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE figure_group (figure_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_7FEDC2FC5C011B5 (figure_id), INDEX IDX_7FEDC2FCFE54D947 (group_id), PRIMARY KEY(figure_id, group_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE figure_group ADD CONSTRAINT FK_7FEDC2FC5C011B5 FOREIGN KEY (figure_id) REFERENCES figure (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE figure_group ADD CONSTRAINT FK_7FEDC2FCFE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE figure_group DROP FOREIGN KEY FK_7FEDC2FC5C011B5');
        $this->addSql('ALTER TABLE figure_group DROP FOREIGN KEY FK_7FEDC2FCFE54D947');
        $this->addSql('DROP TABLE figure_group');
    }
}
