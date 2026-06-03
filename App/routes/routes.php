<?php

declare(strict_types=1);

$app->get('/', App\Controller\Home::class . ':home');#->add(App\Middleware\Middleware::web());
$app->get('/home', App\Controller\Home::class . ':home');#->add(App\Middleware\Middleware::web());
#$app->get('/login', App\Controller\Login::class . ':login')#->add(App\Middleware\Middleware::web());

$app->group('/authentication', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/logout', App\Controller\Login::class . ':logout');
    $group->post('/google', App\Controller\Login::class . ':google');
    $group->post('/auth', App\Controller\Login::class . ':authenticate');
    $group->post('/preregister', App\Controller\Login::class . ':preRegister');
});

$app->group('/cliente', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/lista', App\Controller\Customer::class . ':list');
    $group->get('/detalhes/{id}', App\Controller\Customer::class . ':details');
    $group->get('/detalhes', App\Controller\Customer::class . ':details');
    $group->post('/insert', App\Controller\Customer::class . ':insert');
    $group->post('/update', App\Controller\Customer::class . ':update');
    $group->post('/delete', App\Controller\Customer::class . ':delete');
    $group->post('/listingdata', App\Controller\Customer::class . ':listingdata');
});
$app->group('/empresa', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/lista', App\Controller\Enterprise::class . ':list');
    $group->get('/detalhes/{id}', App\Controller\Enterprise::class . ':details');
    $group->get('/detalhes', App\Controller\Enterprise::class . ':details');
    $group->post('/insert', App\Controller\Enterprise::class . ':insert');
    $group->post('/update', App\Controller\Enterprise::class . ':update');
    $group->post('/delete', App\Controller\Enterprise::class . ':delete');
    $group->post('/listingdata', App\Controller\Enterprise::class . ':listingdata');
});
$app->group('/fornecedor', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/lista', App\Controller\Supplier::class . ':list');
    $group->get('/detalhes/{id}', App\Controller\Supplier::class . ':details');
    $group->get('/detalhes', App\Controller\Supplier::class . ':details');
    $group->post('/insert', App\Controller\Supplier::class . ':insert');
    $group->post('/update', App\Controller\Supplier::class . ':update');
    $group->post('/delete', App\Controller\Supplier::class . ':delete');
    $group->post('/listingdata', App\Controller\Supplier::class . ':listingdata');
});
$app->group('/usuario', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/lista', App\Controller\Users::class . ':list');
    $group->get('/detalhes/{id}', App\Controller\Users::class . ':details');
    $group->get('/detalhes', App\Controller\Users::class . ':details');
    $group->post('/insert', App\Controller\Users::class . ':insert');
    $group->post('/update', App\Controller\Users::class . ':update');
    $group->post('/delete', App\Controller\Users::class . ':delete');
    $group->post('/listingdata', App\Controller\Users::class . ':listingdata');
});
$app->group('/produto', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/lista', App\Controller\Product::class . ':list');
    $group->get('/detalhes/{id}', App\Controller\Product::class . ':details');
    $group->get('/detalhes', App\Controller\Product::class . ':details');
    $group->post('/insert', App\Controller\Product::class . ':insert');
    $group->post('/update', App\Controller\Product::class . ':update');
    $group->post('/delete', App\Controller\Product::class . ':delete');
    $group->post('/listingdata', App\Controller\Product::class . ':listingdata');
});
