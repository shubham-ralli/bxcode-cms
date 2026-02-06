<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->is(get_admin_prefix() . '/*') || $request->is(get_admin_prefix())) {
                return response()->view('admin.errors.admin_404', [], 404);
            }

            // Frontend Theme 404
            if (function_exists('get_active_theme')) {
                $theme = get_active_theme();
                // Load theme functions just in case 404 view needs them
                if (function_exists('load_theme_functions')) {
                    load_theme_functions();
                }

                $view = "themes.{$theme}.404";
                if (view()->exists($view)) {
                    return response()->view($view, [], 404);
                }
            }
        });
    }
}
