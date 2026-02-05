<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "<pre>";
echo "<h1>CPT Helper Debug</h1>";

// 1. Check CPTs
echo "<h2>Active Custom Post Types</h2>";
$cpts = \Illuminate\Support\Facades\DB::table('custom_post_types')->where('active', 1)->get();
foreach ($cpts as $cpt) {
    echo "CPT Key: [{$cpt->key}] Name: {$cpt->plural_label} <br>";
}

// 2. Check Taxonomies
echo "<h2>Active Custom Taxonomies</h2>";
$taxes = \Plugins\ACF\src\Models\CustomTaxonomy::where('active', 1)->get();
foreach ($taxes as $tax) {
    echo "Taxonomy: [{$tax->key}] <br>";
    echo "Post Types (Raw): " . json_encode($tax->getAttributes()['post_types']) . "<br>";
    echo "Post Types (Cast): " . json_encode($tax->post_types) . "<br>";

    // Check match against CPTs
    foreach ($cpts as $cpt) {
        $inArray = in_array($cpt->key, $tax->post_types ?? []);
        echo " -> Match with '{$cpt->key}'? " . ($inArray ? "YES" : "NO") . "<br>";
    }
    echo "<hr>";
}
