@extends('admin.layouts.app')
@section('title', isset($post) ? 'Edit Post' : 'Create Post')
@section('page-title', isset($post) ? "✏️ Edit: {$post->title}" : '📝 Create New Blog Post')

@section('extra-styles')
<style>
.section-builder { display:flex; flex-direction:column; gap:1.5rem; margin-top:2rem; }
.b-section { background:rgba(255,255,255,0.02); border:1px solid #334155; padding:1.5rem; border-radius:12px; position:relative; }
.b-section-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; padding-bottom:0.75rem; border-bottom:1px solid #334155; }
.b-section-title { font-weight:700; color:#818cf8; font-size:1rem; }
.btn-remove-sec { background:transparent; border:none; color:#ef4444; cursor:pointer; font-weight:700; padding:4px 8px; border-radius:6px; transition:0.2s; }
.btn-remove-sec:hover { background:rgba(239,68,68,0.15); }
.file-preview { margin-top:0.75rem; max-width:150px; border-radius:8px; display:block; border:1px solid #334155; }
/* Dark Mode For CKEditor */
.ck.ck-editor__main > .ck-editor__editable { min-height: 250px; background-color: #1e293b !important; color: #f8fafc !important; border-color: #334155 !important; border-bottom-left-radius: 8px !important; border-bottom-right-radius: 8px !important; }
.ck.ck-toolbar { background-color: #0f172a !important; border-color: #334155 !important; border-top-left-radius: 8px !important; border-top-right-radius: 8px !important; }
.ck.ck-icon { color: #cbd5e1 !important; }
.ck.ck-button:hover, .ck.ck-button.ck-on { background-color: #334155 !important; }
.ck-editor__editable.ck-focused { border-color: #4f46e5 !important; box-shadow: 0 0 0 2px rgba(79,70,229,0.2) !important; }
</style>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
@endsection

@section('content')
<form id="post-form" method="POST" action="{{ isset($post) ? route('admin.blog.posts.update', $post) : route('admin.blog.posts.store') }}" enctype="multipart/form-data">
    @csrf
    @if(isset($post)) @method('PUT') @endif

    <div style="display:grid; grid-template-columns:1fr 340px; gap:2rem; align-items:flex-start;">
        
        {{-- MAIN COLUMN --}}
        <div>
            <div class="admin-card">
                <div class="form-group">
                    <label class="form-label">Post Title *</label>
                    <input type="text" name="title" class="form-control" style="font-size:1.1rem; padding:1rem;" value="{{ old('title', $post->title ?? '') }}" required placeholder="Enter post title...">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Excerpt / Short Description</label>
                    <textarea name="excerpt" class="form-control" rows="3" placeholder="Brief summary of the post...">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Main Featured Image (Top of Post)</label>
                    <input type="file" name="featured_image" class="form-control" accept="image/*">
                    @if(isset($post) && $post->featured_image)
                        <img onerror="this.outerHTML='💊'" src="{{ Storage::url($post->featured_image) }}" class="file-preview mt-2" alt="Featured">
                    @endif
                </div>
            </div>

            {{-- SECTION BUILDER --}}
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>📑 Article Contents (Sections)</h3>
                    <button type="button" class="btn btn-outline btn-sm" onclick="addSection()">+ Add New Section</button>
                </div>
                
                <div id="sections-container" class="section-builder">
                    {{-- Dynamically populated sections --}}
                </div>

                <div style="margin-top:2rem; text-align:center;">
                    <button type="button" class="btn btn-primary" onclick="addSection()" style="padding:0.75rem 2rem;">➕ Add Another Section</button>
                </div>
            </div>
        </div>

        {{-- SIDEBAR COLUMN --}}
        <div>
            <div class="admin-card" style="position:sticky; top:100px;">
                <h3 style="margin-bottom:1.5rem; font-size:1rem;">⚙️ Publish Settings</h3>
                
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="blog_category_id" class="form-control">
                        <option value="">— Select Category —</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('blog_category_id', $post->blog_category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Author Name</label>
                    <input type="text" name="author_name" class="form-control" value="{{ old('author_name', $post->author_name ?? 'Admin') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Tags (Comma-separated)</label>
                    <input type="text" name="tags" class="form-control" value="{{ old('tags', $post->tags ?? '') }}" placeholder="cancer, health tips, diabetes">
                    <small style="color:#94a3b8; font-size:0.8rem;">Separate tags with commas</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Visibility</label>
                    <select name="is_published" class="form-control">
                        <option value="1" {{ old('is_published', $post->is_published ?? 1) == 1 ? 'selected' : '' }}>✅ Published</option>
                        <option value="0" {{ old('is_published', $post->is_published ?? 1) == 0 ? 'selected' : '' }}>📝 Draft</option>
                    </select>
                </div>

                <hr style="border:none; border-top:1px solid #334155; margin:1.5rem 0;">

                <h3 style="margin-bottom:1rem; font-size:0.9rem; color:#818cf8;">SEO Data</h3>
                <div class="form-group">
                    <label class="form-label">SEO Title (Optional)</label>
                    <input type="text" name="seo_title" class="form-control" value="{{ old('seo_title', $post->seo_title ?? '') }}" placeholder="Custom title for Google...">
                </div>
                <div class="form-group">
                    <label class="form-label">SEO Description (Optional)</label>
                    <textarea name="seo_description" class="form-control" rows="3" placeholder="Custom meta description...">{{ old('seo_description', $post->seo_description ?? '') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; padding:1rem; font-size:1rem; margin-top:1rem; box-shadow:0 10px 20px rgba(79,70,229,0.3);">
                    💾 {{ isset($post) ? 'Update Post' : 'Publish Post' }}
                </button>
            </div>
        </div>
    </div>
</form>

{{-- Invisible template for JS --}}
<template id="section-template">
    <div class="b-section" id="sec-{INDEX}">
        <input type="hidden" name="sections[{INDEX}][id]" value="">
        <div class="b-section-header">
            <div class="b-section-title">Section #{NUM}</div>
            <button type="button" class="btn-remove-sec" onclick="removeSection({INDEX})">✕ Remove</button>
        </div>
        
        <div class="form-group">
            <label class="form-label">Section Heading (Optional)</label>
            <input type="text" name="sections[{INDEX}][heading]" class="form-control" placeholder="E.g. Symptoms of Infection">
        </div>
        
        <div class="form-group">
            <label class="form-label">Text Content</label>
            <textarea name="sections[{INDEX}][content]" class="form-control rich-editor" rows="10"></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">Section Image (Optional)</label>
            <div style="display:flex; align-items:center; gap:1rem;">
                <input type="file" name="sections[{INDEX}][image]" class="form-control" accept="image/*" style="flex:1;">
                <div class="image-preview-area"></div>
            </div>
        </div>
    </div>
</template>

@endsection

@section('scripts')
<script>
let secIndex = 0;
const container = document.getElementById('sections-container');
const template = document.getElementById('section-template').innerHTML;

// Preload existing sections if Editing
const existingSections = @json(isset($post) ? $post->sections : []);

let editors = {}; // Keep track of instances

function initEditors() {
    document.querySelectorAll('.rich-editor').forEach(el => {
        // Prevent double init by checking our tracking object
        if (editors[el.name]) return;
        
        ClassicEditor
            .create(el, {
                toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote' ]
            })
            .then(editor => {
                editor.model.document.on('change:data', () => {
                    el.value = editor.getData();
                });
                editors[el.name] = editor;
            })
            .catch(error => {
                console.error(error);
            });
    });
}

function addSection(data = null) {
    const isExisting = data !== null;
    const html = template
        .replace(/{INDEX}/g, secIndex)
        .replace(/{NUM}/g, secIndex + 1);
    
    const wrapper = document.createElement('div');
    wrapper.innerHTML = html;
    const el = wrapper.firstElementChild;
    container.appendChild(el);

    // Populate if existing data
    if (isExisting) {
        el.querySelector(`input[name="sections[${secIndex}][id]"]`).value = data.id;
        el.querySelector(`input[name="sections[${secIndex}][heading]"]`).value = data.heading || '';
        el.querySelector(`textarea[name="sections[${secIndex}][content]"]`).value = data.content || '';
        
        if (data.image_path) {
            const previewArea = el.querySelector('.image-preview-area');
            previewArea.innerHTML = `
                <div style="display:flex; align-items:center; gap:1rem;">
                    <img onerror="this.outerHTML='💊'" src="/storage/${data.image_path}" class="file-preview" style="height:50px; width:auto;">
                    <label style="color:#f87171; font-size:0.8rem; cursor:pointer;">
                        <input type="checkbox" name="sections[${secIndex}][remove_image]" value="1"> Remove Image
                    </label>
                </div>
            `;
        }
    }

    // Initialize tinyMCE for the newly added textarea
    // Needs a slight delay to ensure DOM is ready
    setTimeout(() => { initEditors(); }, 100);

    secIndex++;
}

function removeSection(idx) {
    if(confirm('Remove this section?')) {
        const el = document.getElementById('sec-' + idx);
        
        // Destroy CKEditor instance if exists
        const textareaName = `sections[${idx}][content]`;
        if (editors[textareaName]) {
            editors[textareaName].destroy();
            delete editors[textareaName];
        }
        
        el.remove();
        
        // Re-number visible titles
        const titles = container.querySelectorAll('.b-section-title');
        titles.forEach((t, i) => t.innerText = `Section #${i+1}`);
    }
}

// Boot
document.addEventListener('DOMContentLoaded', () => {
    if (existingSections.length > 0) {
        existingSections.forEach(sec => addSection(sec));
    } else {
        addSection(); // Start with 1 blank section automatically
    }
});
</script>
@endsection
