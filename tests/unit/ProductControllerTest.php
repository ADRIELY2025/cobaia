<?php

declare(strict_types=1);

use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

test('insert com dados válidos retorna 201 status true', function () {
    $request = (new RequestFactory())
        ->createRequest('POST', '/produto/insert')
        ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
        ->withParsedBody([
            'nome'                 => 'Banana',
            'codigoBarra'          => '1234567890123',
            'grupo'                => 'Frutas',
            'unidade'              => 'kg',
            'precoCompra'          => '2.5000',
            'totalImposto'         => '0.3000',
            'margemLucro'          => '0.4000',
            'custoOperacional'     => '0.1000',
            'valorVendaSugerido'   => '3.5000',
            'precoVenda'           => '3.2000',
            'descricao'            => 'Descrição do produto',
            'ativo'                => 'true',
        ]);

    $response = (new ResponseFactory())->createResponse();

    $result = (new app\controller\Product())->insert($request, $response);

    $result->getBody()->rewind();

    $json = json_decode($result->getBody()->getContents(), true);

    expect($result->getStatusCode())->toBe(201);

    expect($json['status'])->toBeTrue();
    expect($json['id'])->toBeGreaterThan(0);
    expect($json['msg'])->toContain('Salvo com sucesso!');
});