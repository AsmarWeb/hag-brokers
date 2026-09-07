<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $r)
    {
        return response()->json(['success' => true, 'data' => $r->user()->load('customer')]);
    }
    public function update(Request $r)
    {
        $d = $r->validate(['name' => 'sometimes|string|max:120', 'phone' => 'nullable|string|max:40']);
        $r->user()->update($d);
        if ($r->user()->customer && $r->input('company_name')) $r->user()->customer->update(['company_name' => $r->input('company_name')]);
        return response()->json(['success' => true, 'data' => $r->user()->fresh()->load('customer')]);
    }
}
