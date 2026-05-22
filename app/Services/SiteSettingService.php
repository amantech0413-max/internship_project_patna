<?php

namespace App\Services;

use App\Models\Setting;
use App\Support\SiteSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SiteSettingService
{
    public const LOGO_DIR = 'images/logo';

    public const QR_DIR = 'images/payments';

    public function get(string $key, ?string $default = null): ?string
    {
        $row = Setting::query()->where('key', $key)->first();

        if ($row && $row->value !== null && $row->value !== '') {
            $value = $row->value;

            if ($key === SiteSettings::ORGANIZATION_LOGO) {
                return $this->normalizeFilenameForStorage($value, self::LOGO_DIR);
            }

            if ($key === SiteSettings::UPI_QR) {
                return $this->normalizeFilenameForStorage($value, self::QR_DIR);
            }

            return $value;
        }

        return $default ?? SiteSettings::DEFAULTS[$key] ?? null;
    }

    public function set(string $key, ?string $value): void
    {
        if (in_array($key, [SiteSettings::ORGANIZATION_LOGO, SiteSettings::UPI_QR], true)) {
            $value = $this->normalizeFilenameForStorage($value, $key === SiteSettings::ORGANIZATION_LOGO ? self::LOGO_DIR : self::QR_DIR);
        }

        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public function registrationFeeAmount(): float
    {
        return max(0, (float) $this->get(SiteSettings::REGISTRATION_FEE_AMOUNT, '0'));
    }

    /** @return array<string, mixed> */
    public function publicBranding(): array
    {
        return [
            'organization_name' => $this->get(SiteSettings::ORGANIZATION_NAME),
            'organization_address' => $this->get(SiteSettings::ORGANIZATION_ADDRESS),
            'organization_logo_url' => $this->logoUrl(),
            'registration_fee_amount' => $this->registrationFeeAmount(),
        ];
    }

    /** @return array<string, mixed> */
    public function publicPaymentDisplay(): array
    {
        return array_merge($this->publicBranding(), [
            'upi_id' => $this->get(SiteSettings::UPI_ID),
            'upi_qr_url' => $this->qrUrl(),
        ]);
    }

    /** @return array<string, mixed> */
    public function adminPayload(): array
    {
        $logoFile = $this->get(SiteSettings::ORGANIZATION_LOGO);
        $qrFile = $this->get(SiteSettings::UPI_QR);

        return [
            'organization_name' => $this->get(SiteSettings::ORGANIZATION_NAME),
            'organization_address' => $this->get(SiteSettings::ORGANIZATION_ADDRESS),
            'upi_id' => $this->get(SiteSettings::UPI_ID),
            'registration_fee_amount' => $this->registrationFeeAmount(),
            'organization_logo' => $logoFile,
            'organization_logo_url' => $this->logoUrl(),
            'upi_qr' => $qrFile,
            'upi_qr_url' => $this->qrUrl(),
        ];
    }

    /** @param  array<string, mixed>  $input */
    public function updateText(array $input): void
    {
        foreach ([
            SiteSettings::ORGANIZATION_NAME,
            SiteSettings::ORGANIZATION_ADDRESS,
            SiteSettings::UPI_ID,
            SiteSettings::REGISTRATION_FEE_AMOUNT,
        ] as $key) {
            if (array_key_exists($key, $input)) {
                $this->set($key, $input[$key] !== null ? trim((string) $input[$key]) : null);
            }
        }
    }

    public function storeLogo(?UploadedFile $file): ?string
    {
        if (! $file) {
            return null;
        }

        $this->removeLogo();
        $filename = $this->storeImage($file, self::LOGO_DIR);
        $this->set(SiteSettings::ORGANIZATION_LOGO, $filename);

        return $filename;
    }

    public function storeUpiQr(?UploadedFile $file): ?string
    {
        if (! $file) {
            return null;
        }

        $this->removeUpiQr();
        $filename = $this->storeImage($file, self::QR_DIR);
        $this->set(SiteSettings::UPI_QR, $filename);

        return $filename;
    }

    public function removeLogo(): void
    {
        $this->deleteStoredImage(SiteSettings::ORGANIZATION_LOGO, self::LOGO_DIR);
        $this->set(SiteSettings::ORGANIZATION_LOGO, null);
    }

    public function removeUpiQr(): void
    {
        $this->deleteStoredImage(SiteSettings::UPI_QR, self::QR_DIR);
        $this->set(SiteSettings::UPI_QR, null);
    }

    public function logoUrl(): ?string
    {
        return $this->imageUrl($this->get(SiteSettings::ORGANIZATION_LOGO), self::LOGO_DIR);
    }

    public function qrUrl(): ?string
    {
        return $this->imageUrl($this->get(SiteSettings::UPI_QR), self::QR_DIR);
    }

    protected function storeImage(UploadedFile $file, string $directory): string
    {
        $disk = Storage::disk('public');
        $disk->makeDirectory($directory);

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'png');
        $filename = Str::uuid()->toString().'.'.$extension;

        $file->storeAs($directory, $filename, 'public');

        return $filename;
    }

    protected function deleteStoredImage(string $settingKey, string $directory): void
    {
        $stored = $this->normalizeFilenameForStorage($this->get($settingKey), $directory);
        if (! $stored) {
            return;
        }

        $disk = Storage::disk('public');

        foreach ($this->resolveStoragePaths($stored, $directory) as $path) {
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }
    }

    protected function imageUrl(?string $stored, string $directory): ?string
    {
        $filename = $this->normalizeFilenameForStorage($stored, $directory);
        if (! $filename) {
            return null;
        }

        $disk = Storage::disk('public');

        foreach ($this->resolveStoragePaths($filename, $directory) as $path) {
            if ($disk->exists($path)) {
                return $this->publicStorageUrl($path);
            }
        }

        return null;
    }

    protected function normalizeFilenameForStorage(?string $stored, string $directory): ?string
    {
        if ($stored === null || $stored === '') {
            return null;
        }

        $stored = trim(str_replace('\\', '/', $stored));

        if (preg_match('#/storage/(.+)$#i', $stored, $matches)) {
            $stored = $matches[1];
        } elseif (str_starts_with($stored, 'storage/')) {
            $stored = substr($stored, strlen('storage/'));
        }

        if (str_contains($stored, '://')) {
            $path = parse_url($stored, PHP_URL_PATH) ?: $stored;
            if (preg_match('#/storage/(.+)$#i', $path, $matches)) {
                $stored = $matches[1];
            }
        }

        if (str_starts_with($stored, $directory.'/')) {
            return basename($stored);
        }

        if (str_contains($stored, '/')) {
            return basename($stored);
        }

        return $stored;
    }

    /** @return list<string> */
    protected function resolveStoragePaths(string $filename, string $directory): array
    {
        return [
            $directory.'/'.$filename,
            'settings/logo/'.$filename,
            'settings/upi-qr/'.$filename,
        ];
    }

    protected function publicStorageUrl(string $relativePath): string
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

        return url('storage/'.$relativePath);
    }
}
