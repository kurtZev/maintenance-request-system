<?php
// Base class for all controllers. Gives them view() to show a page.

class Controller
{
    /**
     * Show a view file from app/views.
     *
     * @param string      $view   Path inside app/views without ".php", e.g. 'admin/dashboard'
     * @param array       $data   Values for the view; each key becomes a variable
     * @param string|null $layout Layout in app/views/layouts to wrap the view in,
     *                            or null for views that are a complete page (login)
     */
    protected function view(string $view, array $data = [], ?string $layout = 'main'): void
    {
        $viewFile = APP_PATH . '/views/' . $view . '.php';
        if (!is_file($viewFile)) {
            throw new RuntimeException("View not found: $view");
        }

        extract($data, EXTR_SKIP);

        if ($layout === null) {
            require $viewFile;
        } else {
            // The layout prints the navbar and sidebar, then includes $viewFile.
            require APP_PATH . '/views/layouts/' . $layout . '.php';
        }
    }
}
