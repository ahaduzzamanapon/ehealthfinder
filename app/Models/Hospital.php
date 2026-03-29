<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Hospital extends Model {
    public $timestamps = false;
    protected $guarded = [];
    public function location() { return $this->belongsTo(Location::class); }
}