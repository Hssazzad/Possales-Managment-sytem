<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Menu;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-users')->only(['index']);
        $this->middleware('permission:create-users')->only(['create', 'store']);
        $this->middleware('permission:edit-users')->only(['edit', 'update']);
        $this->middleware('permission:delete-users')->only(['destroy']);
        $this->middleware('permission:edit-users')->only(['assignRoles', 'getUserAccess', 'updateMenuAccess']);
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();
        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        } else {
            $user->roles()->detach();
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete your own account!');
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
            'roles' => $user->roles->pluck('id'),
            'menus' => $user->permissions()->where('name', 'like', 'access-%')->pluck('name')->map(function($perm) {
                return str_replace('access-', '', $perm);
            })->map(function($menuName) use ($user) {
                $menu = Menu::where('text', ucwords(str_replace('_', ' ', $menuName)))->first();
                return $menu ? $menu->id : null;
            })->filter()
        ]);
    }

    public function updateMenuAccess(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'menus' => 'nullable|array',
            'menus.*' => 'exists:menus,id',
        ]);

        $user = User::findOrFail($request->user_id);

        // Update roles
        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        } else {
            $user->roles()->detach();
        }

        // Update menu access by creating menu-specific permissions
        $menuPermissions = [];
        if ($request->has('menus')) {
            foreach ($request->menus as $menuId) {
                $menu = Menu::find($menuId);
                if ($menu) {
                    $permissionName = 'access-' . Str::slug($menu->text, '-');
                    $permission = Permission::firstOrCreate([
                        'name' => $permissionName,
                        'display_name' => 'Access ' . $menu->text,
                        'description' => 'Access to ' . $menu->text . ' menu'
                    ]);
                    $menuPermissions[] = $permission->id;
                }
            }
        }

        // Remove old menu permissions and add new ones
        $user->permissions()->where('name', 'like', 'access-%')->detach();
        if (!empty($menuPermissions)) {
            $user->permissions()->attach($menuPermissions);
        }

        return redirect()->route('users.assign-roles')->with('success', 'User access updated successfully!');
    }
}
