<?php
// app/Http/Controllers/LanguageController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    protected array $supported = ['en', 'bn', 'ar'];

    public function switch(string $lang)
    {
        if (in_array($lang, $this->supported)) {
            session()->put('locale', $lang);
        }

        return redirect()->back();
    }
}
