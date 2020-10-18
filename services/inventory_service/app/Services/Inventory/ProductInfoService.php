<?php

/**
 * {@inheritdoc}
 */

namespace App\Services\Inventory;

use Illuminate\Support\Facades\DB;
use Exception;
use App\Services\Auth\ConnectAuthService;
use App\Models\PrProduct;
use App\Models\PrProductInfo;

class ProductInfoService
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

            $request->validate([
                'product_id' => 'required|exists:pr_product,id',
                'price' => 'required|numeric|min:0',
                'quatity' => 'required|numeric|min:0',
                'default_image' => 'required|string|min:10|max:255',
                'product_images' => 'required|string|min:10|max:255'
            ]);

            $userId = $userData['id'];
            $productInst = PrProduct::where('id', '=', $request->product_id)
                    ->where('owner_id', '=', $userId)
                    ->with('pr_product_infos')
                    ->get();

            if (!$productInst) {
                throw new Exception("PRODUCT_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }

            $totalNumberOfChild = 0;
            foreach ($productInst as $child) {
                $totalNumberOfChild += sizeof($child->getRelations()["pr_product_infos"]);
            }

            if ($totalNumberOfChild > 0) {
                throw new Exception("PRODUCT_INFO_ALREADY_AVAILABLE", getStatusCodes('EXCEPTION'));
            }


            $productInfoInst = New PrProductInfo();
            $productInfoInst->product_id = $request->product_id;
            $productInfoInst->price = $request->price;
            $productInfoInst->quatity = $request->quatity;
            $productInfoInst->default_image = $request->default_image;
            $productInfoInst->product_images = $request->product_images;
            $productInfoInst->created_at = now();
            $productInfoInst->save();
            DB::commit();

            addToLog('product created product info: ' . $request->product_id, $this->enumSuccess);

            return response()->json([
                        'data' => $productInfoInst,
                        'message' => 'PRODUCT_INFO_CREATE_OK'
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
    public function getById($id, $returnType = false)
    {
        try {
            if (!isset($id)) {
                throw new Exception("PRODUCT_INFO_ID_REQUIRED", getStatusCodes('EXCEPTION'));
            }

            $idValidation = checkDataType($id);

            if ($idValidation instanceof Exception) {
                throw new Exception($idValidation->getMessage(), $idValidation->getCode());
            }

            $productInfo = PrProductInfo::orderBy('created_at', 'desc')
                    ->select(['id', 'product_id', 'price', 'price', 'quatity', 'default_image', 'product_images'])
                    ->where('id', '=', $id)
                    ->first();

            if (!$productInfo) {
                throw new Exception("PRODUCT_NOT_AVAILABLE_BY_ID", getStatusCodes('EXCEPTION'));
            }


            addToLog('view data of the given product ' . $id, $this->enumSuccess);

            if ($returnType) {
                return $productInfo;
            }

            return response()->json([
                        'message' => 'GET_PRODUCT_INFO_BY_ID_OK',
                        'data' => $productInfo
            ]);
        } catch (Exception $exception) {
            addToLog($exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], $exception->getCode() == 0 ? getStatusCodes('VALIDATION_ERROR') : $exception->getCode());
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

            $productInfoInst = PrProductInfo::where('id', '=', $id)->first();

            if (!$productInfoInst) {
                throw new Exception("PRODUCT_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }

            $productInst = PrProduct::where('owner_id', '=', $userId)
                    ->where('id', '=', $productInfoInst->product_id)
                    ->first();

            if (!$productInst) {
                throw new Exception("PRODUCT_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }

            $request->validate([
                'product_id' => 'required|exists:pr_product,id',
                'price' => 'required|numeric|min:0',
                'quatity' => 'required|numeric|min:0',
                'default_image' => 'required|string|min:10|max:255',
                'product_images' => 'required|string|min:10|max:255'
            ]);

            $productInfoInst->product_id = $request->product_id;
            $productInfoInst->price = $request->price;
            $productInfoInst->quatity = $request->quatity;
            $productInfoInst->default_image = $request->default_image;
            $productInfoInst->product_images = $request->product_images;
            $productInfoInst->save();

            DB::commit();

            addToLog('PRODUCT info update product_id: ' . $request->product_id, $this->enumSuccess);

            return response()->json([
                        'data' => $productInfoInst,
                        'message' => 'PRODUCT_INFO_UPDATE_OK'
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
    public function quantityUpdate($request, $id)
    {

        DB::beginTransaction();
        try {

            $idValidation = checkDataType($id);

            if ($idValidation instanceof Exception) {
                throw new Exception($idValidation->getMessage(), $idValidation->getCode());
            }

            $authApi = new ConnectAuthService();
            $userData = $authApi->getUserDetails($request);

            if (!permissionLevelCheck('BUYER_ONLY', $userData['role_id'])) {
                throw new Exception("BUYER_ONLY", getStatusCodes('UNAUTHORIZED'));
            }

            $productInfoInst = PrProductInfo::where('id', '=', $id)->first();

            if (!$productInfoInst) {
                throw new Exception("PRODUCT_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }

            $request->validate([
                'quatity' => 'required|numeric|min:0',
            ]);

            if ($productInfoInst->quatity < $request->quatity) {
                throw new Exception("PRODUCT_QUANTITY_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }
            $productInfoInst->quatity = $productInfoInst->quatity - $request->quatity;
            $productInfoInst->save();
            DB::commit();

            addToLog('PRODUCT quantity update product_id: ' . $id, $this->enumSuccess);

            return response()->json([
                        'data' => $productInfoInst,
                        'message' => 'PRODUCT_QUANTITY_UPDATE_OK'
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

            $productInfoInst = PrProductInfo::where('id', '=', $id)->first();

            if (!$productInfoInst) {
                throw new Exception("PRODUCT_INFO_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }

            $productInst = PrProduct::where('owner_id', '=', $userId)
                    ->where('id', '=', $productInfoInst->product_id)
                    ->first();

            if (!$productInst) {
                throw new Exception("PRODUCT_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }

            //check with invice services
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
