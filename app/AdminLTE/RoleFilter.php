<?php

namespace App\AdminLTE;

use JeroenNoten\LaravelAdminLTE\Menu\Filters\FilterInterface;
use Illuminate\Support\Facades\Auth;

class RoleFilter implements FilterInterface
{
    public function transform($item)
    {
        if (isset($item['roles']) && !Auth::user()->hasAnyRole($item['roles'])) {
            return false;
        }
        return $item;
    }
}
