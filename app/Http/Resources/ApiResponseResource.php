<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiResponseResource extends JsonResource
{
    protected bool $success;
    protected mixed $data;
    protected ?array $error;
    protected ?array $meta;

    public function __construct($resource, bool $success = true, ?array $error = null, ?array $meta = null)
    {
        parent::__construct($resource);
        $this->success = $success;
        $this->error = $error;
        $this->meta = $meta;
    }

    public function toArray(Request $request): array
    {
        return [
            'success' => $this->success,
            'data' => $this->resource,
            'error' => $this->error,
            'meta' => $this->meta,
        ];
    }
}
