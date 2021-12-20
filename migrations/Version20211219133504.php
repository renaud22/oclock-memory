<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Card;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211219133504 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE card (
                id INT AUTO_INCREMENT NOT NULL,
                label VARCHAR(50) NOT NULL,
                created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE game (
                id INT AUTO_INCREMENT NOT NULL,
                duration INT NOT NULL,
                created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );

        // On insert les différentes cartes à jouer de base qui serviront d'emblée à faire une partie
        $cards = [
            'redApple',
            'banana',
            'orange',
            'greenLemon',
            'pomegranate',
            'apricot',
            'lemon',
            'strawberry',
            'greenApple',
            'peach',
            'grape',
            'watermelon',
            'prune',
            'pear',
            'cherry',
            'raspberry',
            'mango',
            'mirabelle',
        ];
        $date = date('Y-m-d H:i:s');
        foreach ($cards as $card) {
            $this->addSql("INSERT INTO `card` (`label`, `created_at`) VALUES ('$card', '$date')");
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE game');
    }
}
