<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'DocumentController::index');
$routes->get('view/(:segment)', 'DocumentController::view/$1');

$routes->group('document', function($routes) {
    $routes->get('/', 'DocumentController::index');
    $routes->post('upload', 'DocumentController::upload');
    $routes->get('view/(:segment)', 'DocumentController::view/$1');
    $routes->get('get-rows/(:segment)', 'DocumentController::getRows/$1');
    $routes->post('add-row/(:segment)', 'DocumentController::addRow/$1');
    $routes->post('update-row/(:segment)/(:segment)', 'DocumentController::updateRow/$1/$2');
    $routes->post('delete-row/(:segment)/(:segment)', 'DocumentController::deleteRow/$1/$2');
    $routes->post('delete/(:segment)', 'DocumentController::delete/$1');
    $routes->get('export-excel/(:segment)', 'DocumentController::exportExcel/$1');
    $routes->get('export-pdf/(:segment)', 'DocumentController::exportPdf/$1');
    $routes->get('monthly-report', 'DocumentController::monthlyReport');
});
