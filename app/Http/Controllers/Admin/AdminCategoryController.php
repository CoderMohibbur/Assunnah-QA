<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCategoryController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $categories = Category::query()
            ->whereNull('deleted_at')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name_bn', 'like', "%{$q}%")
                       ->orWhere('slug', 'like', "%{$q}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.categories.index', compact('categories', 'q'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_bn'     => ['required', 'string', 'max:191'],
            'slug'        => ['nullable', 'string', 'max:191', 'unique:categories,slug'],
            'description' => ['nullable', 'string', 'max:5000'],
            'sort_order'  => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $name = trim($data['name_bn']);
        $slug = trim((string)($data['slug'] ?? ''));

        if ($slug === '') {
            $slug = Str::slug($name, '-');
            if ($slug === '') {
                $slug = 'cat-' . now()->format('YmdHis');
            }
        }

        Category::create([
            'name_bn'     => $name,
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'sort_order'  => (int)($data['sort_order'] ?? 0),
            'is_active'   => (bool)($data['is_active'] ?? true),
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Category created ✅');
    }

    public function edit(Category $category)
    {
        if ($category->deleted_at) abort(404);
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Category $category, Request $request)
    {
        if ($category->deleted_at) abort(404);

        $data = $request->validate([
            'name_bn'     => ['required', 'string', 'max:191'],
            'slug'        => ['required', 'string', 'max:191', 'unique:categories,slug,' . $category->id],
            'description' => ['nullable', 'string', 'max:5000'],
            'sort_order'  => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $category->forceFill([
            'name_bn'     => trim($data['name_bn']),
            'slug'        => trim($data['slug']),
            'description' => $data['description'] ?? null,
            'sort_order'  => (int)($data['sort_order'] ?? 0),
            // ✅ checkbox unchecked হলে field আসবে না → false
            'is_active'   => $request->boolean('is_active'),
        ])->save();

        return redirect()->route('admin.categories.index')->with('success', 'Category updated ✅');
    }

    public function destroy(Category $category)
    {
        if ($category->deleted_at) {
            return back()->with('success', 'Already deleted.');
        }

        $category->delete(); // soft delete
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted ✅');
    }
}
