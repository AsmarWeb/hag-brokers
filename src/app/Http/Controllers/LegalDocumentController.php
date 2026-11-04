<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class LegalDocumentController extends Controller
{
    public function api(Request $r, string $slug)
    {
        $row = $this->document($slug);
        $lang = app()->getLocale() === 'ar' ? 'ar' : 'en';

        return response()->json(['data' => ['slug' => $row->slug, 'title' => $row->{'title_'.$lang}, 'body' => $row->{'body_'.$lang}, 'version' => (int) $row->version, 'updated_at' => $row->updated_at]]);
    }

    private function document(string $slug)
    {
        abort_unless(in_array($slug, ['privacy', 'terms'], true), 404);

        return DB::table('platform_legal_documents')->where('slug', $slug)->where('published', true)->firstOrFail();
    }

    public function show(Request $r, string $slug)
    {
        $row = $this->document($slug);
        $lang = app()->getLocale() === 'ar' ? 'ar' : 'en';

        return response()->view('legal.show', ['document' => $row, 'language' => $lang])->header('Cache-Control', 'no-store');
    }
}
