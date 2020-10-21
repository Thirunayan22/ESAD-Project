<?php

/**
 * {@inheritdoc}
 */

namespace App\Services\Inventory;

use Illuminate\Support\Facades\DB;
use Exception;
use App\Services\Auth\ConnectAuthService;
use App\Models\PrProduct;

class ProductsService
{

    private $enumSuccess = 0;

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

            $authApi = new ConnectAuthService();
            $userData = $authApi->getUserDetails($request);

            if (!permissionLevelCheck('SELLER_ONLY', $userData['role_id'])) {
                throw new Exception("SELLER_ONLY", getStatusCodes('UNAUTHORIZED'));
            }
            $userId = $userData['id'];

            $request->validate([
                'category_id' => 'required|exists:pr_category,id',
                'product_name' => 'required|string|min:6|max:255',
                'short_desc' => 'required|string|min:10|max:255',
                'long_desc' => 'required|string|min:10|max:255'
            ]);

            $productInst = New PrProduct();
            $productInst->owner_id = $userId;
            $productInst->category_id = $request->category_id;
            $productInst->product_name = $request->product_name;
            $productInst->short_desc = $request->short_desc;
            $productInst->long_desc = $request->long_desc;
            $productInst->created_at = now();
            $productInst->save();
            DB::commit();

            addToLog('product created role_name: ' . $request->product_name, $this->enumSuccess);

            return response()->json([
                        'data' => $productInst,
                        'message' => 'PRODUCT_CREATE_OK'
            ]);
        } catch (Exception $exception) {
            DB::rollBack();
//            dd($exception);
            addToLog($exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], $exception->getCode() == 0 ? getStatusCodes('VALIDATION_ERROR') : $exception->getCode());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllProducts()
    {
        try {
            $products = PrProduct::orderBy('created_at', 'desc')
                    ->select(['id', 'product_name', 'category_id'])
                    ->with('pr_category:id,category_name')
                    ->with('pr_product_infos:product_id,price,quatity')
                    ->paginate(getGlobalSettingByName('DEFAULT_ITEMS_PER_PAGE'));

            if (sizeof($products) == 0) {

                // if no roles send empty array
                return response()->json([
                            'message' => 'GET_PRODUCTS_OK',
                            'data' => []
                ]);
            }

//            addToLog('view all data roles', $this->enumSuccess);

            return response()->json([
                        'message' => 'GET_PRODUCTS_OK',
                        'data' => $products->toArray()
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
                throw new Exception("PRODUCT_ID_REQUIRED", getStatusCodes('EXCEPTION'));
            }

            $idValidation = checkDataType($id);

            if ($idValidation instanceof Exception) {
                throw new Exception($idValidation->getMessage(), $idValidation->getCode());
            }

            $products = PrProduct::orderBy('created_at', 'desc')
                    ->select(['id', 'product_name', 'category_id'])
                    ->with('pr_category:id,category_name')
                    ->with('pr_product_infos:product_id,price,quatity')
                    ->where('id', '=', $id)
                    ->first();

            if (!$products) {
                throw new Exception("PRODUCT_NOT_AVAILABLE_BY_ID", getStatusCodes('EXCEPTION'));
            }


            addToLog('view data of the given product ' . $id, $this->enumSuccess);

            if ($returnType) {
                return $products;
            }

            return response()->json([
                        'message' => 'GET_PRODUCT_BY_ID_OK',
                        'data' => $products
            ]);
        } catch (Exception $exception) {

            addToLog($exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], $exception->getCode() == 0 ? getStatusCodes('VALIDATION_ERROR') : $exception->getCode());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getByName($name, $returnType = false)
    {
        try {
            if (!isset($name)) {
                throw new Exception("PRODUCT_NAME_REQUIRED", getStatusCodes('EXCEPTION'));
            }

            $idValidation = checkDataType($name, 'string');

            if ($idValidation instanceof Exception) {
                throw new Exception($idValidation->getMessage(), $idValidation->getCode());
            }

            $productExist = PrProduct::where('product_name', 'like', "%{$name}%")
                    ->select(['id', 'product_name', 'category_id'])
                    ->with('pr_category:id,category_name')
                    ->with('pr_product_infos:product_id,price,quatity')
                    ->get();

            if (!$productExist) {
                throw new Exception("PRODUCT_NAME_NOT_EXIST", getStatusCodes('EXCEPTION'));
            }

            addToLog('view data of the given product ' . $name, $this->enumSuccess);

            if ($returnType) {
                return $productExist;
            }
            return response()->json([
                        'message' => 'GET_PRODUCT_OK',
                        'data' => $productExist
            ]);
        } catch (Exception $exception) {
            dd($exception);
            addToLog($exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update($request, $id)
    {

        DB::beginTransaction();
        try {

            $idValidation = checkDataType($id);

            if ($idValidation instanceof Exception) {
                throw new Exception($idValidation->getMessage(), $idValidation->getCode());
            }

            $authApi = new ConnectAuthService();
            $userData = $authApi->getUserDetails($request);

            if (!permissionLevelCheck('SELLER_ONLY', $userData['role_id'])) {
                throw new Exception("SELLER_ONLY", getStatusCodes('UNAUTHORIZED'));
            }

            $userId = $userData['id'];

            $productInst = PrProduct::where('id', '=', $id)
                    ->where('owner_id', '=', $userId)
                    ->first();

            if (!$productInst) {
                throw new Exception("PRODUCT_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }



            $productExist = PrProduct::where('id', '!=', $id)
                    ->where('owner_id', '=', $userId)
                    ->where('product_name', '=', $request->product_name)
                    ->first();

            if ($productExist) {
                throw new Exception("PRODUCT_EXIST", getStatusCodes('EXCEPTION'));
            }



            $request->validate([
                'category_id' => 'required|exists:pr_category,id',
                'product_name' => 'required|string|min:6|max:255',
                'short_desc' => 'required|string|min:10|max:255',
                'long_desc' => 'required|string|min:10|max:255'
            ]);

            $productInst->owner_id = $userId;
            $productInst->category_id = $request->category_id;
            $productInst->product_name = $request->product_name;
            $productInst->short_desc = $request->short_desc;
            $productInst->long_desc = $request->long_desc;
            $productInst->save();

            DB::commit();

            addToLog('PRODUCT update product_name: ' . $request->product_name . ' ,short_desc:' . $request->short_desc, $this->enumSuccess);

            return response()->json([
                        'data' => $productInst,
                        'message' => 'PRODUCT_UPDATE_OK'
            ]);
        } catch (Exception $exception) {
            DB::rollBack();
            addToLog($exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], $exception->getCode() == 0 ? getStatusCodes('VALIDATION_ERROR') : $exception->getCode());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($request, $id)
    {
        DB::beginTransaction();
        try {
            $idValidation = checkDataType($id);

            if ($idValidation instanceof Exception) {
                throw new Exception($idValidation->getMessage(), $idValidation->getCode());
            }

            $authApi = new ConnectAuthService();
            $userData = $authApi->getUserDetails($request);

            if (!permissionLevelCheck('SELLER_ONLY', $userData['role_id'])) {
                throw new Exception("SELLER_ONLY", getStatusCodes('UNAUTHORIZED'));
            }

            $userId = $userData['id'];

            $productInst = PrProduct::where('id', '=', $id)
                    ->where('owner_id', '=', $userId)
                    ->first();

            if (!$productInst) {
                throw new Exception("PRODUCT_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }

            $children = PrProduct::where('id', '=', $id)
                    ->with('pr_product_infos')
                    ->get();

            $totalNumberOfChild = 0;
            foreach ($children as $child) {
                $totalNumberOfChild += sizeof($child->getRelations()["pr_product_infos"]);
            }


            if ($totalNumberOfChild > 0) {
                throw new Exception("PRODUCT_CAN_NOT_DELETE_RELATION_DATA_AVAILABLE", getStatusCodes('EXCEPTION'));
            }

//            $roleExist->delete();
//            DB::commit();

            addToLog('product delete success id - ' . $id, $this->enumSuccess);
            return response()->json([
                        'message' => 'PRODUCT_DELETE_OK'
            ]);
        } catch (Exception $exception) {
            DB::rollBack();
            addToLog($exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], $exception->getCode());
        }
    }

}
