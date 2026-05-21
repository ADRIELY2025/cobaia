<?php

declare(strict_types=1);

namespace app\database\migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260520205714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Product';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('product');

        $table->addColumn('id',                   'bigint',   ['autoincrement' => true, 'unsigned' => true, 'notnull' => true]);
        $table->addColumn('nome',                 'string',   ['length' => 255, 'notnull' => true]);
        $table->addColumn('codigo_barra',         'text',     ['notnull' => false]);
        $table->addColumn('grupo',                'text',     ['notnull' => false]);
        $table->addColumn('unidade',              'text',     ['notnull' => false]);
        $table->addColumn('preco_compra',         'decimal',  ['precision' => 18, 'scale' => 4, 'notnull' => false]);
        $table->addColumn('total_imposto',        'decimal',  ['precision' => 18, 'scale' => 4, 'notnull' => false]);
        $table->addColumn('margem_lucro',         'decimal',  ['precision' => 18, 'scale' => 4, 'notnull' => false]);
        $table->addColumn('custo_operacional',    'decimal',  ['precision' => 18, 'scale' => 4, 'notnull' => false]);
        $table->addColumn('valor_venda_sugerido', 'decimal',  ['precision' => 18, 'scale' => 4, 'notnull' => false]);
        $table->addColumn('preco_venda',          'decimal',  ['precision' => 18, 'scale' => 4, 'notnull' => false]);
        $table->addColumn('descricao',            'text',     ['notnull' => false]);
        $table->addColumn('ativo',                'boolean',  ['default' => true,  'notnull' => true]);
        $table->addColumn('excluido',             'boolean',  ['default' => false, 'notnull' => true]);
        $table->addColumn('criado_em',            'datetime', ['notnull' => true, 'default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('atualizado_em',        'datetime', ['notnull' => true, 'default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['codigo_barra']);
        $table->addIndex(['ativo']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('product');
    }
}
