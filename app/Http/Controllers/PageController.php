<?php

namespace App\Http\Controllers;

use Modules\Core\Entities\Page;

class PageController extends Controller
{
    public function show($slug)
    {
        $page = Page::where('slug', $slug)->published()->firstOrFail();

        return view('pages.show', compact('page'));
    }
}
