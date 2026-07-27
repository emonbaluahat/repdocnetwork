<?php

use App\Core\Router;

$router = Router::getInstance();

// Auth (public routes with CSRF on POST)
$router->get('/', 'DashboardController@index');
$router->get('/shop/switch/{id}', 'DashboardController@switchShop');

$router->get('/logout', 'AuthController@logout');

$router->group(['middleware' => ['csrf']], function ($router) {
    $router->get('/login', 'AuthController@loginForm');
    $router->post('/login', 'AuthController@login');
    $router->get('/register', 'AuthController@registerForm');
    $router->post('/register', 'AuthController@register');
    $router->post('/logout', 'AuthController@logout');

    $router->get('/forgot-password', 'AuthController@forgotPasswordForm');
    $router->post('/forgot-password', 'AuthController@forgotPassword');
    $router->get('/reset-password/{token}', 'AuthController@resetPasswordForm');
    $router->post('/reset-password', 'AuthController@resetPassword');

    $router->post('/send-otp', 'AuthController@sendOtp');
    $router->get('/verify-otp', 'AuthController@verifyOtpForm');
    $router->post('/verify-otp', 'AuthController@verifyOtp');

    $router->get('/accept-invite', 'AuthController@acceptInvite');
});

// Profile (auth required, csrf on POST)
$router->group(['prefix' => 'profile', 'middleware' => ['auth', 'csrf']], function ($router) {
    $router->get('', 'ProfileController@index');
    $router->post('', 'ProfileController@update');
    $router->post('/password', 'ProfileController@changePassword');
    $router->post('/sessions/terminate', 'ProfileController@terminateSession');
    $router->post('/sessions/terminate-all', 'ProfileController@terminateAllSessions');
});

// Admin (Super Admin only)
$router->group(['prefix' => 'admin', 'middleware' => ['auth', 'superAdmin', 'csrf']], function ($router) {
    $router->get('/users', 'AdminController@users');
    $router->get('/users/{id}', 'AdminController@userDetail');
    $router->post('/users/{id}/toggle-status', 'AdminController@toggleStatus');
    $router->post('/users/{id}/change-role', 'AdminController@changeRole');
    $router->post('/users/{id}/reset-password', 'AdminController@resetPassword');
    $router->get('/permissions', 'AdminController@permissions');
    $router->post('/permissions/update', 'AdminController@updatePermissions');
    $router->get('/audit-logs', 'AdminController@auditLogs');
});

// Staff management (auth + shopScope + owner/admin, csrf on POST)
$router->group(['prefix' => 'staff', 'middleware' => ['auth', 'shopScope', 'csrf']], function ($router) {
    $router->get('', 'StaffController@index');
    $router->get('/invite', 'StaffController@inviteForm');
    $router->post('/invite', 'StaffController@invite');
    $router->post('/{id}/remove', 'StaffController@remove');
    $router->post('/{id}/role', 'StaffController@changeRole');
    $router->get('/permissions', 'StaffController@permissions');
    $router->post('/permissions/update', 'StaffController@updatePermissions');
});

// Customers
$router->group(['prefix' => 'customers', 'middleware' => ['auth', 'shopScope', 'csrf']], function ($router) {
    $router->get('', 'CustomerController@index');
    $router->get('/create', 'CustomerController@create');
    $router->post('', 'CustomerController@store');
    $router->get('/search', 'CustomerController@search');
    $router->get('/export', 'CustomerController@export');
    $router->post('/import', 'CustomerController@import');
    $router->get('/{id}', 'CustomerController@show');
    $router->get('/{id}/edit', 'CustomerController@edit');
    $router->post('/{id}', 'CustomerController@update');
    $router->post('/{id}/delete', 'CustomerController@destroy');
    $router->get('/{id}/timeline', 'CustomerController@timeline');
});

// Shop Settings
$router->group(['prefix' => 'settings', 'middleware' => ['auth', 'shopScope', 'csrf']], function ($router) {
    $router->get('', 'ShopSettingsController@index');
    $router->get('/shop', 'ShopSettingsController@index');
    $router->post('/shop', 'ShopSettingsController@update');
});

// Services
$router->group(['prefix' => 'services', 'middleware' => ['auth', 'shopScope', 'csrf']], function ($router) {
    $router->get('', 'ServiceController@index');
    $router->get('/create', 'ServiceController@create');
    $router->post('', 'ServiceController@store');
    $router->get('/categories', 'ServiceController@categories');
    $router->post('/categories', 'ServiceController@storeCategory');
    $router->post('/categories/{id}', 'ServiceController@updateCategory');
    $router->post('/categories/{id}/delete', 'ServiceController@destroyCategory');
    $router->get('/{id}', 'ServiceController@show');
    $router->get('/{id}/edit', 'ServiceController@edit');
    $router->post('/{id}', 'ServiceController@update');
    $router->post('/{id}/delete', 'ServiceController@destroy');
    $router->post('/{id}/toggle-status', 'ServiceController@toggleStatus');
});

// Orders
$router->group(['prefix' => 'orders', 'middleware' => ['auth', 'shopScope', 'csrf']], function ($router) {
    $router->get('', 'OrderController@index');
    $router->get('/create', 'OrderController@create');
    $router->post('', 'OrderController@store');
    $router->get('/search-customer', 'OrderController@searchCustomer');
    $router->get('/search-service', 'OrderController@searchService');
    $router->get('/{id}', 'OrderController@show');
    $router->get('/{id}/edit', 'OrderController@edit');
    $router->post('/{id}', 'OrderController@update');
    $router->post('/{id}/delete', 'OrderController@destroy');
    $router->post('/{id}/status', 'OrderController@updateStatus');
    $router->post('/{id}/payment', 'OrderController@addPayment');
    $router->get('/{id}/print', 'OrderController@printReceipt');
    $router->get('/{id}/timeline', 'OrderController@timeline');
});

// Transactions
$router->group(['prefix' => 'transactions', 'middleware' => ['auth', 'shopScope', 'csrf']], function ($router) {
    $router->get('', 'TransactionController@index');
    $router->post('', 'TransactionController@store');
    $router->get('/{id}', 'TransactionController@show');
    $router->post('/{id}/refund', 'TransactionController@refund');
    $router->get('/report', 'TransactionController@report');
});
