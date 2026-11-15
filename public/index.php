<?php
// Front controller: every page request starts here.
// 1. bootstrap.php sets up the application.
// 2. routes/web.php lists which URL runs which controller method.
// 3. The router runs the matching one.

require __DIR__ . '/../bootstrap.php';
require ROOT_PATH . '/routes/web.php';

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
