<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Brand extends Model {
    public $timestamps = false;
    protected $guarded = [];
    public function generic() { return $this->belongsTo(Generic::class); }
    
    public function getSlugAttribute() {
        return Str::slug($this->name . ' ' . $this->dosage_form);
    }

    public function reviews() { return $this->morphMany(Review::class, 'reviewable'); }
    public function getAverageRatingAttribute() { return round($this->reviews()->where('is_approved', true)->avg('rating') ?: 0, 1); }
    public function getReviewCountAttribute() { return $this->reviews()->where('is_approved', true)->count(); }
}