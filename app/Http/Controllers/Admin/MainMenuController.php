<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;

class MainMenuController extends Controller
{
    /**
     * Show Main Menu management page.
     * Main Menu = parent_id NULL records.
     * Settings = submenu (child) under a Main Menu.
     */
    public function index()
    {
        $mainMenus = Menu::whereNull('parent_id')
            ->orderBy('sort_order')
            ->with(['children' => fn ($q) => $q->orderBy('sort_order')])
            ->get();

        // For parent dropdown (Main Menu select)
        $parents = $mainMenus;

        return view('admin.mainmenu.index', compact('mainMenus', 'parents'));
    }

    /**
     * Store new Main Menu OR Settings/submenu under a Main Menu.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'text'      => 'required|string|max:100',
            'code'      => 'nullable|string|max:20|unique:menus,code',
            'url'       => 'nullable|string|max:255',
            'icon'      => 'required|string|max:100',
            'controller_name' => 'nullable|string|max:255',
            'main_route' => 'nullable|string|max:255|unique:menus,main_route',
            'parent_id' => 'nullable|exists:menus,id',
            'sort_order'=> 'nullable|integer|min:1',
        ]);

        $data['sort_order'] ??= (Menu::where('parent_id', $data['parent_id'] ?? null)->max('sort_order') ?? 0) + 1;
        $data['url'] = $data['url'] ?: '#';

        if (empty($data['code'])) {
            $data['code'] = Menu::generateCode($data['parent_id'] ?? null);
        }
        $data['is_active'] = $request->has('is_active') ? true : true;

        // If parent_id given, ensure parent is a Main Menu (top level).
        // If someone passes a child id, lift it to its top-level parent.
        if (! empty($data['parent_id'])) {
            $parent = Menu::find($data['parent_id']);
            if ($parent && $parent->parent_id) {
                $data['parent_id'] = $parent->parent_id;
            }
        }

        Menu::create($data);

        return redirect()->route('mainmenu.index')
            ->with('success', 'Menu added successfully!');
    }

    /**
     * Quick add "Settings" submenu under selected Main Menu.
     * If Settings already exists under that Main Menu, just reactivate it.
     */
    public function storeSettings(Request $request)
    {
        $data = $request->validate([
            'parent_id' => 'required|exists:menus,id',
            'url'       => 'nullable|string|max:255',
            'icon'      => 'nullable|string|max:100',
        ]);

        $parent = Menu::findOrFail($data['parent_id']);
        // Ensure top-level parent
        if ($parent->parent_id) {
            $parent = Menu::findOrFail($parent->parent_id);
        }

        $existing = Menu::where('parent_id', $parent->id)
            ->where('text', 'Settings')
            ->first();

        if ($existing) {
            $existing->update([
                'is_active' => true,
                'url'  => $data['url'] ?? $existing->url,
                'icon' => $data['icon'] ?? $existing->icon,
            ]);

            return redirect()->route('mainmenu.index')
                ->with('success', "Settings already exists under '{$parent->text}' — reactivated.");
        }

        Menu::create([
            'text'       => 'Settings',
            'code'       => Menu::generateCode($parent->id),
            'url'        => $data['url'] ?? '#',
            'icon'       => $data['icon'] ?? 'fas fa-fw fa-cog',
            'parent_id'  => $parent->id,
            'sort_order' => (Menu::where('parent_id', $parent->id)->max('sort_order') ?? 0) + 1,
            'is_active'  => true,
        ]);

        return redirect()->route('mainmenu.index')
            ->with('success', "Settings added under '{$parent->text}' successfully!");
    }

    public function update(Request $request, Menu $mainmenu)
    {
        $data = $request->validate([
            'text'      => 'required|string|max:100',
            'url'       => 'required|string|max:255',
            'icon'      => 'required|string|max:100',
            'controller_name' => 'nullable|string|max:255',
            'main_route' => 'nullable|string|max:255|unique:menus,main_route,' . $mainmenu->id,
            'parent_id' => 'nullable|exists:menus,id',
            'sort_order'=> 'required|integer|min:1',
        ]);

        if (! empty($data['parent_id']) && $data['parent_id'] == $mainmenu->id) {
            return back()->withErrors(['parent_id' => 'A menu cannot be its own parent.']);
        }

        $data['is_active'] = $request->has('is_active') ? true : $mainmenu->is_active;

        $mainmenu->update($data);

        return redirect()->route('mainmenu.index')
            ->with('success', 'Menu updated successfully!');
    }

    public function destroy(Menu $mainmenu)
    {
        $mainmenu->children()->delete();
        $mainmenu->delete();

        return redirect()->route('mainmenu.index')
            ->with('success', 'Menu deleted successfully!');
    }

    public function toggle(Menu $mainmenu)
    {
        $mainmenu->update(['is_active' => ! $mainmenu->is_active]);

        return back()->with('success', 'Menu status updated!');
    }

    /**
     * Submenu Management page (read-only listing).
     * Submenu = any menu with parent_id NOT NULL.
     * Optional ?parent={id} filters to one Main Menu.
     * All write actions reuse the existing store/update/destroy routes —
     * no change to validation, numbering (id) or relationships.
     */
    public function submenus(Request $request)
    {
        $parents = Menu::whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        $selectedParentId = $request->query('parent');
        $selectedParent = null;

        $query = Menu::whereNotNull('parent_id')
            ->with('parent')
            ->orderBy('parent_id')
            ->orderBy('sort_order');

        if (! empty($selectedParentId)) {
            $selectedParent = Menu::whereNull('parent_id')->find($selectedParentId);
            if ($selectedParent) {
                $query->where('parent_id', $selectedParent->id);
            }
        }

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($w) use ($search) {
                $w->where('text', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%");
            });
        }

        $submenus = $query->get();

        return view('admin.submenus.index', compact('parents', 'submenus', 'selectedParent', 'search'));
    }
}
