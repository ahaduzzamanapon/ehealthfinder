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

    public function reviews() { return $this->morphMany(Review::class, 'reviewable'); }
    public function getAverageRatingAttribute() { return round($this->reviews()->where('is_approved', true)->avg('rating') ?: 0, 1); }
    public function getReviewCountAttribute() { return $this->reviews()->where('is_approved', true)->count(); }
}
