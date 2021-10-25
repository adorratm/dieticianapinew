<?php

namespace App\Http\Resources;

use App\Model\Panel\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogResource extends JsonResource
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
            'writer'=>$this->writable ? [
                'name'=>$this->writable->name,
                'id'=>$this->writable->_id,
                'role'=>$this->writable_type=='App\Model\Theme\User' ? 'Yönetici' : 'Diyetisyen',
            ] : '',
            'title'=>$this->title,
            'featureimage'=>url(Storage::url($this->featureimage)),
            'content'=>$this->content,
            'category'=>$this->category ? [
                'title'=>$this->category->title,
                'id'=>$this->category->_id,
                'description'=>$this->category->description,
            ] : '',
            'excerpt'=>Str::words($this->content,25,'...'),
            'status'=>$this->status,
            'slug'=>$this->slug,
            'created_at'=>$this->created_at->diffForHumans(),
            'updated_at'=>$this->updated_at->diffForHumans()
        ];
    }
}
