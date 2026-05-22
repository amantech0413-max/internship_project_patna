<?php

namespace App\Http\Resources;

use App\Models\College;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollegeRegistrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var College $college */
        $college = $this->resource;

        return [
            'id' => $college->id,
            'slug' => $college->slug,
            'name' => $college->college_name,
            'short_name' => $college->registrationLabel(),
        ];
    }
}
