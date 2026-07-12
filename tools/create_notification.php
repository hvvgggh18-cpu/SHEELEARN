<?php

// Bootstrap the Laravel application and insert a test notification into the database.
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

$userId = getenv('NOTIF_USER_ID') ?: 1;
$id = (string) Str::uuid();

DB::table('notifications')->insert([
    'id' => $id,
    'type' => 'App\\Notifications\\GenericNotification',
    'notifiable_type' => 'App\\Models\\User',
    'notifiable_id' => (int) $userId,
    'data' => json_encode(['title' => 'Test Notification', 'body' => 'This is a test created by create_notification.php']),
    'read_at' => null,
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "Inserted notification $id for user $userId\n";
