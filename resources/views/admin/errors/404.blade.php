<?php
// Check for Admin Panel 404
if (request()->is(get_admin_prefix() . '*')) {
    if (view()->exists('admin.errors.admin_404')) {
        echo view('admin.errors.admin_404')->render();
        exit;
    }
}

// Global 404 Handler behaving as a proxy to theme 404
$theme = get_active_theme(); // Helper available
$themeView = "themes.{$theme}.404";

if (view()->exists($themeView)) {
    echo view($themeView)->render();
} else {
    // Fallback Default 404
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Not Found</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            text-align: center;
            padding: 50px;
            background: #f3f4f6;
        }

        h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #1f2937;
        }

        p {
            color: #4b5563;
        }

        a {
            color: #2563eb;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <h1>404</h1>
    <p>Page not found.</p>
    <a href="{{ url('/') }}">Go Home</a>
</body>

</html>
<?php
}
?>