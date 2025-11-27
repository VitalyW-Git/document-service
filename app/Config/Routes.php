<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Document::index');
$routes->get('view/(:segment)', 'Document::view/$1');

$routes->group('document', function($routes) {
    $routes->get('/', 'Document::index');
    $routes->post('upload', 'Document::upload');
    $routes->get('view/(:segment)', 'Document::view/$1');
    $routes->get('get-rows/(:segment)', 'Document::getRows/$1');
    $routes->post('add-row/(:segment)', 'Document::addRow/$1');
    $routes->post('update-row/(:segment)/(:segment)', 'Document::updateRow/$1/$2');
    $routes->post('delete-row/(:segment)/(:segment)', 'Document::deleteRow/$1/$2');
    $routes->post('delete/(:segment)', 'Document::delete/$1');
    $routes->get('export-excel/(:segment)', 'Document::exportExcel/$1');
    $routes->get('export-pdf/(:segment)', 'Document::exportPdf/$1');
    $routes->get('monthly-report', 'Document::monthlyReport');
});
