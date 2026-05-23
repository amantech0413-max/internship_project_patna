<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CollegeRegistrationResource;
use App\Services\CollegeService;
use App\Services\SiteSettingService;
use Illuminate\Http\JsonResponse;

class PublicRegistrationController extends Controller
{
    public function __construct(
        protected CollegeService $colleges,
        protected SiteSettingService $settings
    ) {}

    public function settings(): JsonResponse
    {
        return $this->success($this->settings->publicPaymentDisplay());
    }

    public function privacyPolicy(): JsonResponse
    {
        return $this->success([
            'title' => 'Privacy Policy',
            'content_html' => $this->settings->privacyPolicyHtml(),
        ]);
    }

    public function colleges(): JsonResponse
    {
        return $this->success(
            CollegeRegistrationResource::collection($this->colleges->registrationColleges())
        );
    }

    public function collegeBySlug(string $slug): JsonResponse
    {
        $college = $this->colleges->findBySlug($slug);

        if (! $college) {
            return $this->error('College not found or registration is closed.', 404);
        }

        return $this->success(new CollegeRegistrationResource($college));
    }
}
