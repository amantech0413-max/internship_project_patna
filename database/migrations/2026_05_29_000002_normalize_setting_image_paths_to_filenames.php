<?php

use App\Support\SiteSettings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        $disk = Storage::disk('public');

        $this->normalizeKey($disk, SiteSettings::ORGANIZATION_LOGO, 'images/logo', [
            'settings/logo',
        ]);

        $this->normalizeKey($disk, SiteSettings::UPI_QR, 'images/payments', [
            'settings/upi-qr',
        ]);
    }

    protected function normalizeKey($disk, string $key, string $targetDir, array $legacyDirs): void
    {
        $row = DB::table('settings')->where('key', $key)->first();
        if (! $row || ! $row->value) {
            return;
        }

        $value = (string) $row->value;

        if (! str_contains($value, '/')) {
            $disk->makeDirectory($targetDir);
            $target = $targetDir.'/'.$value;
            if (! $disk->exists($target)) {
                foreach ($legacyDirs as $legacyDir) {
                    $legacy = $legacyDir.'/'.$value;
                    if ($disk->exists($legacy)) {
                        File::ensureDirectoryExists($disk->path($targetDir));
                        File::move($disk->path($legacy), $disk->path($target));
                        break;
                    }
                }
            }

            return;
        }

        $basename = basename($value);
        $disk->makeDirectory($targetDir);
        $target = $targetDir.'/'.$basename;

        if ($disk->exists($value) && ! $disk->exists($target)) {
            File::ensureDirectoryExists($disk->path($targetDir));
            File::move($disk->path($value), $disk->path($target));
        }

        DB::table('settings')->where('key', $key)->update(['value' => $basename]);
    }

    public function down(): void
    {
        // irreversible path normalization
    }
};
