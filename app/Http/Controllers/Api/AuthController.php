<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthUser\LoginRequest;
use App\Http\Requests\AuthUser\RegisterRequest;
use App\Services\ResponseService;
use App\Http\Resources\User\RegisterResource;
use App\Http\Resources\User\LoginResource;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    public function __construct(private ResponseService $responseService, private AuthService $authService)
    {
        $this->responseService = $responseService;
        $this->authService = $authService;
    }


    /**
     * POST /api/register
     */
    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());
        if (!$result) {
            return $this->responseService->json('Failed!', [], 401, ['error' => ['Failed to register']]);
        }
        $user = new RegisterResource($result['user']);
        $token = $result['token'];
        return $this->responseService->json('Success!', ['user' => $user, 'token' => $token], 200);
    }

    /**
     * POST /api/login
     */
    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());
        if (!$result) {
            return $this->responseService->json('Failed!', [], 401, ['error' => ['Invalid credentials']]);
        }
        $user = new LoginResource($result['user']);
        $token = $result['token'];
        return $this->responseService->json('Success!', ['user' => $user, 'token' => $token], 200);
    }

    /**
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
