<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class RatingDetailResource extends RatingResource
{
    public function toArray(Request $request): array
    {
        return [...parent::toArray($request), 'results' => $this->resource['results']];
    }
}
