<?php

namespace App\Http\Resources\api\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CasherLoginResource extends JsonResource
{
    protected $token;

    public function __construct($resource, $token = null)
    {
        parent::__construct($resource);
        $this->token = $token;
    }

    public function toArray(Request $request): array
    {
        return [
            'token' => $this->when($this->token != null, $this->token),
            'name' => $this->name,
            'email'=>$this->email,
            'id' => $this->id
        ];
    }
}

