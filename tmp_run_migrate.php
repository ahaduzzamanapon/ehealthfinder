<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo \Illuminate\Support\Facades\Artisan::output();
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n\n";
    // Show only first 10 lines of trace
    $trace = explode("\n", $e->getTraceAsString());
    echo implode("\n", array_slice($trace, 0, 10));
}
