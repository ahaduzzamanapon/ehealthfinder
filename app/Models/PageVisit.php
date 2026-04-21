<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageVisit extends Model
{
    protected $fillable = [
        'ip', 'url', 'user_agent', 'visited_date',
        'country', 'country_code', 'city', 'page_type',
    ];
    protected $casts = ['visited_date' => 'date'];
}
