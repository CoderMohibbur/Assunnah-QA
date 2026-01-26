<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class AdminPageController extends Controller
{
    public function index()
    {
        $pages = Page::query()
            ->whereNull('deleted_at')
            ->orderBy('slug')
            ->paginate(20);

        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'slug' => ['required','string','max:190','unique:pages,slug'],
            'title' => ['nullable','string','max:190'],
            'content_html' => ['nullable','string','max:200000'],
            'is_active' => ['nullable','boolean'],
        ]);

        Page::create([
            'slug' => trim($data['slug']),
            'title' => $data['title'] ?? null,
            'content_html' => $data['content_html'] ?? null,
            'is_active' => (bool)($data['is_active'] ?? true),
        ]);

        return redirect()->route('admin.pages.index')->with('success', 'Page created ✅');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'slug' => ['required','string','max:190','unique:pages,slug,'.$page->id],
            'title' => ['nullable','string','max:190'],
            'content_html' => ['nullable','string','max:200000'],
            'is_active' => ['nullable','boolean'],
        ]);

        $page->forceFill([
            'slug' => trim($data['slug']),
            'title' => $data['title'] ?? null,
            'content_html' => $data['content_html'] ?? null,
            'is_active' => (bool)($data['is_active'] ?? false),
        ])->save();

        return redirect()->route('admin.pages.index')->with('success', 'Page updated ✅');
    }

    public function destroy(Page $page)
    {
        $page->delete();
        return redirect()->route('admin.pages.index')->with('success', 'Page deleted ✅');
    }
}
