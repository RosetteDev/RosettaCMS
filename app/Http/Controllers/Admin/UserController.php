<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with('roles');
        
        // Count pending users
        $pendingUsersCount = User::whereNull('email_verified_at')->count();

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role_id') && $request->get('role_id')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('role_id', $request->get('role_id'));
            });
        }

        // Filter by status (active/inactive)
        if ($request->has('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('email_verified_at', '!=', null);
            } elseif ($status === 'inactive') {
                $query->where('email_verified_at', null);
            }
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $roles = Role::orderBy('name')->get();
        
        $settings = SiteSetting::forSite();

        return view('admin.users.index', compact('users', 'roles', 'pendingUsersCount', 'settings'));
    }

    /**
     * Display pending users (those without verified email).
     */
    public function pending(Request $request)
    {
        $query = User::with('roles')->where('email_verified_at', null);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.pending', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::orderBy('name')->get();
        
        return view('admin.users.form', [
            'action' => 'Create',
            'user' => new User(),
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign roles
        if (isset($validated['role_ids'])) {
            $user->roles()->sync($validated['role_ids']);
        } else {
            // Default to subscriber role if no roles selected
            $subscriberRole = Role::where('slug', 'subscriber')->first();
            if ($subscriberRole) {
                $user->roles()->attach($subscriberRole->id);
            }
        }

        return redirect()->route('admin.users.edit', $user->id)
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        
        return view('admin.users.form', [
            'action' => 'Edit',
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|confirmed|' . Password::defaults(),
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Update password only if provided
        if (isset($validated['password']) && !empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Update roles
        if (isset($validated['role_ids'])) {
            $user->roles()->sync($validated['role_ids']);
        }

        return redirect()->route('admin.users.edit', $user->id)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Verify/activate a user's email.
     */
    public function verify(User $user)
    {
        if (!$user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }

        return redirect()->back()
            ->with('success', 'User email verified successfully.');
    }

    /**
     * Deactivate a user.
     */
    public function deactivate(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['email_verified_at' => null]);

        return redirect()->back()
            ->with('success', 'User deactivated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account.');
        }

        // Don't delete the last admin
        $adminRole = Role::where('slug', 'administrator')->first();
        if ($adminRole && $user->roles()->where('role_id', $adminRole->id)->exists()) {
            $adminCount = User::whereHas('roles', function($q) use ($adminRole) {
                $q->where('role_id', $adminRole->id);
            })->count();

            if ($adminCount <= 1) {
                return redirect()->back()
                    ->with('error', 'You cannot delete the last administrator.');
            }
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Bulk actions for users.
     */
    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,verify,deactivate,add_role,remove_role',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $action = $validated['action'];
        $userIds = $validated['user_ids'] ?? [];
        $role = Role::find($validated['role_id'] ?? null);

        $userCount = 0;

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            // Skip self-actions
            if ($user->id === auth()->id()) continue;

            switch ($action) {
                case 'delete':
                    // Skip deleting the last admin
                    $adminRole = Role::where('slug', 'administrator')->first();
                    if ($adminRole && $user->roles()->where('role_id', $adminRole->id)->exists()) {
                        $adminCount = User::whereHas('roles', function($q) use ($adminRole) {
                            $q->where('role_id', $adminRole->id);
                        })->count();

                        if ($adminCount <= 1) {
                            continue 2;
                        }
                    }
                    $user->delete();
                    break;

                case 'verify':
                    if (!$user->email_verified_at) {
                        $user->update(['email_verified_at' => now()]);
                    }
                    break;

                case 'deactivate':
                    $user->update(['email_verified_at' => null]);
                    break;

                case 'add_role':
                    if ($role) {
                        $user->roles()->attach($role->id);
                    }
                    break;

                case 'remove_role':
                    if ($role) {
                        $user->roles()->detach($role->id);
                    }
                    break;
            }

            $userCount++;
        }

        return redirect()->back()
            ->with('success', "Bulk action completed on {$userCount} user(s).");
    }
}
