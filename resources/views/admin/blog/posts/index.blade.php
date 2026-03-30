@extends('admin.layouts.app')
@section('title', 'Blog Posts')
@section('page-title', '📝 Blog Posts')

@section('content')
<div class="search-bar">
    <form method="GET" style="display:flex; gap:0.75rem; flex:1; align-items:center;">
        <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Search blog titles...">
        <button class="btn btn-primary" type="submit">🔍 Search</button>
        @if($q)<a href="{{ route('admin.blog.posts.index') }}" class="btn btn-outline">✕ Clear</a>@endif
    </form>
    <a href="{{ route('admin.blog.posts.create') }}" class="btn btn-primary">+ Create New Post</a>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h3>All Posts <span class="badge badge-primary">{{ $posts->total() }}</span></h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Title / Slug</th>
                <th>Category</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($posts as $post)
            <tr>
                <td style="width:60px;">
                    @if($post->featured_image)
                    <img src="{{ Storage::url($post->featured_image) }}" alt="thumb" style="width:50px; height:50px; object-fit:cover; border-radius:8px;">
                    @else
                    <div style="width:50px; height:50px; background:#334155; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:0.7rem;">No Img</div>
                    @endif
                </td>
                <td>
                    <div style="font-weight:600; max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $post->title }}</div>
                    <div style="font-size:0.75rem; color:#94a3b8; margin-top:2px;">/{{ $post->slug }}</div>
                </td>
                <td>
                    @if($post->category)
                    <span class="badge badge-primary">{{ $post->category->name }}</span>
                    @else <span style="color:#64748b">—</span> @endif
                </td>
                <td>
                    @if($post->is_published)
                        <span class="badge badge-success">Published</span>
                    @else
                        <span class="badge badge-warning">Draft</span>
                    @endif
                </td>
                <td style="font-size:0.8rem; color:#94a3b8;">{{ $post->created_at->format('d M, Y') }}</td>
                <td>
                    <div style="display:flex; gap:0.5rem;">
                        <a href="{{ route('admin.blog.posts.edit', $post) }}" class="btn btn-outline btn-sm">✏️</a>
                        <a href="{{ url('/' . $post->slug) }}" target="_blank" class="btn btn-outline btn-sm">👁️</a>
                        <form method="POST" action="{{ route('admin.blog.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post permanently?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center; padding:2rem; color:#64748b;">No posts created yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination-wrap">{{ $posts->links() }}</div>
</div>
@endsection
