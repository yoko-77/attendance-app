<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse;

class RoleLogoutResponse implements LogoutResponse
{
    public function toResponse($request)
    {
        $loginPage = $request->is('admin/logout')
            ? '/admin/login'
            : '/login';

        return redirect($loginPage);
    }
}
