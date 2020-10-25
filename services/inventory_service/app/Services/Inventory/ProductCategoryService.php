<?php

/**
 * {@inheritdoc}
 */

namespace App\Services\Inventory;

use Illuminate\Support\Facades\DB;
use Exception;
use App\Services\Auth\ConnectAuthService;
use App\Models\PrCategory;
use App\Services\Inventory\ProductsService;

class ProductCategoryService
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

            if (!permissionLevelCheck('ADMINS_ONLY', $userData['role_id'])) {
                throw new Exception("ADMINS_ONLY", getStatusCodes('UNAUTHORIZED'));
            }
//            $userId = $userData['id'];

            $request->validate([
                'super_cat_id' => 'nullable|exists:pr_category,id',
                'category_name' => 'required|string|min:6|max:255'
            ]);

            $categoryExist = PrCategory::where('category_name', '=', $request->category_name)
                    ->first();

            if ($categoryExist) {
                throw new Exception("PRODUCT_CATEGORY_EXIST", getStatusCodes('EXCEPTION'));
            }

            $categoryInst = New PrCategory();
            $categoryInst->super_cat_id = $request->super_cat_id;
            $categoryInst->category_name = $request->category_name;
            $categoryInst->created_at = now();
            $categoryInst->save();
            DB::commit();

            addToLog('created category_name: ' . $request->category_name, $this->enumSuccess);

            return response()->json([
                        'data' => $categoryInst,
                        'message' => 'PRODUCT_CATEGORY_CREATE_OK'
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
    public function getCategoriesWithProducts()
    {
        try {
            $categories = PrCategory::select(['id', 'category_name', 'super_cat_id'])
                    ->with('pr_products:id,category_id')
                    ->get();

            if (sizeof($categories) == 0) {

                // if no categories send empty array
                return response()->json([
                            'message' => 'GET_CATEGORIES_OK',
                            'data' => []
                ]);

                // throw new Exception("CATEGORIES_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }
            $productsInst = new ProductsService();
//            $fullResponse = [];
            foreach ($categories as $category) {
                $productData = [];
                foreach ($category->pr_products as $prProduct) {
                    $productData[] = $productsInst->getById($prProduct->id, true);
                }
//                $fullResponse[] = array_merge($category->toArray(), ['products' => $productData]);
//                $fullResponse[] =
                $category->products = $productData;
            }

            return response()->json([
                        'message' => 'GET_CATEGORIES_WITH_PRODUCTS_OK',
                        'data' => $categories
            ]);
        } catch (Exception $exception) {
            addToLog($exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], $exception->getCode());
        }
    }

    public function getAllNestedCategories($id)
    {
        try {
            if (!isset($id)) {
                throw new Exception("CATEGORY_ID_REQUIRED", getStatusCodes('EXCEPTION'));
            }

            $idValidation = checkDataType($id);

            if ($idValidation instanceof Exception) {
                throw new Exception($idValidation->getMessage(), $idValidation->getCode());
            }

            $category = PrCategory::where('id', '=', $id)
                    ->select('id', 'super_cat_id')//'category_name', 'active_status',
                    ->first();

            if (!$category) {
                throw new Exception("CATEGORY_NOT_AVAILABLE_BY_ID", getStatusCodes('EXCEPTION'));
            }

            $categories = PrCategory::select('id', 'category_name', 'super_cat_id')
                    ->get();

//            $tree2 = $this->formatTree2($categories, $id);
//            $result = $this->arrayFlatten($tree2);
//            $category->pr_categories = $result;

            addToLog('view all data racks', $this->enumSuccess);
            return response()->json([
                        'message' => 'GET_NESTED_CATEGORIES_OK',
                        'data' => $categories
            ]);
        } catch (Exception $exception) {
            addToLog($exception->getMessage());
            dd($exception);
            return response()->json(['message' => $exception->getMessage()], $exception->getCode());
        }
    }

    public function formatTree2($tree, $parent)
    {
        $tree2 = [];
        foreach ($tree as $item) {
            if ($item['super_cat_id'] == $parent) {
                $item->pr_categories = $this->formatTree2($tree, $item['id']);
                $child = $this->formatTree2($tree, $item['id']);

                $tree2[] = $child;
                $tree2[] = $item['id'];
            }
        }

        return $tree2;
    }

    public function arrayFlatten($array = null)
    {

        $result = array();

        if (!is_array($array)) {
            $array = func_get_args();
        }

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, array_flatten($value));
            } else {
                $result = array_merge($result, array($key => $value));
            }
        }

        return $result;
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

            if (!permissionLevelCheck('ADMINS_ONLY', $userData['role_id'])) {
                throw new Exception("ADMINS_ONLY", getStatusCodes('UNAUTHORIZED'));
            }

//            $userId = $userData['id'];

            $categoryInst = PrCategory::where('id', '=', $id)
                    ->first();

            if (!$categoryInst) {
                throw new Exception("PRODUCT_CATEGORY_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }

            $request->validate([
                'super_cat_id' => 'nullable|exists:pr_category,id',
                'category_name' => 'required|string|min:6|max:255'
            ]);

            $categoryExist = PrCategory::where('id', '!=', $id)
                    ->where('category_name', '=', $request->category_name)
                    ->first();

            if ($categoryExist) {
                throw new Exception("PRODUCT_CATEGORY_EXIST", getStatusCodes('EXCEPTION'));
            }

            $categoryInst->super_cat_id = $request->super_cat_id;
            $categoryInst->category_name = $request->category_name;
            $categoryInst->save();

            DB::commit();

            addToLog('update category_name: ' . $request->category_name, $this->enumSuccess);

            return response()->json([
                        'data' => $categoryInst,
                        'message' => 'PRODUCT_CATEGORY_UPDATE_OK'
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

            if (!permissionLevelCheck('ADMINS_ONLY', $userData['role_id'])) {
                throw new Exception("ADMINS_ONLY", getStatusCodes('UNAUTHORIZED'));
            }

//            $userId = $userData['id'];

            $categoryInst = PrCategory::where('id', '=', $id)->first();

            if (!$categoryInst) {
                throw new Exception("PRODUCT_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }

            $children = PrCategory::where('id', '=', $id)
                    ->with('pr_categories')
                    ->with('pr_products')
                    ->get();

            $totalNumberOfChild = 0;
            foreach ($children as $child) {
                $totalNumberOfChild += sizeof($child->getRelations()["pr_categories"]);
                $totalNumberOfChild += sizeof($child->getRelations()["pr_products"]);
            }

            if ($totalNumberOfChild > 0) {
                throw new Exception("PRODUCT_CATEGORY_CAN_NOT_DELETE_RELATION_DATA_AVAILABLE", getStatusCodes('EXCEPTION'));
            }

//            $roleExist->delete();
//            DB::commit();

            addToLog('product category delete success id - ' . $id, $this->enumSuccess);
            return response()->json([
                        'message' => 'PRODUCT_CATEGORY_DELETE_OK'
            ]);
        } catch (Exception $exception) {
            DB::rollBack();
            addToLog($exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], $exception->getCode());
        }
    }

}
