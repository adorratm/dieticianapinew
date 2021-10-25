<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogCategoryResource extends JsonResource
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
            'id'=>$this->_id,
            'slug'=>$this->slug,
            'featureimage'=>$this->featureimage,
            'post_count'=>$this->posts->count(),
            'title'=>$this->title,
            'description'=>$this->description,
            'posts'=>$this->whenLoaded('posts',BlogResource::collection($this->posts)),
            'created_at'=>$this->created_at->diffForHumans(),
            'updated_at'=>$this->updated_at->diffForHumans()
        ];
    }
}
