<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $user = App\Models\User::where('email', 'miescuela.parents@gmail.com')->first();
    $req = Illuminate\Http\Request::create('/api/parent/dashboard', 'GET');
    $req->setUserResolver(function() use ($user) { return $user; });
    $controller = new App\Http\Controllers\Api\ParentDashboardController();
    $response = $controller->index($req);
    echo $response->getContent();
} catch (\Exception $e) {
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}
