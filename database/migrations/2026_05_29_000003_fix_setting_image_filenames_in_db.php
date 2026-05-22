<?php

use App\Support\SiteSettings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            SiteSettings::ORGANIZATION_LOGO,
            SiteSettings::UPI_QR,
        ] as $key) {
            $row = DB::table('settings')->where('key', $key)->first();
            if (! $row || ! $row->value) {
                continue;
            }

            $value = trim(str_replace('\\', '/', (string) $row->value));

            if (preg_match('#/storage/(.+)$#i', $value, $matches)) {
                $value = $matches[1];
            } elseif (str_starts_with($value, 'storage/')) {
                $value = substr($value, strlen('storage/'));
            }

            if (str_contains($value, '://')) {
                $path = parse_url($value, PHP_URL_PATH) ?: $value;
                if (preg_match('#/storage/(.+)$#i', $path, $matches)) {
                    $value = $matches[1];
                }
            }

            $basename = basename($value);

            if ($basename !== $row->value) {
                DB::table('settings')->where('key', $key)->update(['value' => $basename]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
