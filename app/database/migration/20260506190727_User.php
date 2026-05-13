<?php

declare(strict_types=1);

namespace app\database\migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260506190727 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'User';
    }

    public function up(Schema $schema): void
     {
        $table = $schema->createTable('user');
        $table->addColumn('id',            'bigint', ['autoincrement' => true]);
        $table->addColumn('nome', 'text',  ['default' => '']);
        $table->addColumn('sobrenome', 'text',  ['default' => '']);
        $table->addColumn('cpf', 'text',  ['default' => '']);
        $table->addColumn('rg', 'text',  ['default' => '']);
        $table->addColumn('senha', 'text',  ['default' => '']);
        $table->addColumn('ativo', 'boolean',  ['default' => false]);
        $table->addColumn('administrador', 'boolean',  ['default' => false]);
        $table->addColumn('criado_em',     'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('atualizado_em', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['cpf']);
        $table->addIndex(['nome']);
        $table->addIndex(['sobrenome']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('user');
    }
}
