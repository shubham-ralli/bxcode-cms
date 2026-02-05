<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/install', [App\Http\Controllers\InstallController::class, 'index'])->name('install.index');
Route::post('/install', [App\Http\Controllers\InstallController::class, 'store'])->name('install.store');

Route::prefix('lp-admin')->group(function () {
    Route::get('login', [App\Http\Controllers\AdminAuthController::class, 'login'])->name('login');
    Route::post('login', [App\Http\Controllers\AdminAuthController::class, 'authenticate']);
    Route::post('logout', [App\Http\Controllers\AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.dashboard');
        Route::get('/media', [App\Http\Controllers\MediaController::class, 'index'])->name('admin.media.index');
        Route::post('/media/upload', [App\Http\Controllers\MediaController::class, 'upload'])->name('admin.media.upload');
        Route::post('/media', [App\Http\Controllers\MediaController::class, 'store'])->name('admin.media.store');
        Route::delete('/media/bulk-delete', [App\Http\Controllers\MediaController::class, 'bulkDestroy'])->name('admin.media.bulk-delete');
        Route::put('/media/{id}', [App\Http\Controllers\MediaController::class, 'update'])->name('admin.media.update');
        Route::delete('/media/{id}', [App\Http\Controllers\MediaController::class, 'destroy'])->name('admin.media.destroy');

        Route::get('/posts', [App\Http\Controllers\PostController::class, 'index'])->name('admin.posts.index');
        Route::get('/posts/create', [App\Http\Controllers\PostController::class, 'create'])->name('admin.posts.create');
        Route::post('/posts', [App\Http\Controllers\PostController::class, 'store'])->name('admin.posts.store');
        Route::post('/posts/bulk', [App\Http\Controllers\PostController::class, 'bulkAction'])->name('admin.posts.bulk');
        Route::get('/posts/edit', [App\Http\Controllers\PostController::class, 'edit'])->name('admin.posts.edit');
        Route::post('/posts/{id}/restore', [App\Http\Controllers\PostController::class, 'restore'])->name('admin.posts.restore');
        Route::put('/posts/{id}', [App\Http\Controllers\PostController::class, 'update'])->name('admin.posts.update');
        Route::delete('/posts/{id}', [App\Http\Controllers\PostController::class, 'destroy'])->name('admin.posts.destroy');

        // Tags
        Route::get('tags/bulk', function () {
            return 'DEBUG: GET Method Detected. Form is submitting via GET or redirecting incorrectly.';
        });
        Route::post('tags/bulk', [App\Http\Controllers\TagController::class, 'bulkAction'])->name('admin.tags.bulk');
        // Custom Edit Route for WP-style URLs (tags/edit?tag_ID=x)
        Route::get('tags/edit', [App\Http\Controllers\TagController::class, 'edit'])->name('admin.tags.edit_custom');
        Route::resource('tags', App\Http\Controllers\TagController::class, ['as' => 'admin'])->except(['show']);

        // Route for pages redirect to posts for now, or filter
        Route::get('/pages', [App\Http\Controllers\PostController::class, 'index'])->name('admin.pages.index');

        // Settings Routes
        Route::get('/settings/general', [App\Http\Controllers\SettingsController::class, 'general'])->name('admin.settings.general');
        Route::get('/settings/reading', [App\Http\Controllers\SettingsController::class, 'reading'])->name('admin.settings.reading');
        Route::get('/settings/permalink', [App\Http\Controllers\SettingsController::class, 'permalink'])->name('admin.settings.permalink');
        Route::post('/settings/update', [App\Http\Controllers\SettingsController::class, 'update'])->name('admin.settings.update');

        // Theme Routes
        Route::get('/themes', [App\Http\Controllers\ThemeController::class, 'index'])->name('admin.themes.index');
        Route::post('/themes/activate', [App\Http\Controllers\ThemeController::class, 'activate'])->name('admin.themes.activate');

        // User Management Routes
        Route::post('users/bulk', [App\Http\Controllers\UserController::class, 'bulkAction'])->name('admin.users.bulk');
        Route::resource('users', App\Http\Controllers\UserController::class, ['as' => 'admin']);
        Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('admin.profile.edit');
        Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('admin.profile.update');
        // Plugins Management
        Route::get('plugins', [App\Http\Controllers\PluginController::class, 'index'])->name('admin.plugins.index');
        Route::post('plugins/bulk', [App\Http\Controllers\PluginController::class, 'bulk'])->name('admin.plugins.bulk');
        Route::get('plugins/create', [App\Http\Controllers\PluginController::class, 'create'])->name('admin.plugins.create');
        Route::post('plugins/upload', [App\Http\Controllers\PluginController::class, 'upload'])->name('admin.plugins.upload');
        Route::post('plugins/{slug}/activate', [App\Http\Controllers\PluginController::class, 'activate'])->name('admin.plugins.activate');
        Route::post('plugins/{slug}/deactivate', [App\Http\Controllers\PluginController::class, 'deactivate'])->name('admin.plugins.deactivate');
        Route::delete('plugins/{slug}', [App\Http\Controllers\PluginController::class, 'destroy'])->name('admin.plugins.destroy');

        // Appearance > Menus
        Route::get('menus', [App\Http\Controllers\MenuController::class, 'index'])->name('admin.menus.index');
        Route::get('menus/create', [App\Http\Controllers\MenuController::class, 'create'])->name('admin.menus.create');
        Route::post('menus', [App\Http\Controllers\MenuController::class, 'store'])->name('admin.menus.store');
        Route::get('menus/{id}/edit', [App\Http\Controllers\MenuController::class, 'edit'])->name('admin.menus.edit');
        Route::put('menus/{id}', [App\Http\Controllers\MenuController::class, 'update'])->name('admin.menus.update');
        Route::delete('menus/{id}', [App\Http\Controllers\MenuController::class, 'destroy'])->name('admin.menus.destroy');
        Route::post('menus/add-item', [App\Http\Controllers\MenuController::class, 'addItem'])->name('admin.menus.addItem');
        Route::post('menus/update-tree', [App\Http\Controllers\MenuController::class, 'updateTree'])->name('admin.menus.updateTree');
        Route::post('menus/item/{id}', [App\Http\Controllers\MenuController::class, 'updateItem'])->name('admin.menus.updateItem');
        Route::delete('menus/item/{id}', [App\Http\Controllers\MenuController::class, 'deleteItem'])->name('admin.menus.deleteItem');

        // Global Cache Clear
        Route::get('/cache/clear', function () {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            return back()->with('success', 'System Cache Cleared!');
        })->name('admin.cache.clear');

        // SEO Plugin Routes
        Route::get('/seo/settings', [App\Http\Controllers\SeoSettingsController::class, 'index'])->name('admin.seo.settings');
        Route::post('/seo/settings', [App\Http\Controllers\SeoSettingsController::class, 'update'])->name('admin.seo.settings.update');

    });
});

// Serve Theme Assets (Dynamic)
Route::get('/themes/{theme}/{file}', function ($theme, $file) {
    // Security check to prevent directory traversal
    if (str_contains($file, '..'))
        abort(404);

    $path = resource_path("views/themes/{$theme}/{$file}");

    if (file_exists($path)) {
        $headers = [];
        if (str_ends_with($file, '.css') || str_ends_with($file, '.min.css'))
            $headers['Content-Type'] = 'text/css';
        if (str_ends_with($file, '.js') || str_ends_with($file, '.min.js'))
            $headers['Content-Type'] = 'application/javascript';
        if (str_ends_with($file, '.png'))
            $headers['Content-Type'] = 'image/png';
        if (str_ends_with($file, '.jpg'))
            $headers['Content-Type'] = 'image/jpeg';
        if (str_ends_with($file, '.webp'))
            $headers['Content-Type'] = 'image/webp';

        return response()->file($path, $headers);
    }
    abort(404);
})->where('file', '.*')->name('theme.asset');



Route::get('/{slug?}', [App\Http\Controllers\FrontendController::class, 'handle'])->where('slug', '.*')->name('frontend.page');



