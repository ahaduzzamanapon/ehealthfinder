<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'reviewable_type' => 'required|string',
            'reviewable_id'   => 'required|integer',
            'author_name'     => 'required|string|max:255',
            'author_email'    => 'nullable|email|max:255',
            'rating'          => 'required|integer|min:1|max:5',
            'body'            => 'required|string',
        ]);

        // Dynamically find the model
        $modelClass = "App\\Models\\" . $request->reviewable_type;
        if (!class_exists($modelClass)) {
            return back()->with('error', 'Invalid target type.');
        }

        $model = $modelClass::findOrFail($request->reviewable_id);

        $model->reviews()->create([
            'author_name'  => $request->author_name,
            'author_email' => $request->author_email,
            'rating'       => $request->rating,
            'body'         => $request->body,
            'is_approved'  => true, // Auto-approve for now
        ]);

        return back()->with('success', 'Thank you! Your review has been submitted.');
    }
}
