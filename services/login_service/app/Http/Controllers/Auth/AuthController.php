<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Auth\AuthService;

class AuthController extends Controller
{

    private $user;
    private $authService;

    public function __construct(Request $request)
    {
        // $this->user = $request->user();
        $this->authService = new AuthService();
    }

    public function login(Request $request)
    {
        return $this->authService->login($request);
    }

    public function getUserData(Request $request)
    {
        return $this->authService->getUserDetails($request);
    }

    public function logout(Request $request)
    {
        return $this->authService->logout($request);
    }

    public function registerSeller(Request $request)
    {
        return $this->authService->registerSeller($request);
    }

    public function registerBuyer(Request $request)
    {
        return $this->authService->registerBuyer($request);
    }

    public function verifySeller(Request $request)
    {
        return $this->authService->verifySeller($request);
    }

    public function getVerifySellerStatus(Request $request)
    {
        return $this->authService->getVerifySellerStatus($request);
    }

}
