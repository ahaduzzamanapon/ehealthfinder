<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogCategoryAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $categories = BlogCategory::when($q, fn($query) => $query->where('name', 'like', "%$q%"))
            ->withCount('posts')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();
        return view('admin.blog.categories', compact('categories', 'q'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:blog_categories,name']);
        
        BlogCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);
        
        return back()->with('success', "Blog category \"{$request->name}\" added.");
    }

    public function update(Request $request, BlogCategory $category)
    {
        $request->validate(['name' => 'required|string|max:255|unique:blog_categories,name,'.$category->id]);
        
        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);
        
        return back()->with('success', "Blog category updated.");
    }

    public function destroy(BlogCategory $category)
    {
        if ($category->posts()->count() > 0) {
            return back()->with('error', "Cannot delete — {$category->posts()->count()} posts linked.");
        }
        $category->delete();
        return back()->with('success', "Blog category deleted.");
    }
}
