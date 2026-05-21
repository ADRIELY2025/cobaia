<?php

declare(strict_types=1);

use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

test('insert com dados válidos retorna 201 status true', function () {
    $request = (new RequestFactory())
        ->createRequest('POST', '/cliente/insert')
        ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
        ->withParsedBody([
            'nomeExibicao' => 'Belo',
            'nomeLegal' => 'Belo LTDA',
            'numeroDocumento' => '12.123.123/0001-12',
            'registroSecundario' => '123456',
            'dataRegistro' => '01/01/2000',
            'ativo' => 'true'
        ]);

    $response = (new ResponseFactory())->createResponse();

    $result = (new app\controller\Customer())->insert($request, $response);

    $result->getBody()->rewind();

    $json = json_decode($result->getBody()->getContents(), true);
    #Capturamos o código de resposta caso seja 201 significa que o cadastro 
    #Foi criado. 
    expect($result->getStatusCode())->toBe(201);

    expect($json['msg'])->toContain('Salvo com sucesso!');

    expect($json['status'])->toBeTrue();
});
