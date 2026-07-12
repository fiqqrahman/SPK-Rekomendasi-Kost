<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'Home::index');

$routes->get('login', 'Auth::login');
$routes->post('auth/attempt', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout');

$routes->group('owner', ['filter' => 'auth_satpam'], static function (RouteCollection $routes): void {

    // Mengakses URL: localhost/owner
    $routes->get('/', 'Adminkost::index');

    // Mengakses URL: localhost/owner/dashboard
    $routes->get('dashboard', 'Adminkost::index');

    // Jalur eksekusi simpan data (POST) -> localhost/owner/save
    $routes->post('save', 'Adminkost::save');

    // Jalur eksekusi saklar kamar (Flipping Bit) -> localhost/owner/toggle-status/1
    $routes->get('toggle-status/(:num)', 'Adminkost::toggleStatus/$1');
});
