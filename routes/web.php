<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return "Download";
});

$router->post('/get', 'Token@get');
$router->get('/model', 'Token@model');
$router->post('/auth', 'Token@auth');
// $router->post('/register', 'Token@register');