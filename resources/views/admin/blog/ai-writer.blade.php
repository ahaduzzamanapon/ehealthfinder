@extends('admin.layouts.app')
@section('title', 'AI Blog Writer')

@section('content')
<style>
.ai-wrap { max-width: 1100px; margin: 0 auto; }
.ai-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; justify-content: space-between; }
.ai-header h1 { font-size: 1.5rem; font-weight: 800; color: #1e1b4b; margin: 0; display: flex; align-items: center; gap: .6rem; }
.gemini-badge { background: linear-gradient(135deg, #4285f4, #0f9d58, #f4b400, #db4437); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 900; font-size: .85rem; }

/* Generator card */
.gen-card {
    background: #fff; border-radius: 20px; padding: 2rem 2.5rem;
    border: 1px solid #e2e8f0; box-shadow: 0 8px 30px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
}
.gen-card h2 { font-size: 1.1rem; font-weight: 800; color: #1e1b4b; margin: 0 0 1.5rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media(max-width:700px){ .form-grid { grid-template-columns:1fr; } }
.fg { display: flex; flex-direction: column; gap: .4rem; }
.fg label { font-size: .82rem; font-weight: 700; color: #374151; }
.fg input, .fg select, .fg textarea {
    padding: .7rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 10px;
    font-size: .92rem; color: #1e293b; background: #f8fafc;
    transition: border-color .2s;
}
.fg input:focus, .fg select:focus, .fg textarea:focus {
    outline: none; border-color: #4f46e5; background: #fff;
    box-shadow: 0 0 0 3px rgba(79,70,229,.1);
}
.topic-input { grid-column: 1 / -1; }
.gen-btn {
    display: inline-flex; align-items: center; gap: .5rem; margin-top: 1.5rem;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: #fff; font-weight: 800; font-size: 1rem;
    padding: .85rem 2rem; border-radius: 12px; border: none; cursor: pointer;
    box-shadow: 0 6px 20px rgba(79,70,229,.35); transition: transform .2s;
}
.gen-btn:hover { transform: translateY(-2px); }
.gen-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }

/* Loading overlay */
.loading-overlay {
    display: none; position: fixed; inset: 0; background: rgba(15,23,42,.85);
    z-index: 9999; align-items: center; justify-content: center; flex-direction: column; gap: 1.5rem;
}
.loading-overlay.active { display: flex; }
.spinner { width: 56px; height: 56px; border: 5px solid rgba(255,255,255,.15); border-top-color: #818cf8; border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.loading-text { color: #e2e8f0; font-size: 1.1rem; font-weight: 600; }
.loading-sub { color: #94a3b8; font-size: .85rem; }

/* Preview */
.preview-card {
    background: #fff; border-radius: 20px; padding: 2rem 2.5rem;
    border: 1px solid #e2e8f0; box-shadow: 0 8px 30px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
}
.preview-card h2 { font-size: 1.1rem; font-weight: 800; color: #1e1b4b; margin: 0 0 1.25rem; border-bottom: 2px solid #f1f5f9; padding-bottom: .75rem; }
.ai-title { font-size: 1.5rem; font-weight: 900; color: #1e1b4b; margin-bottom: .5rem; }
.ai-meta { font-size: .82rem; color: #94a3b8; margin-bottom: 1.25rem; display: flex; gap: 1rem; flex-wrap: wrap; }
.ai-excerpt { background: #f0fdf4; border-left: 4px solid #10b981; padding: .85rem 1.1rem; border-radius: 0 10px 10px 0; font-size: .92rem; color: #166534; margin-bottom: 1.5rem; }

.section-preview { border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: 1.25rem; overflow: hidden; }
.section-heading { background: #f8fafc; padding: .85rem 1.25rem; font-weight: 800; color: #1e1b4b; font-size: .95rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
.section-content { padding: 1rem 1.25rem; font-size: .9rem; color: #374151; line-height: 1.7; }
.section-content p { margin-bottom: .75rem; }
.section-content ul { margin-left: 1.25rem; margin-bottom: .75rem; }
.section-content li { margin-bottom: .3rem; }

.seo-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
@media(max-width:700px){ .seo-row { grid-template-columns:1fr; } }

.publish-bar {
    display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
    background: #f8fafc; border-radius: 14px; padding: 1rem 1.5rem;
    border: 1px solid #e2e8f0; margin-top: 1.5rem;
}
.pub-btn {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff; font-weight: 800; font-size: .95rem;
    padding: .75rem 1.75rem; border-radius: 10px; border: none; cursor: pointer;
    box-shadow: 0 4px 14px rgba(16,185,129,.3); transition: transform .15s;
}
.pub-btn:hover { transform: translateY(-1px); }
.draft-toggle { display: flex; align-items: center; gap: .5rem; font-size: .88rem; font-weight: 600; color: #374151; }

.error-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 1rem 1.25rem; color: #dc2626; font-size: .9rem; margin-bottom: 1.5rem; }
.success-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 1rem 1.25rem; color: #166534; font-size: .9rem; margin-bottom: 1.5rem; }
</style>

<div class="ai-wrap">

    <div class="ai-header">
        <h1>🤖 AI Blog Writer <span class="gemini-badge">Gemini</span></h1>
        <a href="{{ route('admin.blog.posts.index') }}" style="font-size:.85rem;color:#64748b;text-decoration:none;">← All Posts</a>
    </div>

    @if(session('error'))
        <div class="error-box">❌ {{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="success-box">✅ {{ session('success') }}</div>
    @endif

    {{-- Generator Form --}}
    <div class="gen-card">
        <h2>✏️ Generate New Blog Post</h2>
        <form method="POST" action="{{ route('admin.blog.ai-writer.generate') }}" id="gen-form">
            @csrf
            <div class="form-grid">
                <div class="fg topic-input">
                    <label>Blog Topic / Keyword *</label>
                    <input type="text" name="topic" required placeholder="e.g. Benefits of Vitamin D for bone health in Bangladesh" value="{{ old('topic') }}">
                </div>
                <div class="fg">
                    <label>Language</label>
                    <select name="language">
                        <option value="english" {{ old('language','english') === 'english' ? 'selected' : '' }}>🇬🇧 English</option>
                        <option value="bangla"  {{ old('language') === 'bangla'  ? 'selected' : '' }}>🇧🇩 বাংলা (Bangla)</option>
                    </select>
                </div>
                <div class="fg">
                    <label>Tone</label>
                    <select name="tone">
                        <option value="informative"  {{ old('tone','informative') === 'informative'  ? 'selected' : '' }}>📚 Informative</option>
                        <option value="friendly"     {{ old('tone') === 'friendly'     ? 'selected' : '' }}>😊 Friendly</option>
                        <option value="professional" {{ old('tone') === 'professional' ? 'selected' : '' }}>💼 Professional</option>
                        <option value="simple"       {{ old('tone') === 'simple'       ? 'selected' : '' }}>🙂 Simple & Easy</option>
                    </select>
                </div>
                <div class="fg">
                    <label>Number of Sections</label>
                    <select name="sections">
                        @foreach([3,4,5,6,7,8] as $n)
                        <option value="{{ $n }}" {{ old('sections',4) == $n ? 'selected' : '' }}>{{ $n }} sections</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="gen-btn" id="gen-btn">
                ✨ Generate with Gemini AI
            </button>
        </form>
    </div>

    {{-- Loading Overlay --}}
    <div class="loading-overlay" id="loading-overlay">
        <div class="spinner"></div>
        <div class="loading-text">✨ Gemini is writing your blog post...</div>
        <div class="loading-sub">This may take 10-30 seconds</div>
    </div>

    {{-- Preview & Publish --}}
    @if(!empty($data))
    <form method="POST" action="{{ route('admin.blog.ai-writer.publish') }}" id="publish-form">
        @csrf

        <div class="preview-card">
            <h2>📄 Preview & Edit</h2>

            {{-- Title --}}
            <div class="fg" style="margin-bottom:1rem;">
                <label>Title</label>
                <input type="text" name="title" value="{{ $data['title'] }}" required>
            </div>

            {{-- Excerpt --}}
            <div class="fg" style="margin-bottom:1rem;">
                <label>Excerpt / Summary</label>
                <textarea name="excerpt" rows="2">{{ $data['excerpt'] }}</textarea>
            </div>

            {{-- SEO fields --}}
            <div class="seo-row">
                <div class="fg">
                    <label>SEO Title (max 60 chars)</label>
                    <input type="text" name="seo_title" value="{{ $data['seo_title'] }}" maxlength="60">
                </div>
                <div class="fg">
                    <label>Meta Description (max 155 chars)</label>
                    <input type="text" name="seo_description" value="{{ $data['seo_description'] }}" maxlength="155">
                </div>
            </div>

            {{-- Tags + Author + Category --}}
            <div class="form-grid" style="margin-bottom:1rem;">
                <div class="fg">
                    <label>Tags (comma separated)</label>
                    <input type="text" name="tags" value="{{ $data['tags'] }}">
                </div>
                <div class="fg">
                    <label>Author</label>
                    <input type="text" name="author_name" value="{{ $data['author'] ?? 'eHealthFinder Editorial Team' }}">
                </div>
                <div class="fg">
                    <label>Category</label>
                    <select name="blog_category_id">
                        <option value="">— No Category —</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Sections --}}
            @foreach($data['sections'] as $i => $sec)
            <div class="section-preview">
                <div class="section-heading">
                    <span>📌 Section {{ $i + 1 }}</span>
                </div>
                <div style="padding:1rem 1.25rem;">
                    <div class="fg" style="margin-bottom:.75rem;">
                        <label>Heading</label>
                        <input type="text" name="sections[{{ $i }}][heading]" value="{{ $sec['heading'] }}">
                    </div>
                    <div class="fg">
                        <label>Content (HTML)</label>
                        <textarea name="sections[{{ $i }}][content]" rows="6">{{ $sec['content'] }}</textarea>
                    </div>
                </div>
                {{-- Preview --}}
                <div class="section-content" style="border-top:1px solid #f1f5f9;">
                    {!! $sec['content'] !!}
                </div>
            </div>
            @endforeach

            {{-- Publish bar --}}
            <div class="publish-bar">
                <button type="submit" class="pub-btn">💾 Save Post</button>
                <label class="draft-toggle">
                    <input type="checkbox" name="is_published" value="1">
                    Publish immediately
                </label>
                <span style="font-size:.8rem;color:#94a3b8;">Or save as draft and review later</span>
            </div>
        </div>
    </form>
    @endif

</div>

<script>
document.getElementById('gen-form').addEventListener('submit', function() {
    document.getElementById('gen-btn').disabled = true;
    document.getElementById('gen-btn').textContent = 'Generating...';
    document.getElementById('loading-overlay').classList.add('active');
});
</script>
@endsection
