<?php
// All routes of the system: which URL runs which controller method.
//
//   $router->get(path, [Controller::class, 'method'], access)   a page
//   $router->post(path, [Controller::class, 'method'], access)  a form submission
//
// access: null = everyone, 'guest' = logged-out visitors only,
//         'auth' = any logged-in user, 'admin' / 'requester' = that role only.

// Home
$router->get('/', [DashboardController::class, 'index']);

// Authentication
$router->get('/login',   [AuthController::class, 'showLogin'], 'guest');
$router->post('/login',  [AuthController::class, 'login'],     'guest');
$router->get('/register',  [AuthController::class, 'showRegister'], 'guest');
$router->post('/register', [AuthController::class, 'register'],     'guest');
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/logout',  [DashboardController::class, 'index']);   // typed in the address bar: no effect

// Dashboards
$router->get('/admin/dashboard',     [DashboardController::class, 'admin'],     'admin');
$router->get('/requester/dashboard', [DashboardController::class, 'requester'], 'requester');

// Maintenance requests: admin (all requests)
$router->get('/admin/requests',          [MaintenanceRequestController::class, 'adminIndex'],   'admin');
$router->get('/admin/requests/view',     [MaintenanceRequestController::class, 'adminShow'],    'admin');
$router->get('/admin/requests/edit',     [MaintenanceRequestController::class, 'adminEdit'],    'admin');
$router->post('/admin/requests/edit',    [MaintenanceRequestController::class, 'adminUpdate'],  'admin');
$router->post('/admin/requests/status',  [MaintenanceRequestController::class, 'updateStatus'], 'admin');
$router->post('/admin/requests/delete',  [MaintenanceRequestController::class, 'destroy'],      'admin');

// Maintenance requests: requester (own requests only)
$router->get('/requester/requests',          [MaintenanceRequestController::class, 'requesterIndex'],  'requester');
$router->get('/requester/requests/create',   [MaintenanceRequestController::class, 'create'],          'requester');
$router->post('/requester/requests/create',  [MaintenanceRequestController::class, 'store'],           'requester');
$router->get('/requester/requests/view',     [MaintenanceRequestController::class, 'requesterShow'],   'requester');
$router->get('/requester/requests/edit',     [MaintenanceRequestController::class, 'requesterEdit'],   'requester');
$router->post('/requester/requests/edit',    [MaintenanceRequestController::class, 'requesterUpdate'], 'requester');
$router->post('/requester/requests/cancel',  [MaintenanceRequestController::class, 'cancel'],          'requester');

// User management (admin)
$router->get('/admin/users',          [UserController::class, 'index'],        'admin');
$router->get('/admin/users/create',   [UserController::class, 'create'],       'admin');
$router->post('/admin/users/create',  [UserController::class, 'store'],        'admin');
$router->get('/admin/users/edit',     [UserController::class, 'edit'],         'admin');
$router->post('/admin/users/edit',    [UserController::class, 'update'],       'admin');
$router->post('/admin/users/status',  [UserController::class, 'toggleStatus'], 'admin');
$router->post('/admin/users/delete',  [UserController::class, 'destroy'],      'admin');

// Reports (admin)
$router->get('/admin/reports',        [ReportController::class, 'index'],  'admin');
$router->get('/admin/reports/export', [ReportController::class, 'export'], 'admin');

// Request photos (the controller checks the viewer is an admin or the owner)
$router->get('/requests/photo', [MaintenanceRequestController::class, 'photo'], 'auth');
