<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogPostSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiBlogWriterController extends Controller
{
    private string $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function index()
    {
        $categories = BlogCategory::orderBy('name')->get();
        return view('admin.blog.ai-writer', compact('categories'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'topic'    => 'required|string|max:300',
            'language' => 'required|in:english,bangla',
            'tone'     => 'required|in:informative,friendly,professional,simple',
            'sections' => 'required|integer|min:2|max:8',
        ]);

        $apiKey  = env('GEMINI_API_KEY');
        $topic   = $request->topic;
        $lang    = $request->language === 'bangla' ? 'Bengali (Bangla)' : 'English';
        $tone    = $request->tone;
        $numSecs = (int) $request->sections;

        $prompt = <<<PROMPT
You are a professional health/medical blog writer. Write a detailed blog post in {$lang}.

Topic: "{$topic}"
Tone: {$tone}
Number of sections: {$numSecs}

Return ONLY valid JSON (no markdown, no code blocks) in this exact format:
{
  "title": "Full blog post title",
  "excerpt": "2-3 sentence summary (max 200 chars)",
  "seo_title": "SEO optimized title (max 60 chars)",
  "seo_description": "Meta description (max 155 chars)",
  "tags": "tag1, tag2, tag3, tag4, tag5",
  "image_query": "2-3 keywords for a relevant medical/health image search on Unsplash",
  "author": "eHealthFinder Editorial Team",
  "sections": [
    {
      "heading": "Section heading",
      "content": "Full HTML content for this section. Use <p>, <ul>, <li>, <strong> tags. At least 150 words per section."
    }
  ]
}

Write detailed, accurate health content. Do NOT include any text outside the JSON.
PROMPT;

        try {
            $response = Http::timeout(45)->post("{$this->geminiUrl}?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature'     => 0.7,
                    'maxOutputTokens' => 8192,
                ]
            ]);

            if (!$response->ok()) {
                return back()->withInput()->with('error', 'Gemini API error: ' . $response->status() . ' — ' . $response->body());
            }

            $text = $response->json('candidates.0.content.parts.0.text') ?? '';

            // Strip possible markdown code fences
            $text = preg_replace('/^```json\s*/i', '', trim($text));
            $text = preg_replace('/```\s*$/i', '', $text);
            $text = trim($text);

            $data = json_decode($text, true);
            if (!$data || !isset($data['title'])) {
                return back()->withInput()->with('error', 'Could not parse Gemini response. Raw: ' . Str::limit($text, 300));
            }

            // Build Unsplash image URL (no API key needed for random image)
            $imageQuery   = urlencode($data['image_query'] ?? 'health medicine');
            $data['featured_image_url'] = "https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=1200&q=80";
            // Use a static health image for reliability; admin can change
            $data['image_query_display'] = $data['image_query'] ?? 'health';

            $categories = BlogCategory::orderBy('name')->get();
            return view('admin.blog.ai-writer', compact('categories', 'data'));

        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function publish(Request $request)
    {
        $request->validate([
            'title'           => 'required|string|max:500',
            'excerpt'         => 'nullable|string',
            'seo_title'       => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'tags'            => 'nullable|string',
            'author_name'     => 'nullable|string|max:255',
            'blog_category_id'=> 'nullable|integer',
        ]);

        $post = new BlogPost();
        $post->title            = $request->title;
        $post->slug             = Str::slug($request->title) . '-' . Str::random(6);
        $post->excerpt          = $request->excerpt;
        $post->seo_title        = $request->seo_title ?: $request->title;
        $post->seo_description  = $request->seo_description;
        $post->tags             = $request->tags;
        $post->author_name      = $request->author_name ?: 'eHealthFinder Editorial Team';
        $post->blog_category_id = $request->blog_category_id ?: null;
        $post->is_published     = $request->boolean('is_published');
        $post->save();

        // Save sections
        $sections = $request->input('sections', []);
        foreach ($sections as $i => $sec) {
            BlogPostSection::create([
                'blog_post_id' => $post->id,
                'heading'      => $sec['heading'] ?? null,
                'content'      => $sec['content'] ?? null,
                'order_index'  => $i,
            ]);
        }

        return redirect()->route('admin.blog.posts.edit', $post)
            ->with('success', '✅ AI-generated blog post saved! Review and publish.');
    }
}
