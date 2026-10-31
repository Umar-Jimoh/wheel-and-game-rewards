<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261031234747 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pending_games (userId INTEGER NOT NULL, game VARCHAR NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__users AS SELECT name, points, id FROM users');
        $this->addSql('DROP TABLE users');
        $this->addSql('CREATE TABLE users (name VARCHAR(255) NOT NULL, points INTEGER NOT NULL, id INTEGER NOT NULL, PRIMARY KEY (id))');
        $this->addSql('INSERT INTO users (name, points, id) SELECT name, points, id FROM __temp__users');
        $this->addSql('DROP TABLE __temp__users');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE pending_games');
        $this->addSql('CREATE TEMPORARY TABLE __temp__users AS SELECT name, points, id FROM users');
        $this->addSql('DROP TABLE users');
        $this->addSql('CREATE TABLE users (name VARCHAR(255) NOT NULL, points INTEGER NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL)');
        $this->addSql('INSERT INTO users (name, points, id) SELECT name, points, id FROM __temp__users');
        $this->addSql('DROP TABLE __temp__users');
    }
}
