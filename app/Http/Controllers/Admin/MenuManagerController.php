<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentMenu;
use App\Models\SubMenu;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuManagerController extends Controller
{
    public function parents()
    {
        return view('admin.menu-manager.parents', ['parents' => ParentMenu::orderBy('position')->get()]);
    }

    public function storeParent(Request $request)
    {
        ParentMenu::create($this->parentData($request));
        return back()->with('success', 'Main menu added successfully.');
    }

    public function updateParent(Request $request, ParentMenu $parentMenu)
    {
        $parentMenu->update($this->parentData($request, $parentMenu));
        return back()->with('success', 'Main menu updated successfully.');
    }

    public function destroyParent(ParentMenu $parentMenu)
    {
        $parentMenu->submenus()->delete();
        $parentMenu->delete();
        return back()->with('success', 'Main menu deleted successfully.');
    }

    public function submenus()
    {
        return view('admin.menu-manager.submenus', [
            'parents' => ParentMenu::where('status', true)->orderBy('position')->get(),
            'submenus' => SubMenu::with('parent')->orderBy('menuparentcode')->orderBy('position')->get(),
        ]);
    }

    public function storeSubmenu(Request $request)
    {
        $data = $this->submenuData($request);
        $data['submenucode'] = $this->nextSubmenuCode((int) $data['menuparentcode']);

        SubMenu::create($data);
        return back()->with('success', 'Submenu added successfully.');
    }

    public function updateSubmenu(Request $request, SubMenu $subMenu)
    {
        $data = $this->submenuData($request);

        if ($data['menuparentcode'] != $subMenu->menuparentcode && SubMenu::where('menuparentcode', $data['menuparentcode'])->where('submenucode', $subMenu->submenucode)->exists()) {
            return back()->withErrors(['menuparentcode' => 'This parent already has the same submenu code.'])->withInput();
        }

        $subMenu->update($data);
        return back()->with('success', 'Submenu updated successfully.');
    }

    public function destroySubmenu(SubMenu $subMenu)
    {
        $subMenu->delete();
        return back()->with('success', 'Submenu deleted successfully.');
    }

    private function parentData(Request $request, ?ParentMenu $parent = null): array
    {
        return $request->validate([
            'parentcode' => ['required', 'integer', 'min:1', Rule::unique('parentmenu')->ignore($parent)],
            'parentname' => 'required|string|max:100',
            'controllername' => 'nullable|string|max:100',
            'mainroute' => ['required', 'string', 'max:255', Rule::unique('parentmenu')->ignore($parent)],
            'icon' => 'required|string|max:100',
            'position' => 'required|integer|min:1',
            'status' => 'nullable|boolean',
        ]) + ['status' => $request->boolean('status')];
    }

    private function submenuData(Request $request): array
    {
        return $request->validate([
            'menuparentcode' => 'required|exists:parentmenu,parentcode',
            'submenuname' => 'required|string|max:100',
            'method' => 'required|string|max:255',
            'icon' => 'required|string|max:100',
            'position' => 'required|integer|min:1',
            'status' => 'nullable|boolean',
        ]) + ['status' => $request->boolean('status')];
    }

    private function nextSubmenuCode(int $parentCode): int
    {
        for ($sequence = 1; ; $sequence++) {
            $code = (int) ($parentCode . str_pad((string) $sequence, 2, '0', STR_PAD_LEFT));

            if (! SubMenu::where('menuparentcode', $parentCode)->where('submenucode', $code)->exists()) {
                return $code;
            }
        }
    }
}
