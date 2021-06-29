<?php

use Jalno\Profile\Http\Controllers\UsersController;

/** @var \Laravel\Lumen\Routing\Router $router */

$router->group(['prefix' => '/userpanel', 'middleware' => 'auth'], function($router) {
    $router->get("/profile", array('uses' => UsersController::class . "@byUser"));
    $router->get("/profile/{id}", array('uses' => UsersController::class . "@findByID"));
    $router->post("/profile/{id}", array('uses' => UsersController::class . "@edit"));
    $router->delete("/profile/{id}", array('uses' => UsersController::class . "@delete"));
});
