<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'author_name',
        'author_email',
        'rating',
        'body',
        'is_approved',
    ];

    /**
     * Get the parent reviewable model (doctor, brand, or post).
     */
    public function reviewable()
    {
        return $this->morphTo();
    }
}
