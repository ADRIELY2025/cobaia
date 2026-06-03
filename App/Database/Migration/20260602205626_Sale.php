<?php

declare(strict_types=1);

namespace App\Database\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260602205626 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sale';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('sale');
        $table->addOption('comment', 'Tabela de vendas (operação comercial)');
        $table->addColumn('id',               'bigint',   ['autoincrement' => true, 'comment' => 'Identificador único da venda']);
        $table->addColumn('id_cliente',       'bigint',   ['notnull' => false, 'comment' => 'Referência ao cliente da venda']);
        $table->addColumn('valor_desconto',   'decimal',  ['precision' => 18, 'scale' => 4, 'notnull' => false, 'comment' => 'Valor absoluto do desconto aplicado na venda']);
        $table->addColumn('valor_acrescimo',  'decimal',  ['precision' => 18, 'scale' => 4, 'notnull' => false, 'comment' => 'Valor absoluto do acréscimo aplicado na venda']);
        $table->addColumn('total_bruto',      'decimal',  ['precision' => 18, 'scale' => 4, 'comment' => 'Total bruto da venda antes de descontos e acréscimos']);
        $table->addColumn('total_liquido',    'decimal',  ['precision' => 18, 'scale' => 4, 'comment' => 'Total líquido da venda após descontos e acréscimos']);
        $table->addColumn('total_imposto',    'decimal',  ['precision' => 18, 'scale' => 4, 'default' => '0', 'comment' => 'Total de impostos incidentes na venda']);
        $table->addColumn('total_frete',      'decimal',  ['precision' => 18, 'scale' => 4, 'comment' => 'Total de frete da venda']);
        $table->addColumn('total_lucro',      'decimal',  ['precision' => 18, 'scale' => 4, 'comment' => 'Lucro total apurado na venda']);
        $table->addColumn('observacao',       'text',     ['notnull' => false, 'comment' => 'Observações gerais sobre a venda']);
        $table->addColumn('status',           'text',     ['columnDefinition' => "TEXT NOT NULL CHECK (status IN ('ORCAMENTO', 'PRE_VENDA', 'VENDA'))"]);
        $table->addColumn('criado_em',        'datetime', ['default' => 'CURRENT_TIMESTAMP', 'comment' => 'Data e hora de criação do registro']);
        $table->addColumn('atualizado_em',    'datetime', ['notnull' => false, 'default' => 'CURRENT_TIMESTAMP', 'comment' => 'Data e hora da última atualização — mantida pelo trigger trg_sale_atualizado_em']);
        $table->setPrimaryKey(['id']);
        # FK id_cliente  person.id: CASCADE remove vendas quando o cliente for excluído
        $table->addForeignKeyConstraint('customer', ['id_cliente'], ['id'], ['onDelete' => 'CASCADE', 'onUpdate' => 'NO ACTION'], 'fk_sale_id_cliente');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS sale CASCADE');
    }
}