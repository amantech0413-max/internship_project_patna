<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Controller;
use App\Services\SiteSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteSettingController extends Controller
{
    public function __construct(protected SiteSettingService $settings) {}

    public function show(): JsonResponse
    {
        return $this->success($this->settings->adminPayload());
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'organization_name' => ['nullable', 'string', 'max:255'],
            'organization_address' => ['nullable', 'string', 'max:500'],
            'upi_id' => ['nullable', 'string', 'max:100'],
            'registration_fee_amount' => ['nullable', 'numeric', 'min:0'],
            'organization_logo' => ['nullable', 'image', 'max:2048'],
            'upi_qr' => ['nullable', 'image', 'max:2048'],
            'remove_logo' => ['sometimes', 'boolean'],
            'remove_upi_qr' => ['sometimes', 'boolean'],
            'privacy_policy_html' => ['nullable', 'string', 'max:65535'],
            'support_contact_number' => ['nullable', 'string', 'max:30'],
            'support_email' => ['nullable', 'email', 'max:255'],
        ]);

        $this->settings->updateText($request->only([
            'organization_name',
            'organization_address',
            'upi_id',
            'registration_fee_amount',
            'privacy_policy_html',
            'support_contact_number',
            'support_email',
        ]));

        if ($request->boolean('remove_logo')) {
            $this->settings->removeLogo();
        } elseif ($request->hasFile('organization_logo')) {
            $this->settings->storeLogo($request->file('organization_logo'));
        }

        if ($request->boolean('remove_upi_qr')) {
            $this->settings->removeUpiQr();
        } elseif ($request->hasFile('upi_qr')) {
            $this->settings->storeUpiQr($request->file('upi_qr'));
        }

        return $this->success($this->settings->adminPayload(), 'Settings saved.');
    }
}
