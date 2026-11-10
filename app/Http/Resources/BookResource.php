<?php

namespace App\Http\Resources;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Book */
class BookResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'description' => $this->description,
            'published_year' => $this->published_year,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn () => CategoryResource::make($this->category)->resolve($request)),
        ];
    }
}
