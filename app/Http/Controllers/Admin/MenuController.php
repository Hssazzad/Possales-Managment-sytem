<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /* ─────────────────────────────────────────
     |  INDEX
     ───────────────────────────────────────── */
    public function index()
    {
        $menus = Menu::whereNull('parent_id')
                     ->orderBy('sort_order')
                     ->with(['children' => fn($q) => $q->orderBy('sort_order')])
                     ->get();

        return view('admin.menus.index', compact('menus'));
    }

    /* ─────────────────────────────────────────
     |  CREATE
     ───────────────────────────────────────── */
    public function addNewView()
    {
        $parents = Menu::whereNull('parent_id')
                       ->orderBy('sort_order')
                       ->get();
        return view('admin.menus.add-new', compact('parents'));
    }


    public function create()
    {
        $parents = Menu::whereNull('parent_id')
                       ->orderBy('sort_order')
                       ->get();

        return view('admin.menus.create', compact('parents'));
    }

    public function show(Menu $menu)
    {
        return view('admin.menus.show', compact('menu'));
    }

    /* ─────────────────────────────────────────
     |  STORE
     ───────────────────────────────────────── */
    public function store(Request $request)
    {
        $data = $request->validate([
            'text'        => 'required|string|max:100',
            'url'         => 'required|string|max:255',
            'icon'        => 'required|string|max:100',
            'parent_id'   => 'nullable|exists:menus,id',
            'sort_order'  => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'nullable|boolean',
        ]);

        $data['sort_order'] = $data['sort_order']
            ?? (Menu::where('parent_id', $data['parent_id'] ?? null)->max('sort_order') + 1);

        $data['is_active'] = $request->has('is_active');

        Menu::create($data);

        return redirect()->route('menus.index')
                         ->with('success', 'Menu created successfully!');
    }

    /* ─────────────────────────────────────────
     |  EDIT
     ───────────────────────────────────────── */
    public function edit(Menu $menu)
    {
        $parents = Menu::whereNull('parent_id')
                       ->where('id', '!=', $menu->id)
                       ->orderBy('sort_order')
                       ->get();

        return view('admin.menus.edit', compact('menu', 'parents'));
    }

    /* ─────────────────────────────────────────
     |  UPDATE
     ───────────────────────────────────────── */
    public function update(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'text'        => 'required|string|max:100',
            'url'         => 'required|string|max:255',
            'icon'        => 'required|string|max:100',
            'parent_id'   => 'nullable|exists:menus,id',
            'sort_order'  => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'nullable|boolean',
        ]);

        // Prevent menu from being its own parent
        if (isset($data['parent_id']) && $data['parent_id'] == $menu->id) {
            return back()->withErrors(['parent_id' => 'A menu cannot be its own parent.']);
        }

        $data['is_active']   = $request->has('is_active');
        $data['sort_order']  = $data['sort_order'] ?? $menu->sort_order;

        $menu->update($data);

        return redirect()->route('menus.index')
                         ->with('success', 'Menu updated successfully!');
    }

    /* ─────────────────────────────────────────
     |  DESTROY
     ───────────────────────────────────────── */
    public function destroy(Menu $menu)
    {
        // Delete children first
        $menu->children()->delete();
        $menu->delete();

        return redirect()->route('menus.index')
                         ->with('success', 'Menu deleted successfully!');
    }

    /* ─────────────────────────────────────────
     |  TOGGLE STATUS
     ───────────────────────────────────────── */
    public function toggle(Menu $menu)
    {
        $menu->update(['is_active' => !$menu->is_active]);

        return back()->with('success', 'Menu status updated!');
    }

    /* ─────────────────────────────────────────
     |  REORDER (AJAX)
     ───────────────────────────────────────── */
    public function reorder(Request $request)
    {
        $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer|exists:menus,id',
        ]);

        foreach ($request->order as $index => $id) {
            Menu::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    /* ─────────────────────────────────────────
     |  GET NEXT SORT ORDER (AJAX)
     ───────────────────────────────────────── */
    public function getNextSortOrder(Request $request)
    {
        $parentId = $request->input('parent_id');
        $max      = Menu::where('parent_id', $parentId)->max('sort_order');

        return response()->json(['nextSortOrder' => $max + 1]);
    }
}
