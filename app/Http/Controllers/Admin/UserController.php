<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Menu;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:users.view')->only(['index', 'show']);
        $this->middleware('permission:users.create')->only(['create', 'store']);
        $this->middleware('permission:users.edit')->only(['edit', 'update', 'assignRoles', 'getUserAccess', 'updateMenuAccess']);
        $this->middleware('permission:users.delete')->only(['destroy']);
    }

    public function index()
    {
        $users = User::with('roles')->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles'    => 'nullable|array',
            'roles.*'  => 'exists:roles,id',
        ]);

        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'is_super_admin' => 0, // ✅ নতুন user কখনো Super Admin না
        ]);

        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    public function show(User $user)
    {
        $user->load('roles');
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        // ✅ Super Admin কে শুধু Super Admin edit করতে পারবে
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return back()->with('error', 'Super Admin কে edit করার permission নেই!');
        }

        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();
        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    public function update(Request $request, User $user)
    {
        // ✅ Super Admin কে শুধু Super Admin update করতে পারবে
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return back()->with('error', 'Super Admin কে edit করার permission নেই!');
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles'    => 'nullable|array',
            'roles.*'  => 'exists:roles,id',
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        // ✅ শুধু Super Admin অন্যকে Super Admin বানাতে পারবে
        if (auth()->user()->isSuperAdmin()) {
            $data['is_super_admin'] = $request->boolean('is_super_admin');
        }

        $user->update($data);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // ✅ Super Admin এর role change হবে না
        if (!$user->isSuperAdmin()) {
            $user->roles()->sync($request->roles ?? []);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        // ✅ নিজেকে delete করা যাবে না
        if ($user->id === auth()->id()) {
            return back()->with('error', 'নিজের account delete করা যাবে না!');
        }

        // ✅ Super Admin কে delete করা যাবে না
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Super Admin কে delete করা যাবে না!');
        }

        $user->roles()->detach();
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }

    public function assignRoles()
    {
        $users = User::with('roles')->get();
        $roles = Role::all();
        $menus = Menu::whereNull('parent_id')->with('children')->orderBy('sort_order')->get();

        return view('admin.users.assign-roles', compact('users', 'roles', 'menus'));
    }

    public function getUserAccess(User $user)
    {
        return response()->json([
            'roles'          => $user->roles->pluck('id'),
            'is_super_admin' => $user->isSuperAdmin(),
        ]);
    }

    public function updateMenuAccess(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'roles'   => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::findOrFail($request->user_id);

        // ✅ Super Admin এর role change করা যাবে না
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Super Admin এর role change করা যাবে না!');
        }

        $user->roles()->sync($request->roles ?? []);

        return redirect()->route('users.assign-roles')->with('success', 'User access updated successfully!');
    }
}