<?php

declare(strict_types=1);

namespace App\Controller;

final class Product extends Base
{
    public function list($request, $response)
    {
        return $this->getTwig()
            ->render($response, $this->setView('list-product'), [
                'titulo' => 'Lista de produtos',
            ])
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function details($request, $response, $args)
    {
        $id     = $args['id'] ?? null;
        $action = ($id === null) ? 'c' : 'e';
        $product = [];

        if (!is_null($id)) {
            $qb = \app\database\DB::select('*')->from('product');

            $product = $qb
                ->where('id = ' . $qb->createPositionalParameter($id, \Doctrine\DBAL\ParameterType::INTEGER))
                ->fetchAssociative();
        }

        return $this->getTwig()
            ->render($response, $this->setView('product'), [
                'titulo'  => 'Detalhes do produto',
                'id'      => $id,
                'action'  => $action,
                'product' => $product,
            ])
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function insert($request, $response)
    {
        $form = $request->getParsedBody();

        $fieldsAndValues = [
    'nome'                 => $form['nome'],
    'codigo_barra'         => $form['codigoBarra']                          ?? null,
    'grupo'                => $form['grupo']                                ?? null,
    'unidade'              => $form['unidade']                              ?? null,
    'preco_compra'         => isset($form['precoCompra'])         ? (float) $form['precoCompra']         : null,
    'total_imposto'        => isset($form['totalImposto'])        ? (float) $form['totalImposto']        : null,
    'margem_lucro'         => isset($form['margemLucro'])         ? (float) $form['margemLucro']         : null,
    'custo_operacional'    => isset($form['custoOperacional'])    ? (float) $form['custoOperacional']    : null,
    'valor_venda_sugerido' => isset($form['valorVendaSugerido'])  ? (float) $form['valorVendaSugerido']  : null,
    'preco_venda'          => isset($form['precoVenda'])          ? (float) $form['precoVenda']          : null,
    'descricao'            => $form['descricao']                            ?? null,
    'ativo'                => ($form['ativo'] === 'true') ? true : false,
];

        try {
            $isInserted = \app\database\DB::connection()->insert('product', $fieldsAndValues);

            if (!$isInserted) {
                return $this->json($response, ['status' => false, 'msg' => 'Restrição: ' . $isInserted, 'id' => 0], 500);
            }

            $id = \app\database\DB::connection()->lastInsertId();

            return $this->json($response, ['status' => true, 'msg' => 'Salvo com sucesso!', 'id' => $id], 201);
        } catch (\Exception $e) {
            return $this->json($response, ['status' => false, 'msg' => 'Restrição: ' . $e->getMessage(), 'id' => 0], 500);
        }
    }

    public function update($request, $response)
    {
        $form = $request->getParsedBody();
        $id   = $form['id'] ?? null;

        if (is_null($id)) {
            return $this->json($response, ['status' => false, 'msg' => 'Por favor informe o ID do registro', 'id' => 0], 403);
        }

        $fieldsAndValues = [
            'nome'                 => $form['nome']               ?? null,
            'codigo_barra'         => $form['codigoBarra']        ?? null,
            'grupo'                => $form['grupo']              ?? null,
            'unidade'              => $form['unidade']            ?? null,
            'preco_compra'         => $form['precoCompra']        ?? null,
            'total_imposto'        => $form['totalImposto']       ?? null,
            'margem_lucro'         => $form['margemLucro']        ?? null,
            'custo_operacional'    => $form['custoOperacional']   ?? null,
            'valor_venda_sugerido' => $form['valorVendaSugerido'] ?? null,
            'preco_venda'          => $form['precoVenda']         ?? null,
            'descricao'            => $form['descricao']          ?? null,
            'ativo'                => ($form['ativo'] === 'true') ? true : false,
        ];

        try {
            $isUpdated = \app\database\DB::connection()->update('product', $fieldsAndValues, ['id' => $id]);

            if (!$isUpdated) {
                return $this->json($response, ['status' => false, 'msg' => 'Restrição: ' . $isUpdated, 'id' => 0], 403);
            }

            return $this->json($response, ['status' => true, 'msg' => 'Alterado com sucesso!', 'id' => $id], 201);
        } catch (\Exception $e) {
            return $this->json($response, ['status' => false, 'msg' => 'Restrição: ' . $e->getMessage(), 'id' => 0], 500);
        }
    }

    public function delete($request, $response)
    {
        $form = $request->getParsedBody();
        $id   = $form['id'] ?? null;

        if (is_null($id) || $id === '') {
            return $this->json($response, ['status' => false, 'msg' => 'Informe o código do produto', 'id' => 0], 403);
        }

        try {
            $isDeleted = \app\database\DB::connection()->delete('product', ['id' => $id]);

            if (!$isDeleted) {
                return $this->json($response, ['status' => false, 'msg' => 'Restrição: ' . $isDeleted, 'id' => $id], 403);
            }

            return $this->json($response, ['status' => true, 'msg' => 'Removido com sucesso!', 'id' => $id]);
        } catch (\Exception $e) {
            return $this->json($response, ['status' => false, 'msg' => 'Restrição: ' . $e->getMessage(), 'id' => 0], 500);
        }
    }

    public function listingdata($request, $response)
    {
        $form = $request->getParsedBody();

        $term   = $form['search']['value'] ?? null;
        $start  = (int) ($form['start']  ?? 0);
        $length = (int) ($form['length'] ?? 10);

        # Whitelist de colunas — proteção contra SQL injection no orderBy
        $columns = [
            0 => 'id',
            1 => 'nome',
            2 => 'codigo_barra',
            3 => 'grupo',
            4 => 'unidade',
            5 => 'preco_venda',
            6 => 'ativo',
            7 => 'criado_em',
            8 => 'atualizado_em',
        ];

        $posField = (isset($form['order'][0]['column']) && isset($columns[(int) $form['order'][0]['column']]))
            ? (int) $form['order'][0]['column']
            : 0;

        $orderType  = strtoupper($form['order'][0]['dir'] ?? 'DESC');
        $orderType  = in_array($orderType, ['ASC', 'DESC'], true) ? $orderType : 'DESC';
        $orderField = $columns[$posField];

        try {
            $totalRecords = (int) \app\database\DB::select('COUNT(*)')
                ->from('product')
                ->fetchOne();

            $query = \app\database\DB::select('*')->from('product');

            if (!is_null($term) && $term !== '') {
                $query->setParameter('term', '%' . $term . '%');

                $query->where('CAST(id AS TEXT) ILIKE :term')
                    ->orWhere('nome ILIKE :term')
                    ->orWhere('codigo_barra ILIKE :term')
                    ->orWhere('grupo ILIKE :term')
                    ->orWhere('unidade ILIKE :term')
                    ->orWhere("TO_CHAR(criado_em, 'DD/MM/YYYY HH24:MI:SS') ILIKE :term")
                    ->orWhere("TO_CHAR(atualizado_em, 'DD/MM/YYYY HH24:MI:SS') ILIKE :term");
            }

            $filteredRecords = (int) (clone $query)
                ->select('COUNT(*)')
                ->fetchOne();

            $products = $query
                ->orderBy($orderField, $orderType)
                ->setFirstResult($start)
                ->setMaxResults($length)
                ->fetchAllAssociative();

            $rows = [];
            foreach ($products as $key => $value) {
                $rows[$key] = [
                    $value['id'],
                    $value['nome'],
                    $value['codigo_barra'] ?? '-',
                    $value['grupo']        ?? '-',
                    $value['unidade']      ?? '-',
                    number_format((float) ($value['preco_venda'] ?? 0), 2, ',', '.'),
                    ($value['ativo'] === true) ? 'Ativo' : 'Inativo',
                    (new \DateTime($value['criado_em']))->format('d/m/Y H:i:s'),
                    (new \DateTime($value['atualizado_em']))->format('d/m/Y H:i:s'),
                    "<td>
                        <a class='btn btn-sm btn-warning' href='/produto/detalhes/" . $value['id'] . "'><i class='fa-solid fa-pen-to-square'></i> Editar</a>
                        <button type='button' class='btn btn-sm btn-danger' onclick='ShowModal(" . $value['id'] . ");'><i class='fa-solid fa-trash'></i> Excluir</button>
                    </td>",
                ];
            }

            return $this->json($response, [
                'recordsTotal'    => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data'            => $rows,
            ], 200);
        } catch (\Exception $e) {
            return $this->json($response, [
                'status' => false,
                'msg'    => 'Restrição: ' . $e->getMessage(),
                'id'     => 0,
            ], 500);
        }
    }
}