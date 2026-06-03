<?php

declare(strict_types=1);

namespace App\Controller;

use App\Database\DB;
use Doctrine\DBAL\ParameterType;

class Sale extends Base
{
    public function list($request, $response)
    {
        return $this->getTwig()
            ->render($response, $this->setView('list-sale'), [
                'titulo' => 'Lista de vendas',
            ])
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function details($request, $response, $args)
    {
        $id = $args['id'] ?? null;
        $action = ($id === null) ? 'c' : 'e';
        $sale = [];

        if (!is_null($id)) {
            $qb = DB::select('*')->from('sale');

            $sale = $qb->where(
                'id = ' . $qb->createPositionalParameter($id, ParameterType::INTEGER)
            )->fetchAssociative();
        }

        return $this->getTwig()
            ->render($response, $this->setView('sale'), [
                'titulo' => 'Detalhes da venda',
                'id' => $id,
                'action' => $action,
                'sale' => $sale
            ])
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function insert($request, $response)
    {
        $form = $request->getParsedBody();

        $id_produto = $form['pesquisa'] ?? null;

        if (empty($id_produto)) {
            return $this->json($response, [
                'status' => false,
                'msg' => 'O ID do produto é obrigatório!',
                'id' => 0
            ], 403);
        }

        $customer = DB::select('id')
            ->from('customer')
            ->orderBy('id', 'ASC')
            ->setMaxResults(1)
            ->fetchAssociative();

        if (!$customer) {
            return $this->json($response, [
                'status' => false,
                'msg' => 'Nenhum cliente encontrado!',
                'id' => 0
            ], 403);
        }

        $fields = [
            'id_cliente' => $customer['id'],
            'total_bruto' => 0,
            'total_liquido' => 0,
            'desconto' => 0,
            'acrescimo' => 0,
            'observacao' => ''
        ];

        try {
            DB::connection()->insert('sale', $fields);

            $id = DB::connection()->lastInsertId();

            return $this->json($response, [
                'status' => true,
                'msg' => 'Venda inserida com sucesso!',
                'id' => $id
            ], 201);

        } catch (\Exception $e) {
            return $this->json($response, [
                'status' => false,
                'msg' => $e->getMessage(),
                'id' => 0
            ], 500);
        }
    }

    public function update($request, $response)
    {
        $form = $request->getParsedBody();

        $id = $form['id'] ?? null;

        if (!$id) {
            return $this->json($response, [
                'status' => false,
                'msg' => 'Informe o ID da venda',
                'id' => 0
            ], 403);
        }

        $total = DB::select(
            'COALESCE(SUM(total_liquido),0) AS total_liquido,
             COALESCE(SUM(total_bruto),0) AS total_bruto'
        )
            ->from('item_sale')
            ->where('id_venda = :id')
            ->setParameter('id', $id)
            ->fetchAssociative();

        $fields = [
            'total_bruto' => $total['total_bruto'],
            'total_liquido' => $total['total_liquido']
        ];

        if (!empty($form['id_cliente'])) {
            $fields['id_cliente'] = $form['id_cliente'];
        }

        if (!empty($form['observacao'])) {
            $fields['observacao'] = $form['observacao'];
        }

        try {
            DB::connection()->update('sale', $fields, ['id' => $id]);

            return $this->json($response, [
                'status' => true,
                'msg' => 'Venda atualizada com sucesso!',
                'id' => $id
            ]);

        } catch (\Exception $e) {
            return $this->json($response, [
                'status' => false,
                'msg' => $e->getMessage(),
                'id' => 0
            ], 500);
        }
    }

    public function delete($request, $response)
    {
        $form = $request->getParsedBody();
        $id = $form['id'] ?? null;

        if (!$id) {
            return $this->json($response, [
                'status' => false,
                'msg' => 'Informe o ID da venda',
                'id' => 0
            ], 403);
        }

        try {
            DB::connection()->delete('sale', ['id' => $id]);

            return $this->json($response, [
                'status' => true,
                'msg' => 'Venda removida com sucesso!',
                'id' => $id
            ]);

        } catch (\Exception $e) {
            return $this->json($response, [
                'status' => false,
                'msg' => $e->getMessage(),
                'id' => 0
            ], 500);
        }
    }

    public function listingdata($request, $response)
    {
        $form = $request->getParsedBody();

        $term = $form['search']['value'] ?? null;
        $start = (int) ($form['start'] ?? 0);
        $length = (int) ($form['length'] ?? 10);

        $columns = [
            0 => 'id',
            1 => 'id_cliente',
            2 => 'total_bruto',
            3 => 'total_liquido',
            4 => 'criado_em',
            5 => 'atualizado_em',
        ];

        $posField = (int)($form['order'][0]['column'] ?? 0);
        $orderField = $columns[$posField] ?? 'id';

        $orderType = strtoupper($form['order'][0]['dir'] ?? 'DESC');
        $orderType = in_array($orderType, ['ASC', 'DESC']) ? $orderType : 'DESC';

        try {
            $totalRecords = (int) DB::select('COUNT(*)')
                ->from('sale')
                ->fetchOne();

            $query = DB::select('*')->from('sale');

            if (!empty($term)) {
                $query->setParameter('term', "%$term%");

                $query->where('CAST(id AS TEXT) ILIKE :term')
                    ->orWhere('CAST(id_cliente AS TEXT) ILIKE :term')
                    ->orWhere('CAST(total_bruto AS TEXT) ILIKE :term')
                    ->orWhere('CAST(total_liquido AS TEXT) ILIKE :term');
            }

            $filteredRecords = (int)(clone $query)
                ->select('COUNT(*)')
                ->fetchOne();

            $sales = $query
                ->orderBy($orderField, $orderType)
                ->setFirstResult($start)
                ->setMaxResults($length)
                ->fetchAllAssociative();

            $rows = [];

            foreach ($sales as $s) {
                $rows[] = [
                    $s['id'],
                    $s['id_cliente'],
                    number_format($s['total_bruto'], 2, ',', '.'),
                    number_format($s['total_liquido'], 2, ',', '.'),
                    $s['criado_em'],
                    $s['atualizado_em'],
                    "<td>
                        <a class='btn btn-sm btn-warning' href='/venda/detalhes/{$s['id']}'>Editar</a>
                        <button class='btn btn-sm btn-danger' onclick='ShowModal({$s['id']})'>Excluir</button>
                    </td>"
                ];
            }

            return $this->json($response, [
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $rows
            ]);

        } catch (\Exception $e) {
            return $this->json($response, [
                'status' => false,
                'msg' => $e->getMessage()
            ], 500);
        }
    }
}