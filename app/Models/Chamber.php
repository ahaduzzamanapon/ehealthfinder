<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Chamber extends Model {
    public $timestamps = false;
    protected $guarded = [];
    public function doctor() { return $this->belongsTo(Doctor::class); }
    public function hospital() { return $this->belongsTo(Hospital::class); }
}