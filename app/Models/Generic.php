<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Generic extends Model {
    public $timestamps = false;
    protected $guarded = [];
    public function brands() { return $this->hasMany(Brand::class); }
}