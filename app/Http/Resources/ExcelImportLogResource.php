<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExcelImportLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'college_id' => $this->college_id,
            'file_name' => $this->file_name,
            'total_rows' => $this->total_rows,
            'success_count' => $this->success_count,
            'failed_count' => $this->failed_count,
            'skipped_count' => $this->skipped_count,
            'errors' => $this->errors ?? [],
            'college' => $this->whenLoaded('college', fn () => [
                'id' => $this->college->id,
                'college_name' => $this->college->college_name,
            ]),
            'importer' => $this->whenLoaded('importer', fn () => [
                'id' => $this->importer->id,
                'name' => $this->importer->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
