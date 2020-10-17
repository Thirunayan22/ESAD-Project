<?php

/**
 * {@inheritdoc}
 */

namespace App\Services\Auth;

use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Exception;

class RolesService
{

    private $enumSuccess = 0;

    public function __construct()
    {
        $this->enumSuccess = app('config')->get("enum.common.log_status")['SUCCESS'];
    }

    /**
     * {@inheritdoc}
     */
    public function create(RolesRequest $request)
    {

        DB::beginTransaction();
        try {

            $roleExist = Role::where('role_name', '=', $request->role_name)
                    ->first();

            if ($roleExist) {
                throw new Exception("ROLE_EXISTS", getStatusCodes('EXCEPTION'));
            }

            $roleInst = New Role();
            $roleInst->role_name = $request->role_name;
            $roleInst->created_at = now();
            $roleInst->save();
            DB::commit();

            addToLog('Role created role_name: ' . $request->role_name, $this->enumSuccess);

            return response()->json([
                        'data' => $roleInst,
                        'message' => 'ROLE_CREATE_OK'
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
    public function getRoles()
    {
        try {
            $roles = Role::orderBy('created_at', 'desc')
                    ->select(['id', 'role_name'])
                    ->paginate(getGlobalSettingByName('DEFAULT_ITEMS_PER_PAGE'));

            if (sizeof($roles) == 0) {

                // if no roles send empty array
                return response()->json([
                            'message' => 'GET_ROLES_OK',
                            'data' => []
                ]);
            }

//            addToLog('view all data roles', $this->enumSuccess);

            return response()->json([
                        'message' => 'GET_ROLES_OK',
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
    public function getById($id, $returnType = false)
    {
        try {
            if (!isset($id)) {
                throw new Exception("ROLE_ID_REQUIRED", getStatusCodes('EXCEPTION'));
            }

            $idValidation = checkDataType($id);

            if ($idValidation instanceof Exception) {
                throw new Exception($idValidation->getMessage(), $idValidation->getCode());
            }

            $role = Role::where('id', '=', $id)
                    ->select(['id', 'role_name'])
                    ->first();

            if (!$role) {
                throw new Exception("ROLE_NOT_AVAILABLE_BY_ID", getStatusCodes('EXCEPTION'));
            }


            addToLog('view data of the given role ' . $id, $this->enumSuccess);

            if ($returnType) {
                return $role;
            }

            return response()->json([
                        'message' => 'GET_ROLE_BY_ID_OK',
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
    public function update(RolesRequest $request, $id)
    {

        DB::beginTransaction();
        try {

            $idValidation = checkDataType($id);

            if ($idValidation instanceof Exception) {
                throw new Exception($idValidation->getMessage(), $idValidation->getCode());
            }

            $roleInst = Role::where([['id', '=', $id]])->first();

            if (!$roleInst) {
                throw new Exception("ROLE_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }



            $roleExist = Role::where('id', '!=', $id)
                    ->where('role_name', '=', $request->role_name)
                    ->first();

            if ($roleExist) {
                throw new Exception("ROLE_EXIST", getStatusCodes('EXCEPTION'));
            }


            $roleInst->role_name = $request->role_name;
            $roleInst->save();

            DB::commit();

            addToLog('Role update role_name: ' . $request->role_name . ' ,role_desc:' . $request->role_desc, $this->enumSuccess);

            return response()->json([
                        'data' => $roleInst,
                        'message' => 'ROLE_UPDATE_OK'
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
