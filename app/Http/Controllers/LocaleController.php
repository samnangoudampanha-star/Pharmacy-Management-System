<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LocaleController extends Controller
{
    public function switch(Request $request): Response
    {
        $available = array_keys(config('app.available_locales', ['en' => 'English', 'km' => 'ខ្មែរ']));

        $data = $request->validate([
            'locale' => ['required', 'string', 'in:'.implode(',', $available)],
        ]);

        $request->session()->put('locale', $data['locale']);
        app()->setLocale($data['locale']);

        return response()->noContent();
    }
}
