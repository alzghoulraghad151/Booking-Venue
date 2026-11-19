<?php

namespace App\Services;
use App\Models\User;

class AdminUserShowService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public function getUserDetails(User $user): User
    {
        return User::query()
            ->select([
                'id',
                'name',
                'email',
                'phone',
                'role',
                'status',
                'created_at',
            ])
            ->findOrFail($user->id);
    }
}
