<?php
// Page frame for every logged-in page. Controller::view() loads this file
// and sets $viewFile to the page's own view, which is printed in the middle.
// Variables from the controller: $pageTitle, $activePage.
$pageTitle  = $pageTitle ?? APP_NAME;
$activePage = $activePage ?? '';

require __DIR__ . '/header.php';
require $viewFile;
require __DIR__ . '/footer.php';
