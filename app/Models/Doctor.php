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
}