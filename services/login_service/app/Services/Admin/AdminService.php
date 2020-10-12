<?php

/**
 * {@inheritdoc}
 */

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Validation\ValidationException;

class AdminService
{

    private $enumSuccess = 0;
    private $user;

    public function __construct()
    {
        $this->enumSuccess = app('config')->get("enum.common.log_status")['SUCCESS'];
    }

    /**
     * {@inheritdoc}
     */
    public function create($request)
    {

        DB::beginTransaction();
        try {

            if (!permissionLevelCheck('ADMIN_ONLY', $request->user()->role_id)) {
                throw new Exception("ACCESS_DENIED", getStatusCodes('UNAUTHORIZED'));
            }


            $request->validate([
                'name' => 'required|string|min:6|max:255',
                'role_id' => 'required|exists:roles,id',
                'email' => 'required|email|max:255',
                'password' => 'required|min:8',
                'device_name' => 'required',
            ]);

            $userCheck = User::where('email', $request->email)->first();

            if ($userCheck) {
                throw new Exception("EMAIL_ALREADY_IN_USE", getStatusCodes('EXCEPTION'));
            }



            $userInst = new User();
            $userInst->name = $request->name;
            $userInst->email = $request->email;
            $userInst->role_id = $request->role_id;
            $userInst->password = Hash::make($request->password);
            $userInst->created_at = now();
            $userInst->save();
            DB::commit();

            addToLog('User created: ' . $request->name, $this->enumSuccess);
            $roleData = roleData($request->role_id);
            $userInst->entered_password = $request->password;
            $userInst->role_data = $roleData;

            return response()->json([
                        'data' => $userInst,
                        'message' => 'USER_CREATE_OK'
            ]);
        } catch (Exception $exception) {
            DB::rollBack();
            addToLog($exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUsers($request)
    {
        try {

            if (!permissionLevelCheck('ADMINS_ONLY', $request->user()->role_id)) {
                throw new Exception("ACCESS_DENIED", getStatusCodes('UNAUTHORIZED'));
            }

            if (permissionLevelCheck('ADMIN_ONLY', $request->user()->role_id)) {
                $roles = User::orderBy('created_at', 'desc')
                        ->where('id', '!=', $request->user()->id)
                        ->select(['id', 'role_id', 'email', 'name'])
                        ->with('role:id,role_name')
                        ->paginate(getGlobalSettingByName('DEFAULT_ITEMS_PER_PAGE'));
            }

            if (permissionLevelCheck('ADMIN_ASSIST_ONLY', $request->user()->role_id)) {
                $roles = User::orderBy('created_at', 'desc')
                        ->where('role_id', '!=', roleNames('ADMIN')->id)
                        ->where('id', '!=', $request->user()->id)
                        ->select(['id', 'role_id', 'email', 'name'])
                        ->with('role:id,role_name')
                        ->paginate(getGlobalSettingByName('DEFAULT_ITEMS_PER_PAGE'));
            }

            if (sizeof($roles) == 0) {

                // if no roles send empty array
                return response()->json([
                            'message' => 'GET_USERS_OK',
                            'data' => []
                ]);
            }

            addToLog('view all data users', $this->enumSuccess);

            return response()->json([
                        'message' => 'GET_USERS_OK',
                        'data' => $roles->toArray()
            ]);
        } catch (Exception $exception) {
            addToLog($exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUserById($id)
    {
        try {
            if (!isset($id)) {
                throw new Exception("USER_ID_REQUIRED", getStatusCodes('EXCEPTION'));
            }

            $idValidation = checkDataType($id);

            if ($idValidation instanceof Exception) {
                throw new Exception($idValidation->getMessage(), $idValidation->getCode());
            }

            $role = User::where('id', '=', $id)
                    ->select(['id', 'role_id', 'email', 'name'])
                    ->with('role:id,role_name')
                    ->first();

            if (!$role) {

                throw new Exception("USER_NOT_AVAILABLE_BY_ID", getStatusCodes('EXCEPTION'));
            }

            addToLog('view data of the given role ' . $id, $this->enumSuccess);

            return response()->json([
                        'message' => 'GET_USER_BY_ID_OK',
                        'data' => $role
            ]);
        } catch (Exception $exception) {

            addToLog($exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getByName($name, $returnType = false)
    {
        try {
            if (!isset($name)) {
                throw new Exception("ROLE_NAME_REQUIRED", getStatusCodes('EXCEPTION'));
            }

            $idValidation = checkDataType($name, 'string');

            if ($idValidation instanceof Exception) {
                throw new Exception($idValidation->getMessage(), $idValidation->getCode());
            }

            $roleExist = Role::where('role_name', '=', $name)
                    ->first();

            if (!$roleExist) {
                throw new Exception("ROLE_NOT_EXIST", getStatusCodes('EXCEPTION'));
            }

            addToLog('view data of the given role ' . $name, $this->enumSuccess);

            if ($returnType) {
                return $roleExist;
            }
            return response()->json([
                        'message' => 'GET_ROLE_OK',
                        'data' => $roleExist
            ]);
        } catch (Exception $exception) {

            addToLog($exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function adminAssistUpdate($request, $id)
    {

        DB::beginTransaction();
        try {

            $idValidation = checkDataType($id);

            if ($idValidation instanceof Exception) {
                throw new Exception($idValidation->getMessage(), $idValidation->getCode());
            }

            dd($id);
            $request->validate([
                'name' => 'required|string|min:6|max:255',
                'role_id' => 'required|exists:roles,id',
                'email' => 'required|email|max:255',
                'password' => 'required|min:8',
                'device_name' => 'required',
            ]);


            $userExist = User::where('id', '!=', $id)
//                    ->where('role_name', '=', $request->role_name)
                    ->first();

            if (!$userExist) {
                throw new Exception("USER_NOT_EXIST", getStatusCodes('EXCEPTION'));
            }


            $userExist->name = $request->name;
            $userExist->email = $request->email;
            $userExist->role_id = $request->role_id;
            $userExist->password = Hash::make($request->password);
            $userExist->created_at = now();
            $userExist->save();
            DB::commit();

            addToLog('User update name: ' . $request->name, $this->enumSuccess);

            return response()->json([
                        'data' => $userExist,
                        'message' => 'USER_UPDATE_OK'
            ]);
        } catch (Exception $exception) {
            DB::rollBack();
            dd($exception);
            addToLog($exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $idValidation = checkDataType($id);

            if ($idValidation instanceof Exception) {
                throw new Exception($idValidation->getMessage(), $idValidation->getCode());
            }

            $roleExist = Role::where([['id', '=', $id]])->first();

            if (!$roleExist) {
                throw new Exception("ROLE_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }


            $children = Role::where('id', '=', $id)
                    ->with('users')
                    ->get();

            $totalNumberOfChild = 0;
            foreach ($children as $child) {
                $totalNumberOfChild += sizeof($child->getRelations()["users"]);
            }


            if ($totalNumberOfChild > 0) {
                throw new Exception("ROLE_CAN_NOT_DELETE_RELATION_DATA_AVAILABLE", getStatusCodes('EXCEPTION'));
            }

//            $roleExist->delete();
//            DB::commit();

            addToLog('role delete success id - ' . $id, $this->enumSuccess);
            return response()->json([
                        'message' => 'ROLE_DELETE_OK'
            ]);
        } catch (Exception $exception) {
            DB::rollBack();
            addToLog($exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], $exception->getCode());
        }
    }

}
