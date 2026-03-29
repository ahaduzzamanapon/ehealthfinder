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
}