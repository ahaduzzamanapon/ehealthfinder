<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogPostSection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BlogPostAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $posts = BlogPost::with('category')
            ->when($q, fn($query) => $query->where('title', 'like', "%$q%"))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.blog.posts.index', compact('posts', 'q'));
    }

    public function create()
    {
        $categories = BlogCategory::orderBy('name')->get();
        return view('admin.blog.posts.form', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'            => 'required|string|max:500',
            'blog_category_id' => 'nullable|integer',
            'excerpt'          => 'nullable|string',
            'author_name'      => 'nullable|string|max:255',
            'is_published'     => 'nullable|boolean',
            'seo_title'        => 'nullable|string|max:255',
            'seo_description'  => 'nullable|string',
            'tags'             => 'nullable|string',
            'featured_image'   => 'nullable|image|max:2048',
        ]);

        $post = new BlogPost();
        $post->title = $request->title;
        $post->slug = Str::slug($request->title) . '-' . uniqid();
        $post->blog_category_id = $request->blog_category_id;
        $post->excerpt = $request->excerpt;
        $post->author_name = $request->author_name;
        $post->is_published = $request->boolean('is_published');
        $post->seo_title = $request->seo_title ?: $request->title;
        $post->seo_description = $request->seo_description ?: Str::limit(strip_tags($request->excerpt), 150);
        $post->tags = $request->tags;

        if ($request->hasFile('featured_image')) {
            $post->featured_image = $request->file('featured_image')->store('blog', 'public');
        }
        $post->save();

        $this->syncSections($request, $post);

        // If no excerpt was provided but sections were added, try to auto-update seo_description from the first section
        if (!$request->seo_description && !$request->excerpt) {
            $firstSec = $post->sections->first();
            if ($firstSec && $firstSec->content) {
                $post->seo_description = Str::limit(strip_tags($firstSec->content), 150);
                $post->save();
            }
        }

        return redirect()->route('admin.blog.posts.index')->with('success', 'Blog post created successfully.');
    }

    public function edit(BlogPost $post)
    {
        $categories = BlogCategory::orderBy('name')->get();
        $post->load(['sections' => function($q) { $q->orderBy('order_index'); }]);
        return view('admin.blog.posts.form', compact('post', 'categories'));
    }

    public function update(Request $request, BlogPost $post)
    {
        $request->validate([
            'title'            => 'required|string|max:500',
            'blog_category_id' => 'nullable|integer',
            'excerpt'          => 'nullable|string',
            'author_name'      => 'nullable|string|max:255',
            'is_published'     => 'nullable|boolean',
            'seo_title'        => 'nullable|string|max:255',
            'seo_description'  => 'nullable|string',
            'tags'             => 'nullable|string',
            'featured_image'   => 'nullable|image|max:2048',
        ]);

        $post->title = $request->title;
        $post->blog_category_id = $request->blog_category_id;
        $post->excerpt = $request->excerpt;
        $post->author_name = $request->author_name;
        $post->is_published = $request->boolean('is_published');
        $post->seo_title = $request->seo_title ?: $request->title;
        $post->seo_description = $request->seo_description ?: Str::limit(strip_tags($request->excerpt), 150);
        $post->tags = $request->tags;

        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) { Storage::disk('public')->delete($post->featured_image); }
            $post->featured_image = $request->file('featured_image')->store('blog', 'public');
        }

        $post->save();

        $this->syncSections($request, $post);

        // Auto fallback from first section if excerpt is empty
        if (!$request->seo_description && !$request->excerpt) {
            $firstSec = $post->sections->first();
            if ($firstSec && $firstSec->content) {
                $post->seo_description = Str::limit(strip_tags($firstSec->content), 150);
                $post->save();
            }
        }

        return redirect()->route('admin.blog.posts.index')->with('success', 'Blog post updated successfully.');
    }

    public function destroy(BlogPost $post)
    {
        foreach ($post->sections as $sec) {
            if ($sec->image_path) { Storage::disk('public')->delete($sec->image_path); }
            $sec->delete();
        }
        if ($post->featured_image) { Storage::disk('public')->delete($post->featured_image); }

        $post->delete();
        return back()->with('success', 'Blog post deleted.');
    }

    private function syncSections(Request $request, BlogPost $post)
    {
        $keptSectionIds = [];
        $sections = $request->input('sections', []);
        
        foreach ($sections as $index => $secData) {
            $sectionId = $secData['id'] ?? null;

            $dbData = [
                'blog_post_id' => $post->id,
                'heading'      => $secData['heading'] ?? null,
                'content'      => $secData['content'] ?? null,
                'order_index'  => $index,
            ];

            if ($request->hasFile("sections.{$index}.image")) {
                $dbData['image_path'] = $request->file("sections.{$index}.image")->store('blog/sections', 'public');
            }

            if ($sectionId) {
                $section = $post->sections()->find($sectionId);
                if ($section) {
                    if (isset($secData['remove_image']) && $secData['remove_image'] == '1') {
                        if ($section->image_path) { Storage::disk('public')->delete($section->image_path); }
                        $dbData['image_path'] = null;
                    } elseif (!isset($dbData['image_path'])) {
                        // User didn't upload a new one, and didn't remove existing. Keep old.
                        $dbData['image_path'] = $section->image_path;
                    }
                    $section->update($dbData);
                    $keptSectionIds[] = $section->id;
                }
            } else {
                $newSec = current([$post->sections()->create($dbData)]);
                $keptSectionIds[] = $newSec->id;
            }
        }

        $sectionsToDelete = $post->sections()->whereNotIn('id', $keptSectionIds)->get();
        foreach ($sectionsToDelete as $oldSec) {
            if ($oldSec->image_path) { Storage::disk('public')->delete($oldSec->image_path); }
            $oldSec->delete();
        }
    }
}
