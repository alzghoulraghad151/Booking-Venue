<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Admin\AdminUserIndexRequest;
use App\Services\AdminUserListingService;

class AdminUserController extends Controller
{

    public function index(
        AdminUserIndexRequest $request,
        AdminUserListingService $listingService
    ) {
        $filters = array_merge([
            'search' => '',
            'role' => '',
            'status' => '',
            'sort' => 'created_at',
            'direction' => 'desc',
            'per_page' => '10',
        ], $request->validated());

        $users = $listingService->getUsers($filters);

        return view('admin.users.index', compact('users', 'filters'));
    }

}