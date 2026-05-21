<?php

declare(strict_types=1);

use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

test('preRegister com dados válidos retorna 200 status true', function () {

    $request = (new RequestFactory())
        ->createRequest('POST', '/authentication/preregister')
        ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
        ->withParsedBody([
            'cad-nome' => 'Wilton',
            'cad-sobrenome' => 'Willl de Paulo',
            'cad-cpf' => '123.123.123-12',
            'cad-rg' => '123456',
            'cad-senha' => '1234',
            'cad-email' => 'wiltonwilldepaulo@gmail.com',
            'cad-telefone' => '69999060839'
        ]);

    $response = (new ResponseFactory())->createResponse();

    $result = (new app\controller\Login())->preRegister($request, $response);

    $result->getBody()->rewind();

    $json = json_decode($result->getBody()->getContents(), true);
    #Capturamos o código de resposta caso seja 201 significa que o cadastro 
    #Foi criado. 
    expect($result->getStatusCode())->toBe(201);

    expect($json['msg'])->toContain('Usuário cadastrado com sucesso!');

    expect($json['status'])->toBeTrue();
    
    expect($json['id'])->toBeGreaterThan(0);
});
