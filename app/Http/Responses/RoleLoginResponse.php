<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse;

class RoleLoginResponse implements LoginResponse
{
    public function toResponse($request)
    {
        $home = $request->user()->admin_status
            ? '/admin/attendance/list'
            : config('fortify.home');

        return redirect($home);
    }
}
