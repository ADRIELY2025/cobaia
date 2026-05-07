<?php

declare(strict_types=1);

namespace app\database\migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260506190946 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Federative_Unit';
    }

    public function up(Schema $schema): void
    {
        // escreva aqui as alterações
    }

    public function down(Schema $schema): void
    {
        // escreva aqui o rollback do up()
    }
}