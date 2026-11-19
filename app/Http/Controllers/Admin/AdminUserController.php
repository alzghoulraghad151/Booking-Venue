<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUserIndexRequest;
use App\Http\Requests\Admin\AdminUserStatusRequest;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    private const SAFE_USER_COLUMNS = [
        'id',
        'name',
        'email',
        'phone',
        'role',
        'status',
        'created_at',
    ];

    public function index(AdminUserIndexRequest $request): View
    {
        $filters = array_merge([
            'search' => '',
            'role' => '',
            'status' => '',
            'sort' => 'created_at',
            'direction' => 'desc',
            'per_page' => '10',
        ], array_filter(
            $request->validated(),
            static fn ($value) => $value !== null && $value !== ''
        ));

        $query = User::query()->select(self::SAFE_USER_COLUMNS);

        // Search by name, email, or phone.
        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by role.
        if ($filters['role'] !== '') {
            $query->where('role', $filters['role']);
        }

        // Filter by status.
        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        // Apply sorting.
        $allowedSorts = [
            'name',
            'email',
            'role',
            'status',
            'created_at',
        ];

        $sort = in_array($filters['sort'], $allowedSorts, true)
            ? $filters['sort']
            : 'created_at';

        $direction = in_array($filters['direction'], ['asc', 'desc'], true)
            ? $filters['direction']
            : 'desc';

        $query->orderBy($sort, $direction);

        $users = $query
            ->paginate((int) $filters['per_page'])
            ->withQueryString();

        return view('admin.users.index', compact('users', 'filters'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(StoreAdminUserRequest $request): RedirectResponse
    {
        $user = new User;
        $user->fill($request->safe()->only(['name', 'email', 'password']));
        $user->phone = $request->validated('phone');
        $user->role = $request->validated('role');
        $user->save();

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $user = User::query()
            ->select(self::SAFE_USER_COLUMNS)
            ->whereKey($user->getKey())
            ->firstOrFail();

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateAdminUserRequest $request, User $user): RedirectResponse
    {
        if (auth()->id() === $user->id && $request->validated('role') !== 'admin') {
            return back()
                ->withErrors(['role' => 'You cannot remove admin access from your own account.'])
                ->withInput();
        }

        $user->fill($request->safe()->only(['name', 'email']));
        $user->phone = $request->validated('phone');
        $user->role = $request->validated('role');

        if ($request->filled('password')) {
            $user->password = $request->validated('password');
        }

        $user->save();

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if(auth()->id() === $user->id, 403, 'You cannot delete your own account.');

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User moved to trash successfully.');
    }

    public function updateStatus(AdminUserStatusRequest $request, User $user): RedirectResponse
    {
        $status = $request->validated('status');

        abort_if(
            auth()->id() === $user->id && $status === 'suspended',
            403,
            'You cannot suspend your own account.'
        );

        $user->status = $status;
        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User status updated successfully.');
    }
}
