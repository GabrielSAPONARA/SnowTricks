<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250704080534 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE figure ADD groupe_id INT NOT NULL');
        $this->addSql('ALTER TABLE figure ADD CONSTRAINT FK_2F57B37A7A45358C FOREIGN KEY (groupe_id) REFERENCES `group` (id)');
        $this->addSql('CREATE INDEX IDX_2F57B37A7A45358C ON figure (groupe_id)');
        $this->addSql('ALTER TABLE picture_figure ADD figure_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE picture_figure ADD CONSTRAINT FK_BEB3056A5C011B5 FOREIGN KEY (figure_id) REFERENCES figure (id)');
        $this->addSql('CREATE INDEX IDX_BEB3056A5C011B5 ON picture_figure (figure_id)');
        $this->addSql('ALTER TABLE video_figure ADD figure_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE video_figure ADD CONSTRAINT FK_170013B75C011B5 FOREIGN KEY (figure_id) REFERENCES figure (id)');
        $this->addSql('CREATE INDEX IDX_170013B75C011B5 ON video_figure (figure_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE figure DROP FOREIGN KEY FK_2F57B37A7A45358C');
        $this->addSql('DROP INDEX IDX_2F57B37A7A45358C ON figure');
        $this->addSql('ALTER TABLE figure DROP groupe_id');
        $this->addSql('ALTER TABLE picture_figure DROP FOREIGN KEY FK_BEB3056A5C011B5');
        $this->addSql('DROP INDEX IDX_BEB3056A5C011B5 ON picture_figure');
        $this->addSql('ALTER TABLE picture_figure DROP figure_id');
        $this->addSql('ALTER TABLE video_figure DROP FOREIGN KEY FK_170013B75C011B5');
        $this->addSql('DROP INDEX IDX_170013B75C011B5 ON video_figure');
        $this->addSql('ALTER TABLE video_figure DROP figure_id');
    }
}
