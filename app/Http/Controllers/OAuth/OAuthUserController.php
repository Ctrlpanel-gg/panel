<?php

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OAuthUserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->tokenCan('identify')) {
            return $request->user();
        } else {
            return response(['message' => 'Unauthorized'], 401);
        }
    }
}
