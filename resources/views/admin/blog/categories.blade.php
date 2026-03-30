@extends('admin.layouts.app')
@section('title', 'Blog Categories')
@section('page-title', '📂 Blog Categories')

@section('content')
<div style="display:grid; grid-template-columns:1fr 320px; gap:1.5rem; align-items:flex-start;">

    {{-- List --}}
    <div class="admin-card" style="margin-bottom:0">
        <div class="admin-card-header">
            <h3>All Blog Categories <span class="badge badge-primary">{{ $categories->total() }}</span></h3>
            <form method="GET" style="display:flex; gap:0.5rem;">
                <input type="text" name="q" value="{{ $q }}" class="form-control" style="width:200px" placeholder="Search...">
                <button class="btn btn-outline btn-sm">🔍</button>
            </form>
        </div>
        <table class="admin-table">
            <thead><tr><th>ID</th><th>Category Name</th><th>Posts</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($categories as $cat)
                <tr>
                    <td style="color:#64748b; font-size:0.8rem;">#{{ $cat->id }}</td>
                    <td style="font-weight:600">{{ $cat->name }}</td>
                    <td><span class="badge badge-primary">{{ $cat->posts_count }}</span></td>
                    <td>
                        <div style="display:flex; gap:0.5rem;">
                            <button onclick="editCategory({{ $cat->id }}, '{{ addslashes($cat->name) }}')" class="btn btn-outline btn-sm">✏️ Edit</button>
                            <form method="POST" action="{{ route('admin.blog.categories.destroy', $cat) }}" onsubmit="return confirm('Delete {{ $cat->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center; padding:2rem; color:#64748b;">No categories found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination-wrap">{{ $categories->links() }}</div>
    </div>

    {{-- Add / Edit Form --}}
    <div>
        <div class="admin-card" style="margin-bottom:0" id="form-card">
            <h3 id="form-title" style="margin-bottom:1.25rem; font-size:1rem;">➕ Add Category</h3>
            <form id="category-form" method="POST" action="{{ route('admin.blog.categories.store') }}">
                @csrf
                <input type="hidden" id="form-method" name="_method" value="">
                <div class="form-group">
                    <label class="form-label">Category Name *</label>
                    <input type="text" name="name" id="category-name" class="form-control" placeholder="Health, Fitness..." required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">💾 Save</button>
                <button type="button" onclick="resetForm()" class="btn btn-outline" style="width:100%; margin-top:0.5rem;">Cancel</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function editCategory(id, name) {
    document.getElementById('form-title').innerText = '✏️ Edit Category';
    document.getElementById('category-name').value = name;
    document.getElementById('form-method').value = 'PUT';
    document.getElementById('category-form').action = '/admin/blog/categories/' + id;
}
function resetForm() {
    document.getElementById('form-title').innerText = '➕ Add Category';
    document.getElementById('category-name').value = '';
    document.getElementById('form-method').value = '';
    document.getElementById('category-form').action = '{{ route('admin.blog.categories.store') }}';
}
</script>
@endsection
