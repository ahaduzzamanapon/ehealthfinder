<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Specialty extends Model {
    public $timestamps = false;
    protected $table = 'specialties';
    protected $guarded = [];
    public function doctors() { return $this->hasMany(Doctor::class); }
}