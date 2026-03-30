<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Support\Str;

// Create category if not exists
$cat = BlogCategory::firstOrCreate(['slug' => 'cardiology'], ['name' => 'Cardiology & Heart']);

$post = new BlogPost();
$post->title = '5 Proven Ways to Keep Your Heart Healthy Naturally';
$post->slug = Str::slug($post->title) . '-' . uniqid();
$post->blog_category_id = $cat->id;
$post->excerpt = 'Your heart is the engine of your body. Discover 5 scientifically proven ways to keep cardiovascular diseases at bay and live a healthier, happier life.';
$post->author_name = 'Dr. Amina Rahman';
$post->is_published = true;
$post->tags = 'heart, cardiology, healthy living, diet, exercise';
$post->seo_title = 'How to Keep Heart Healthy: 5 Proven Ways';
$post->seo_description = 'Learn 5 medically proven, natural ways to keep your heart healthy and young. Discover tips on diet, exercise, and stress management for cardiology health.';
$post->save();

// Add sections
$post->sections()->create([
    'order_index' => 0,
    'heading' => '1. Eat a Heart-Healthy Diet',
    'content' => '<p>Eating a healthy diet is one of the most effective ways to lower your risk of cardiovascular disease. Focus on incorporating fresh <strong>fruits, vegetables, whole grains, and lean proteins</strong> into your meals.</p><ul><li>Avoid trans fats and limit saturated fats.</li><li>Reduce sodium (salt) intake.</li><li>Include Omega-3 fatty acids like salmon or walnuts.</li></ul>',
]);

$post->sections()->create([
    'order_index' => 1,
    'heading' => '2. Exercise Regularly',
    'content' => '<p>Aim for at least <strong>150 minutes of moderate-intensity aerobic activity</strong> per week. Whether it\'s brisk walking, swimming, or cycling, moving your body strengthens the heart muscle and improves blood circulation.</p><p><em>Tip: You don\'t need to do it all at once; 30 minutes a day, 5 days a week is perfect!</em></p>',
]);

$post->sections()->create([
    'order_index' => 2,
    'heading' => '3. Manage Your Stress Levels',
    'content' => '<p>Chronic stress can severely impact your heart. When you are stressed, your body releases cortisol, which can raise blood pressure and cholesterol levels.</p><p style="padding-left:1em; border-left:4px solid #4f46e5; font-style:italic;">"Meditation, deep breathing exercises, and yoga are excellent tools to manage daily stress. Never underestimate the power of a relaxed mind!"</p>',
]);

$post->sections()->create([
    'order_index' => 3,
    'heading' => '4. Get Enough Quality Sleep',
    'content' => '<p>A good night’s sleep isn’t just essential for resting your brain—it\'s critical for your heart. People who don\'t get enough sleep have a much higher risk of cardiovascular disease—regardless of age, weight, smoking, and exercise habits.</p><strong>Aim for 7 to 8 hours of quality sleep every night.</strong>',
]);

echo "Blog post created successfully: " . $post->title;
