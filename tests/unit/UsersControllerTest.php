<?php

declare(strict_types=1);

use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;


test('insert com dados válidos retorna 201 status true', function () {
    $request = (new RequestFactory())
        ->createRequest('POST', '/usuario/insert')
        ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
        ->withParsedBody([
            'nomeExibicao'       => 'John',
            'nomeLegal'          => 'Doe',
            'numeroDocumento'    => '123.456.789-00',
            'registroSecundario' => '12.345.678-9',
            'senha'              => 'password',
            'administrador'      => 'false',
            'ativo'              => 'true',
        ]);

    $response = (new ResponseFactory())->createResponse();
    $result   = (new app\controller\Users())->insert($request, $response);
    $result->getBody()->rewind();
    $json = json_decode($result->getBody()->getContents(), true);

    expect($result->getStatusCode())->toBe(201);
    expect($json['status'])->toBeTrue();
    expect($json['msg'])->toContain('Salvo com sucesso!');
    expect($json['id'])->toBeGreaterThan(0); // garante que retornou um ID válido
});

test('update com dados válidos retorna 200 status true', function () {
    # Busca o último ID inserido para não depender de id fixo
    $id = \app\database\DB::select('id')
        ->from('users')
        ->orderBy('id', 'DESC')
        ->fetchOne();

    $request = (new RequestFactory())
        ->createRequest('POST', '/usuario/update')
        ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
        ->withParsedBody([
            'id'                 => (string) $id,
            'nomeExibicao'       => 'John Atualizado',
            'nomeLegal'          => 'Doe Atualizado',
            'numeroDocumento'    => '123.456.789-00',
            'registroSecundario' => '12.345.678-9',
            'senha'              => 'password',
            'administrador'      => 'false',
            'ativo'              => 'true',
        ]);

    $response = (new ResponseFactory())->createResponse();
    $result   = (new app\controller\Users())->update($request, $response);
    $result->getBody()->rewind();
    $json = json_decode($result->getBody()->getContents(), true);

    expect($result->getStatusCode())->toBe(200);
    expect($json['status'])->toBeTrue();
    expect($json['msg'])->toContain('Alterado com sucesso!');
});

test('delete com id válido retorna 200 status true', function () {
    # Busca o último ID inserido para não depender de id fixo
    $id = \app\database\DB::select('id')
        ->from('users')
        ->orderBy('id', 'DESC')
        ->fetchOne();

    $request = (new RequestFactory())
        ->createRequest('POST', '/usuario/delete')
        ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
        ->withParsedBody([
            'id' => (string) $id,
        ]);

    $response = (new ResponseFactory())->createResponse();
    $result   = (new app\controller\Users())->delete($request, $response);
    $result->getBody()->rewind();
    $json = json_decode($result->getBody()->getContents(), true);

    expect($result->getStatusCode())->toBe(200);
    expect($json['status'])->toBeTrue();
    expect($json['msg'])->toContain('Removido com sucesso!');
});

test('listingdata retorna 200 com estrutura correta do DataTables', function () {
    $request = (new RequestFactory())
        ->createRequest('POST', '/usuario/listingdata')
        ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
        ->withParsedBody([
            'draw'   => '1',
            'start'  => '0',
            'length' => '10',
            'search' => ['value' => ''],
            'order'  => [['column' => '0', 'dir' => 'DESC']],
        ]);

    $response = (new ResponseFactory())->createResponse();
    $result   = (new app\controller\Users())->listingdata($request, $response);
    $result->getBody()->rewind();
    $json = json_decode($result->getBody()->getContents(), true);

    expect($result->getStatusCode())->toBe(200);
    expect($json)->toHaveKeys(['recordsTotal', 'recordsFiltered', 'data']);
    expect($json['data'])->toBeArray();
});