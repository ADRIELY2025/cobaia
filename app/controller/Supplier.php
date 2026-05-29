<?php

declare(strict_types=1);

namespace App\Controller;

final class Supplier extends Base
{
    public function list($request, $response)
    {
        return $this->getTwig()
            ->render($response, $this->setView('list-supplier'), [
                'titulo' => 'Lista de fornecedores',
            ])
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function details($request, $response, $args)
    {
        $id = $args['id'] ?? null;
        $action = ($id === null) ? 'c' : 'e';
        $supplier = [];
        if (!is_null($id)) {
            $qb = \app\database\DB::select('*')->from('supplier');

            $supplier = $qb
                ->where('id = ' . $qb->createPositionalParameter($id, \Doctrine\DBAL\ParameterType::INTEGER))
                ->fetchAssociative();
        }
        return $this->getTwig()
            ->render($response, $this->setView('supplier'), [
                'titulo' => 'Detalhes do fornecedor ' . ($supplier['nome_fantasia'] ?? ''),
                'id' => $id,
                'action' => $action,
                'supplier' => $supplier
            ])
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function insert($request, $response)
    {
        $form = $request->getParsedBody();
        $FieldsAndValues = [
            'nome_fantasia' => $form['nomeExibicao'],
            'sobrenome_razao' => $form['nomeLegal'] ?? '',
            'cpf_cnpj' => $form['numeroDocumento'] ?? '',
            'rg_ie' => $form['registroSecundario'] ?? '',
            'nascimento_fundacao' => $this->convertBrDateToDatabaseFormat($form['dataRegistro']),
            'ativo' => ($form['ativo'] === 'true') ? true : false
        ];
        try {
            $IsInserted = \app\database\DB::connection()->insert('supplier', $FieldsAndValues);
            if (!$IsInserted) {
                return $this->json($response, ['status' => false, 'msg' => 'Restrição: ' . $IsInserted, 'id' => 0], 500);
            }
            $id = \app\database\DB::select('id')->from('supplier')->fetchAssociative();

            return $this->json($response, ['status' => true, 'msg' => 'Salvo com sucesso!', 'id' => $id['id']], 201);
        } catch (\Exception $e) {
            return $this->json($response, ['status' => false, 'msg' => 'Restrição: ' . $e->getMessage(), 'id' => 0], 500);
        }
    }
    public function update($request, $response)
    {
        $form = $request->getParsedBody();
        $id = $form['id'] ?? null;
        if (is_null($id)) {
            return $this->json($response, ['status' => false, 'msg' => 'Por favor informe o ID do registro', 'id' => 0], 403);
        }
        $FieldsAndValues = [
            'nome_fantasia' => $form['nomeExibicao'] ?? null,
            'sobrenome_razao' => $form['nomeLegal'] ?? null,
            'cpf_cnpj' => $form['numeroDocumento'] ?? null,
            'rg_ie' => $form['registroSecundario'] ?? null,
            'nascimento_fundacao' => $this->convertBrDateToDatabaseFormat($form['dataRegistro']),
            'ativo' => ($form['ativo'] === 'true') ? true : false
        ];
        try {
            $IsUpdated = \app\database\DB::connection()->update('supplier', $FieldsAndValues, ['id' => $id]);
            if (!$IsUpdated) {
                return $this->json($response, ['status' => false, 'msg' => 'Restrição: ' . $IsUpdated, 'id' => 0], 403);
            }
            return $this->json($response, ['status' => true, 'msg' => 'Alterado com sucesso!', 'id' => $id], 201);
        } catch (\Exception $e) {
            return $this->json($response, ['status' => false, 'msg' => 'Restrição: ' . $e->getMessage(), 'id' => 0], 500);
        }
    }
    public function delete($request, $response)
    {
        $form = $request->getParsedBody();
        $id = $form['id'] ?? null;
        if (is_null($id) || $id === '') {
            return $this->json($response, ['status' => false, 'msg' => 'Informe o código do fornecedor', 'id' => 0], 403);
        }
        try {
            $IsDeleted = \app\database\DB::connection()->delete('supplier', ['id' => $id]);
            if (!$IsDeleted) {
                return $this->json($response, ['status' => false, 'msg' => 'Restrição: ' . $IsDeleted, 'id' => $id], 403);
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
            1 => 'nome_fantasia',
            2 => 'cpf_cnpj',
            3 => 'inscricao_estadual',
            4 => 'nascimento_fundacao',
            5 => 'criado_em',
            6 => 'atualizado_em',
        ];

        $posField = (isset($form['order'][0]['column']) && isset($columns[(int) $form['order'][0]['column']]))
            ? (int) $form['order'][0]['column']
            : 0;

        # Validação da direção evita SQL injection no ORDER BY
        $orderType  = strtoupper($form['order'][0]['dir'] ?? 'DESC');
        $orderType  = in_array($orderType, ['ASC', 'DESC'], true) ? $orderType : 'DESC';
        $orderField = $columns[$posField];

        try {
            # Total geral DataTables: recordsTotal
            $totalRecords = (int) \app\database\DB::select('COUNT(*)')
                ->from('supplier')
                ->fetchOne();

            # Query principal com WHERE opcional
            $query = \app\database\DB::select('*')->from('supplier');

            if (!is_null($term) && $term !== '') {
                $query->setParameter('term', '%' . $term . '%');

                $query->where('CAST(id AS TEXT) ILIKE :term')
                    ->orWhere('nome_fantasia ILIKE :term')
                    ->orWhere('sobrenome_razao ILIKE :term')
                    ->orWhere('cpf_cnpj ILIKE :term')
                    ->orWhere('rg_ie ILIKE :term')
                    ->orWhere("TO_CHAR(nascimento_fundacao, 'DD/MM/YYYY') ILIKE :term")
                    ->orWhere("TO_CHAR(criado_em, 'DD/MM/YYYY HH24:MI:SS') ILIKE :term")
                    ->orWhere("TO_CHAR(atualizado_em, 'DD/MM/YYYY HH24:MI:SS') ILIKE :term");
            }

            # Total com filtro aplicado — clona o query e troca o SELECT por COUNT
            $filteredRecords = (int) (clone $query)
                ->select('COUNT(*)')
                ->fetchOne();

            # Resultados paginados e ordenados
            $suppliers = $query
                ->orderBy($orderField, $orderType)
                ->setFirstResult($start)
                ->setMaxResults($length)
                ->fetchAllAssociative();

            # Formatação para o DataTables
            $rows = [];
            foreach ($suppliers as $key => $value) {
                $cpfCnpj        = $value['cpf_cnpj']         ?? '';
                $nomeFantasia   = $value['nome_fantasia']    ?? '';
                $sobrenomeRazao = $value['sobrenome_razao']  ?? '';

                $nomeCompleto = (strlen($cpfCnpj) <= 14)
                    ? trim($nomeFantasia . ' ' . $sobrenomeRazao)
                    : $nomeFantasia;

                $rows[$key] = [
                    $value['id'],
                    $nomeCompleto,
                    $cpfCnpj,
                    (new \DateTime($value['nascimento_fundacao'] ?? date('Y-m-d')))->format('d/m/Y'),
                    ($value['ativo'] === true) ? 'Ativo' : 'Inativo',
                    (new \DateTime($value['criado_em']))->format('d/m/Y H:i:s'),
                    (new \DateTime($value['atualizado_em']))->format('d/m/Y H:i:s'),
                    "<td>
                    <a class='btn btn-sm btn-warning' href='/fornecedor/detalhes/" . $value['id'] . "'> <i class='fa-solid fa-pen-to-square'></i> Editar</a>
                    <button type='button' class='btn btn-sm btn-danger' onclick='ShowModal(" . $value['id'] . ");'> <i class='fa-solid fa-trash'></i> Excluir</button>
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
