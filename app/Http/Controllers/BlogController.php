<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Display a listing of the blog posts.
     */
    public function index(Request $request)
    {
        $categorySlug = $request->query('category');
        
        $posts = BlogPost::with('category')
            ->where('is_published', true)
            ->when($categorySlug, function ($query, $categorySlug) {
                return $query->whereHas('category', function($q) use ($categorySlug) {
                    $q->where('slug', $categorySlug);
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = BlogCategory::withCount('posts')->orderBy('name')->get();

        return view('blog.index', compact('posts', 'categories', 'categorySlug'));
    }

    /**
     * Display the specified blog post.
     */
    public function show($slug)
    {
        $post = BlogPost::with(['category', 'sections' => function($q) {
                $q->orderBy('order_index');
            }])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // Fetch related posts (same category, excluding current)
        $relatedPosts = collect();
        if ($post->blog_category_id) {
            $relatedPosts = BlogPost::with('category')
                ->where('blog_category_id', $post->blog_category_id)
                ->where('id', '!=', $post->id)
                ->where('is_published', true)
                ->latest()
                ->take(3)
                ->get();
        }

        // Fallback: if no related category posts, just get latest
        if ($relatedPosts->isEmpty()) {
            $relatedPosts = BlogPost::with('category')
                ->where('id', '!=', $post->id)
                ->where('is_published', true)
                ->latest()
                ->take(3)
                ->get();
        }

        return view('blog.show', compact('post', 'relatedPosts'));
    }

    /**
     * Generate dynamic sitemap XML.
     */
    public function sitemap()
    {
        $posts = BlogPost::where('is_published', true)
            ->orderBy('updated_at', 'desc')
            ->get();
            
        return response()->view('sitemap', compact('posts'))->header('Content-Type', 'text/xml');
    }
}
