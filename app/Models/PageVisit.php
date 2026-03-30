<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageVisit extends Model
{
    protected $fillable = ['ip', 'url', 'user_agent', 'visited_date'];
    protected $casts    = ['visited_date' => 'date'];
}
