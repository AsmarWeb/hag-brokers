<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\{User, Customer};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $r)
    {
        $d = $r->validate(['email' => 'required|email', 'password' => 'required']);
        $u = User::where('email', $d['email'])->first();
        if (!$u || !Hash::check($d['password'], $u->password)) return response()->json(['success' => false, 'message' => 'Invalid credentials'], 422);
        return response()->json(['success' => true, 'token' => $u->createToken('mobile')->plainTextToken, 'user' => $u]);
    }
    public function register(Request $r)
    {
        $d = $r->validate(['name' => 'required', 'email' => 'required|email|unique:users,email', 'password' => 'required|min:8|confirmed', 'company_name' => 'required']);
        $c = Customer::create(['company_name' => $d['company_name']]);
        $u = User::create(['name' => $d['name'], 'email' => $d['email'], 'password' => Hash::make($d['password']), 'customer_id' => $c->id, 'role' => 'customer']);
        return response()->json(['success' => true, 'token' => $u->createToken('mobile')->plainTextToken, 'user' => $u], 201);
    }
    public function logout(Request $r)
    {
        $r->user()->currentAccessToken()?->delete();
        return response()->json(['success' => true]);
    }
}
