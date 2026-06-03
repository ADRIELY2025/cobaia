<?php

declare(strict_types=1);

namespace App\Database\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260602205814 extends AbstractMigration
{
     public function getDescription(): string
    {
        return 'ItemSale';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('sale_item');
        $table->addOption('comment', 'Itens de linha de cada venda — um registro por produto por venda');

        $table->addColumn('id',              'bigint',  ['autoincrement' => true, 'comment' => 'Identificador único do item de venda']);
        # NOT NULL: item de venda sem venda não tem sentido no domínio
        $table->addColumn('id_venda',        'bigint',  ['comment' => 'Referência à venda à qual este item pertence']);
        $table->addColumn('id_produto',      'bigint',  ['notnull' => false, 'comment' => 'Referência ao produto vendido neste item']);
        $table->addColumn('nome',            'text',    ['notnull' => false, 'comment' => 'Observações adicionais sobre o item de venda']);
        $table->addColumn('quantidade',      'decimal', ['precision' => 18, 'scale' => 4, 'comment' => 'Quantidade vendida do produto']);
        $table->addColumn('valor_unitario',  'decimal', ['precision' => 18, 'scale' => 4, 'comment' => 'Preço de venda unitário do produto']);
        # valor_custo exclusivo de sale_item: necessário para calcular total_lucro por item e reconstituir margem da venda
        $table->addColumn('valor_custo',     'decimal', ['precision' => 18, 'scale' => 4, 'notnull' => false, 'comment' => 'Custo unitário do produto no momento da venda — base para apuração do lucro']);
        $table->addColumn('valor_desconto',  'decimal', ['precision' => 18, 'scale' => 4, 'notnull' => false, 'comment' => 'Valor do desconto concedido ao cliente neste item']);
        $table->addColumn('valor_acrescimo', 'decimal', ['precision' => 18, 'scale' => 4, 'notnull' => false, 'comment' => 'Valor do acréscimo aplicado neste item']);
        $table->addColumn('total_bruto',     'decimal', ['precision' => 18, 'scale' => 4, 'comment' => 'Total bruto do item (quantidade × valor_unitario)']);
        $table->addColumn('total_liquido',   'decimal', ['precision' => 18, 'scale' => 4, 'comment' => 'Total líquido do item após descontos e acréscimos']);
        $table->addColumn('total_imposto',   'decimal', ['precision' => 18, 'scale' => 4, 'default' => '0', 'comment' => 'Total de impostos incidentes neste item']);
        $table->addColumn('total_lucro',     'decimal', ['precision' => 18, 'scale' => 4, 'notnull' => false, 'comment' => 'Lucro apurado neste item (total_liquido - (valor_custo × quantidade))']);
        $table->addColumn('observacao',      'text',    ['notnull' => false, 'comment' => 'Observações adicionais sobre o item de venda']);
        $table->addColumn('criado_em',       'datetime', ['default' => 'CURRENT_TIMESTAMP', 'comment' => 'Data e hora de criação do registro']);
        $table->addColumn('atualizado_em',   'datetime', ['notnull' => false, 'default' => 'CURRENT_TIMESTAMP', 'comment' => 'Data e hora da última atualização — mantida pelo trigger trg_sale_item_atualizado_em']);
        $table->setPrimaryKey(['id']);

        # Índice em id_venda — busca de todos os itens de uma venda é a operação mais frequente
        $table->addIndex(['id_venda'],   'idx_sale_item_id_venda');
        # Índice em id_produto — rastreamento do histórico de vendas por produto
        $table->addIndex(['id_produto'], 'idx_sale_item_id_produto');

        # CASCADE: deletar a venda remove seus itens — relação de composição
        $table->addForeignKeyConstraint('sale', ['id_venda'], ['id'], ['onDelete' => 'CASCADE', 'onUpdate' => 'NO ACTION'], 'fk_sale_item_id_venda');
        # RESTRICT: impede exclusão de produto com histórico de vendas (preserva rastreabilidade financeira)
        $table->addForeignKeyConstraint('product', ['id_produto'], ['id'], ['onDelete' => 'RESTRICT', 'onUpdate' => 'NO ACTION'], 'fk_sale_item_id_produto');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS sale_item CASCADE');
    }
}