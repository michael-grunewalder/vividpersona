<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Plan;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\ValueObjects\UserSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * List and search users.
     */
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', ['users' => $users]);
    }

    /**
     * Show the form to create a new user.
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::query()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'meta' => new UserSettings(Plan::from($request->plan), (int) $request->max_teams),
        ]);

        $user->forceFill([
            'email_verified_at' => now(),
            'is_super_admin' => $request->boolean('is_super_admin'),
        ])->save();

        return redirect()->route('backend.user.index')->with('success', __('admin.users.created'));
    }

    /**
     * Show the form to edit an existing user.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', ['user' => $user]);
    }

    /**
     * Update an existing user.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'meta' => new UserSettings(Plan::from($request->plan), (int) $request->max_teams),
        ];

        if (filled($request->password)) {
            $data['password'] = $request->password;
        }

        $superAdmin = $request->boolean('is_super_admin');

        if ($superAdmin !== (bool) $user->is_super_admin) {
            $guardError = $this->guardSuperAdminChange($request, $user, $superAdmin);

            if ($guardError !== null) {
                return back()->withErrors(['is_super_admin' => $guardError]);
            }

            $data['is_super_admin'] = $superAdmin;
        }

        $user->forceFill($data)->save();

        return redirect()->route('backend.user.index')->with('success', __('admin.users.updated'));
    }

    /**
     * Delete an existing user.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->getKey() === $request->user()->getKey()) {
            return back()->withErrors(['user' => __('admin.users.cannot_delete_self')]);
        }

        if ($user->ownedTeams()->exists()) {
            return back()->withErrors(['user' => __('admin.users.cannot_delete_owns_teams')]);
        }

        $user->roles()->detach();
        $user->permissions()->detach();
        $user->delete();

        return redirect()->route('backend.user.index')->with('success', __('admin.users.deleted'));
    }

    /**
     * Guard revoking super admin access from yourself or the last super admin.
     */
    private function guardSuperAdminChange(Request $request, User $user, bool $toSuperAdmin): ?string
    {
        if ($toSuperAdmin) {
            return null;
        }

        if ($user->getKey() === $request->user()->getKey()) {
            return __('admin.users.cannot_revoke_self');
        }

        $hasAnother = User::query()
            ->where('is_super_admin', true)
            ->whereKeyNot($user->getKey())
            ->exists();

        if (! $hasAnother) {
            return __('admin.users.cannot_revoke_last');
        }

        return null;
    }
}
