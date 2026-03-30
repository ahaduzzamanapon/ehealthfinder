<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Doctor extends Model {
    public $timestamps = false;
    protected $guarded = [];
    public function location() { return $this->belongsTo(Location::class); }
    public function specialty() { return $this->belongsTo(Specialty::class); }
    public function chambers() { return $this->hasMany(Chamber::class); }
    
    public function getSlugAttribute() {
        return Str::slug($this->name);
    }
    
    public function getSeoSlugAttribute() {
        $parts = [];
        if ($this->relationLoaded('specialty') && $this->specialty) {
            $parts[] = Str::slug($this->specialty->name);
        }
        if ($this->relationLoaded('location') && $this->location) {
            $parts[] = 'in';
            $parts[] = Str::slug($this->location->name);
        }
        $parts[] = Str::slug($this->name);
        $parts[] = $this->id;
        return implode('-', $parts);
    }
}