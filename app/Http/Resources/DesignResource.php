<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DesignResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [

            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'images' => $this->images,
            'is_live' => $this->is_live,
            'description' => $this->description,
            'like_count' => $this->likes()->count(),
            'tag_list' => [
               'tags' => $this->tagArray,
               'normalized' => $this->tagArrayNormalized
            ],
            'team' => $this->team ? [

                'name' => $this->team->name,
                'slug' => $this->team->slug
            ] : null,
            'created_at_dates' => [

                'created_at_human' => $this->created_at->diffForHumans(),
                'created_at' => $this->created_at
            ],
            'updated_at_dates' => [

                'updated_at_human' => $this->updated_at->diffForHumans(),
                'updated_at' => $this->updated_at
            ],
            'comments' => CommentResource::collection($this->comments),
            'user' => new UserResource($this->user),
        ];
    }
}
