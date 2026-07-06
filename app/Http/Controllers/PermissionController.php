<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->sendSuccess(
            Permission::orderBy('id')->get(['id', 'name', 'label'])
        );
    }
}
