<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\TagCategory;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $categories = TagCategory::with(['tags' => function($query) {
            $query->orderBy('name');
        }])->orderBy('name')->get();
        $tagsWithoutCategory = Tag::whereNull('tag_category_id')->orderBy('name')->get();
        
        return view('tags.index', compact('categories', 'tagsWithoutCategory'));
    }

    public function createCategory()
    {
        return view('tags.create-category');
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tag_categories,name',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'applies_to' => 'required|in:meals,items,both',
        ]);

        TagCategory::create($validated);

        return redirect()->route('tags.index')
            ->with('success', 'Tag category created successfully!');
    }

    public function createTag()
    {
        $categories = TagCategory::orderBy('name')->get();
        return view('tags.create-tag', compact('categories'));
    }

    public function storeTag(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tag_category_id' => 'nullable|exists:tag_categories,id',
        ]);

        // Check for unique name within category
        $exists = Tag::where('name', $validated['name'])
            ->where('tag_category_id', $validated['tag_category_id'] ?? null)
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'A tag with this name already exists in this category.'])->withInput();
        }

        Tag::create($validated);

        return redirect()->route('tags.index')
            ->with('success', 'Tag created successfully!');
    }

    public function editCategory(TagCategory $category)
    {
        return view('tags.edit-category', compact('category'));
    }

    public function updateCategory(Request $request, TagCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tag_categories,name,' . $category->id,
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'applies_to' => 'required|in:meals,items,both',
        ]);

        $category->update($validated);

        return redirect()->route('tags.index')
            ->with('success', 'Tag category updated successfully!');
    }

    public function editTag(Tag $tag)
    {
        $categories = TagCategory::orderBy('name')->get();
        return view('tags.edit-tag', compact('tag', 'categories'));
    }

    public function updateTag(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tag_category_id' => 'nullable|exists:tag_categories,id',
        ]);

        // Check for unique name within category (excluding current tag)
        $exists = Tag::where('name', $validated['name'])
            ->where('tag_category_id', $validated['tag_category_id'] ?? null)
            ->where('id', '!=', $tag->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'A tag with this name already exists in this category.'])->withInput();
        }

        $tag->update($validated);

        return redirect()->route('tags.index')
            ->with('success', 'Tag updated successfully!');
    }

    public function destroyCategory(TagCategory $category)
    {
        $category->delete();
        return redirect()->route('tags.index')
            ->with('success', 'Tag category deleted successfully!');
    }

    public function destroyTag(Tag $tag)
    {
        $tag->delete();
        return redirect()->route('tags.index')
            ->with('success', 'Tag deleted successfully!');
    }
}

