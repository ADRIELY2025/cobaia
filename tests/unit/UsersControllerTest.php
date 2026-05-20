<?php

declare(strict_types=1);

use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

test('insert com dados válidos retorna 200 status true', function () {

    $request = (new RequestFactory())
        ->createRequest('POST', '/usuario/insert')
        ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
        ->withParsedBody([
            'nome' => 'John',
            'sobrenome' => 'Doe',
            'cpf' => '123.456.789-00',
            'rg' => '12.345.678-9',
            'senha' => 'password',
            'administrador' => 'false',
            'ativo' => 'true'
        ]);

    $response = (new ResponseFactory())->createResponse();

    $result = (new app\controller\Users())->insert($request, $response);

    $result->getBody()->rewind();([
            'nome' => 'John',
            'sobrenome' => 'Doe',
            'cpf' => '123.456.789-00',
            'rg' => '12.345.678-9',
            'senha' => 'password',
            'administrador' => 'false',
            'ativo' => 'true'
        ]);

    $response = (new ResponseFactory())->createResponse();

    $result = (new app\controller\Users())->insert($request, $response);

    $result->getBody()->rewind();

    $json = json_decode($result->getBody()->getContents(), true);
    #Capturamos o código de resposta caso seja 201 significa que o cadastro 
    #Foi criado. 
    expect($result->getStatusCode())->toBe(201);

    expect($json['msg'])->toContain('Restrição:');

    expect($json['status'])->toBeTrue();
});
