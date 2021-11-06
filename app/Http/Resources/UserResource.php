<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $uploadPath = env('UPLOAD_PATH');
        $userInfo   = [
            'id'            => $this->id,
            'name'          => (string) $this->name,
            'user_name'     => (string) $this->user_name,
            'user_role'     => (int) $this->user_role,
            'email'         => (string) $this->email,
            'avtar'         => !empty($this->avatar) ? url($uploadPath . "avatar/" . $this->avatar) : "",
            'registered_at' => !empty($this->registered_at) ? date("Y-m-d", strtotime($this->registered_at)) : "",
        ];

        if (!empty($this->access_token)) {
            $userInfo['access_token'] = $this->access_token;
        }

        return $userInfo;
    }
}
