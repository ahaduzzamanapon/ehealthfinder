<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $fillable = [
        'blog_category_id',
        'title',
        'slug',
        'excerpt',
        'featured_image',
        'author_name',
        'is_published',
        'seo_title',
        'seo_description',
        'tags'
    ];

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function sections()
    {
        return $this->hasMany(BlogPostSection::class)->orderBy('order_index');
    }
}
