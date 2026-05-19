<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

$rows = DB::table('artwork_sells')
    ->where('image_path', 'like', 'demo-artworks/%')
    ->get();

echo "Found {$rows->count()} records to fix.\n";

foreach ($rows as $row) {
    $newPath = 'artwork-sells/' . basename($row->image_path);
    if (!Storage::disk('public')->exists($newPath)) {
        Storage::disk('public')->copy($row->image_path, $newPath);
    }
    DB::table('artwork_sells')->where('id', $row->id)->update(['image_path' => $newPath]);
    echo "[ID {$row->id}] main → {$newPath}\n";

    $extras = json_decode($row->extra_images, true);
    if ($extras) {
        $newExtras = [];
        foreach ($extras as $ep) {
            if (str_starts_with($ep, 'demo-artworks/')) {
                $dest = 'artwork-sells/' . basename($ep);
                if (!Storage::disk('public')->exists($dest)) {
                    Storage::disk('public')->copy($ep, $dest);
                }
                $newExtras[] = $dest;
                echo "[ID {$row->id}] extra → {$dest}\n";
            } else {
                $newExtras[] = $ep;
            }
        }
        DB::table('artwork_sells')->where('id', $row->id)->update(['extra_images' => json_encode($newExtras)]);
    }
}

echo "Done!\n";