<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Post;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $menus = Menu::all();
        $selectedMenu = null;

        if ($request->has('menu')) {
            $selectedMenu = Menu::with('items')->find($request->input('menu'));
        } elseif ($menus->count() > 0) {
            $selectedMenu = Menu::with('items')->find($menus->first()->id);
        }

        // Build Tree for Selected Menu
        $menuItems = [];
        if ($selectedMenu) {
            $menuItems = build_menu_tree($selectedMenu->items);
        }

        // Build Dynamic Accordions for Sidebar
        $accordions = [];

        // 1. Pages
        $accordions[] = [
            'label' => 'Pages',
            'type' => 'page',
            'items' => Post::where('type', 'page')->where('status', 'publish')->get()
        ];

        // 2. Posts
        $accordions[] = [
            'label' => 'Posts',
            'type' => 'post',
            'items' => Post::where('type', 'post')->where('status', 'publish')->latest()->take(20)->get()
        ];

        // 3. Custom Post Types
        $cpts = \App\Models\CustomPostType::where('active', true)->get();
        foreach ($cpts as $cpt) {
            $accordions[] = [
                'label' => $cpt->plural_label,
                'type' => $cpt->key,
                'items' => Post::where('type', $cpt->key)->where('status', 'publish')->latest()->take(20)->get()
            ];
        }

        // 4. Taxonomies (Categories, Tags)
        // Get distinct taxonomies from Tags table
        $taxonomies = \App\Models\Tag::select('taxonomy')->distinct()->pluck('taxonomy');
        foreach ($taxonomies as $tax) {
            $label = ucfirst(str_replace('_', ' ', $tax));
            if ($tax === 'category')
                $label = 'Categories';
            if ($tax === 'post_tag')
                $label = 'Tags';

            $accordions[] = [
                'label' => $label,
                'type' => 'taxonomy',
                'taxonomy' => $tax, // Store taxonomy key for special handling if needed
                'items' => \App\Models\Tag::where('taxonomy', $tax)->get()
            ];
        }

        // Get Menu Locations
        $locations = [
            'primary' => \App\Models\Setting::get('menu_location_primary'),
            'footer' => \App\Models\Setting::get('menu_location_footer'),
        ];

        return view('admin.menus.index', compact('menus', 'selectedMenu', 'menuItems', 'accordions', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $slug = \Illuminate\Support\Str::slug($request->name);

        $menu = Menu::create([
            'name' => $request->name,
            'slug' => $slug
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'menu' => $menu,
                'message' => 'Menu created successfully.'
            ]);
        }

        return redirect()->route('admin.menus.index', ['menu' => $menu->id])
            ->with('success', 'Menu created.');
    }

    public function destroy($id)
    {
        Menu::findOrFail($id)->delete();
        return redirect()->route('admin.menus.index')->with('success', 'Menu deleted.');
    }

    public function addItem(Request $request)
    {
        $menu = Menu::findOrFail($request->menu_id);

        // Items is an array of items to add
        $items = $request->input('items', []);

        foreach ($items as $item) {
            MenuItem::create([
                'menu_id' => $menu->id,
                'title' => $item['title'],
                'url' => $item['url'] ?? null,
                'type' => $item['type'],
                'type_id' => $item['id'] ?? null, // For post/page ID
                'order' => 999 // Append to end
            ]);
        }

        return back()->with('success', 'Items added to menu.');
    }

    public function updateTree(Request $request)
    {
        $menuId = $request->menu_id;
        $tree = $request->input('tree'); // JSON structure from specific drag-drop lib

        // Handle Locations
        if ($request->has('locations')) {
            $locations = $request->input('locations');
            foreach ($locations as $loc => $isChecked) {
                if ($isChecked) {
                    \App\Models\Setting::set("menu_location_{$loc}", $menuId);
                } else {
                    // Only unset if currently set to this menu
                    $current = \App\Models\Setting::get("menu_location_{$loc}");
                    if ($current == $menuId) {
                        \App\Models\Setting::set("menu_location_{$loc}", '');
                    }
                }
            }
        }

        $this->saveTree($tree, null);

        return response()->json(['success' => true]);
    }

    private function saveTree($items, $parentId)
    {
        foreach ($items as $index => $item) {
            $menuItem = MenuItem::find($item['id']);
            if ($menuItem) {
                $menuItem->order = $index;
                $menuItem->parent_id = $parentId;
                $menuItem->save();

                if (isset($item['children']) && !empty($item['children'])) {
                    $this->saveTree($item['children'], $menuItem->id);
                }
            }
        }
    }

    public function updateItem(Request $request, $id)
    {
        $item = MenuItem::findOrFail($id);
        $item->update($request->only(['title', 'url', 'target', 'css_class']));
        return back()->with('success', 'Item updated.');
    }

    public function deleteItem($id)
    {
        MenuItem::findOrFail($id)->delete();
        return back()->with('success', 'Item removed.');
    }
}

