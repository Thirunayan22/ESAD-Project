<?php

namespace App\Services\Auth;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\User;
use App\Models\SellerDetail;

class AuthService
{

    private $enumSuccess = 0;

    public function __construct()
    {
        $this->enumSuccess = app('config')->get("enum.common.log_status")['SUCCESS'];
    }

    public function registerSeller(Request $request)
    {
        DB::beginTransaction();
        try {

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'password' => 'required|min:8',
                'device_name' => 'required',
            ]);

            $userCheck = User::where('email', $request->email)->first();
            if ($userCheck) {
                throw ValidationException::withMessages([
                    'email' => ['You can not use this email, use a different one.'],
                ]);
            }

            $userInst = new User();
            $userInst->name = $request->name;
            $userInst->email = $request->email;
            $userInst->role_id = roleNames('SELLER')->id;
            $userInst->password = Hash::make($request->password);
            $userInst->created_at = now();
            $userInst->save();
            DB::commit();
            $token = $userInst->createToken($request->device_name)->plainTextToken;

            $response = [
                'user' => $userInst,
                'token' => $token
            ];
            return response()->json([
                        'data' => $response,
                        'message' => 'REGISTERED_AS_SELLER_OK'
            ]);
        } catch (Exception $exception) {
            DB::rollBack();
            addToLog($exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], $exception->getCode() == 0 ? getStatusCodes('VALIDATION_ERROR') : $exception->getCode());
        }
    }

    public function registerBuyer(Request $request)
    {
        DB::beginTransaction();
        try {

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'password' => 'required|min:8',
                'device_name' => 'required',
            ]);

            $userCheck = User::where('email', $request->email)->first();
            if ($userCheck) {
                throw ValidationException::withMessages([
                    'email' => ['You can not use this email, use a different one.'],
                ]);
            }

            $userInst = new User();
            $userInst->name = $request->name;
            $userInst->email = $request->email;
            $userInst->role_id = roleNames('BUYER')->id;
            $userInst->password = Hash::make($request->password);
            $userInst->created_at = now();
            $userInst->save();
            DB::commit();
            $token = $userInst->createToken($request->device_name)->plainTextToken;

            $response = [
                'user' => $userInst,
                'token' => $token
            ];
            return response()->json([
                        'data' => $response,
                        'message' => 'REGISTERED_AS_BUYER_OK'
            ]);
        } catch (Exception $exception) {
            DB::rollBack();
            addToLog($exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], $exception->getCode() == 0 ? getStatusCodes('VALIDATION_ERROR') : $exception->getCode());
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];
        return response($response, 200);
    }

    public function getUserDetails(Request $request)
    {
        return $request->user();
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        $response = [
            'message' => 'logout success',
        ];
        return response($response, 200);
    }

    public function verifySeller(Request $request)
    {
        DB::beginTransaction();
        try {

            $user = $request->user();

            if (!$user) {
                throw new Exception("USER_NOT_EXISTS", getStatusCodes('EXCEPTION'));
            }


            if (!permissionLevelCheck('SELLER_ONLY', $request->user()->role_id)) {
                throw new Exception("ACCESS_DENIED", getStatusCodes('UNAUTHORIZED'));
            }

            $docExist = SellerDetail::where('document', '=', $request->br_doc)
                    ->where('verify_status', '=', 1)
                    ->first();

            if ($docExist) {
                throw new Exception("ALREADY_VERIFIED", getStatusCodes('EXCEPTION'));
            }

            $detailInst = New SellerDetail();
            $detailInst->user_id = $user->id;
            $detailInst->document = $request->br_doc;
            $detailInst->created_at = now();
            $detailInst->save();
            DB::commit();

            addToLog('seller verification step 2 completed: ' . $user->id, $this->enumSuccess);

            return response()->json([
                        'data' => $detailInst,
                        'message' => 'DOCUMENT_SUBMIT_OK'
            ]);
        } catch (Exception $exception) {
            DB::rollBack();
            addToLog($exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], $exception->getCode());
        }
    }

    public function getVerifySellerStatus(Request $request)
    {
        DB::beginTransaction();
        try {

            $user = $request->user();

            if (!$user) {
                throw new Exception("USER_NOT_EXISTS", getStatusCodes('EXCEPTION'));
            }

            if (!permissionLevelCheck('SELLER_ONLY', $request->user()->role_id)) {
                throw new Exception("ACCESS_DENIED", getStatusCodes('UNAUTHORIZED'));
            }

            $docExist = SellerDetail::where('user_id', '=', $user->id)
                    ->first();

            if (!$docExist) {
                throw new Exception("CANNOT_FIND_VERIFICATION_DETAILS", getStatusCodes('EXCEPTION'));
            }

            addToLog('seller verification step 2 checked: ' . $user->id, $this->enumSuccess);

            return response()->json([
                        'data' => $docExist,
                        'message' => 'GET_VERIFICATION_DETAILS_OK'
            ]);
        } catch (Exception $exception) {
            DB::rollBack();
            addToLog($exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], $exception->getCode());
        }
    }

}
