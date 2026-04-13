<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogApiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 15), 30);

        $posts = BlogPost::with('category')
            ->where('is_published', 1)
            ->select('id', 'title', 'slug', 'excerpt', 'thumbnail', 'category_id', 'published_at')
            ->orderByDesc('published_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $posts->map(fn($p) => [
                'id'           => $p->id,
                'title'        => $p->title,
                'slug'         => $p->slug,
                'excerpt'      => $p->excerpt,
                'category'     => $p->category?->name,
                'thumbnail'    => $p->thumbnail ? asset($p->thumbnail) : null,
                'published_at' => $p->published_at,
                'url'          => route('blog.show', ['slug' => $p->slug]),
            ]),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page'    => $posts->lastPage(),
                'total'        => $posts->total(),
            ],
        ]);
    }

    public function show($slug)
    {
        $post = BlogPost::with('category')
            ->where('slug', $slug)
            ->where('is_published', 1)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'id'           => $post->id,
                'title'        => $post->title,
                'slug'         => $post->slug,
                'content'      => $post->content,
                'excerpt'      => $post->excerpt,
                'category'     => $post->category?->name,
                'thumbnail'    => $post->thumbnail ? asset($post->thumbnail) : null,
                'published_at' => $post->published_at,
                'url'          => route('blog.show', ['slug' => $post->slug]),
            ],
        ]);
    }
}
