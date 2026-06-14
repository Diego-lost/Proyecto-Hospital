<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\AuthRedirect;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function csrf(): JsonResponse
    {
        return response()->json(['token' => csrf_token()]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['user' => null]);
        }

        return response()->json(['user' => AuthRedirect::userPayload($user)]);
    }
}
