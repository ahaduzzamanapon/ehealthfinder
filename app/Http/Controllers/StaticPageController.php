<?php

namespace App\Http\Controllers;

class StaticPageController extends Controller
{
    public function about()    { return view('pages.about'); }
    public function privacy()  { return view('pages.privacy'); }
    public function disclaimer(){ return view('pages.disclaimer'); }
    public function terms()    { return view('pages.terms'); }
    public function refund()   { return view('pages.refund'); }
}
