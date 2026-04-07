<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Repositories\User\UserRepository;

class AuthService
{
    protected $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }
    public function login(array $data)
    {
        $user = $this->userRepo->findByEmail($data['email']);
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return false;
        }
        $token = $user->createToken($user->is_admin ? 'admin-token' : 'user-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function register(array $data)
    {
        $user = $this->userRepo->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        if (!$user) {
            return false;
        }
        $token = $user->createToken($user->is_admin ? 'admin-token' : 'user-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
