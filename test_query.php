<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $user = App\Models\User::where('email', 'miescuela.parents@gmail.com')->first();
    $children = App\Models\Student::with(['classroom', 'attendanceLogs' => function ($query) {
        $query->with(['kiosk', 'recordedBy'])->latest('scanned_at');
    }])->where('tutor_email', $user->email)->get();
    echo "Success!";
} catch (\Exception $e) {
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}
