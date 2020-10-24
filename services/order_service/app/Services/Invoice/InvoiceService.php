<?php

/**
 * {@inheritdoc}
 */

namespace App\Services\Invoice;

use Illuminate\Support\Facades\DB;
use Exception;
use App\Services\Auth\ConnectAuthService;
use App\Models\InvInvoice;

class InvoiceService
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

            if (!permissionLevelCheck('BUYER_ONLY', $userData['role_id'])) {
                throw new Exception("BUYER_ONLY", getStatusCodes('UNAUTHORIZED'));
            }
            $userId = $userData['id'];

            $request->validate([
                'total_amount' => 'required|numeric',
                'payment_procedure' => 'required|numeric|digits:1|in:1,2',
                'paid_status' => 'required|numeric|digits:1|in:1,2',
            ]);

            $invInst = New InvInvoice();
            $invInst->buyer_id = $userId;
            $invInst->complete_status = 2;
            $invInst->total_amount = $request->total_amount;
            $invInst->payment_procedure = $request->payment_procedure;
            $invInst->paid_status = $request->paid_status;
            $invInst->created_at = now();
            $invInst->save();
            DB::commit();

            addToLog('invoice created role_name: ' . $invInst->buyer_id, $this->enumSuccess);

            return response()->json([
                        'data' => $invInst,
                        'message' => 'INVOICE_CREATE_OK'
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
    public function getAllInvoices($request)
    {
        try {
            $authApi = new ConnectAuthService();
            $userData = $authApi->getUserDetails($request);

            if (!permissionLevelCheck('BUYER_ONLY', $userData['role_id'])) {
                throw new Exception("BUYER_ONLY", getStatusCodes('UNAUTHORIZED'));
            }
            $userId = $userData['id'];

            $invoices = InvInvoice::orderBy('created_at', 'desc')
                    ->select(['id', 'buyer_id', 'complete_status', 'total_amount',
                        'payment_procedure', 'paid_status', 'delivery_date'])
                    ->with('inv_items:inv_id,product_id,quantity,unit_price,discount_amount,total_price')
                    ->where('buyer_id', '=', $userId)
                    ->get();
            //->paginate(getGlobalSettingByName('DEFAULT_ITEMS_PER_PAGE'));

            if (sizeof($invoices) == 0) {

                // if no invoices send empty array
                return response()->json([
                            'message' => 'GET_PRODUCTS_OK',
                            'data' => []
                ]);
            }

            addToLog('view all data invoices', $this->enumSuccess);

            return response()->json([
                        'message' => 'GET_INVOICES_OK',
                        'data' => $invoices->toArray()
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
                throw new Exception("INVOICE_ID_REQUIRED", getStatusCodes('EXCEPTION'));
            }

            $idValidation = checkDataType($id);

            if ($idValidation instanceof Exception) {
                throw new Exception($idValidation->getMessage(), $idValidation->getCode());
            }

            $invoice = InvInvoice::orderBy('created_at', 'desc')
                    ->select(['id', 'buyer_id', 'complete_status', 'total_amount',
                        'payment_procedure', 'paid_status', 'delivery_date'])
                    ->with('inv_items:inv_id,product_id,quantity,unit_price,discount_amount,total_price')
                    ->where('id', '=', $id)
                    ->first();

            if (!$invoice) {
                throw new Exception("INVOICE_NOT_AVAILABLE_BY_ID", getStatusCodes('EXCEPTION'));
            }


            addToLog('view data of the given invoice ' . $id, $this->enumSuccess);

            if ($returnType) {
                return $invoice;
            }

            return response()->json([
                        'message' => 'GET_INVOICE_BY_ID_OK',
                        'data' => $invoice
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
     * update complete status
     */
    public function delivered($request, $id)
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


            $invInst = InvInvoice::where('id', '=', $id)->first();

            if (!$invInst) {
                throw new Exception("INVOICE_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }

            if ($invInst->complete_status != 1) {
                throw new Exception("INVOICE_NOT_COMPLETED", getStatusCodes('EXCEPTION'));
            }

            $children = InvInvoice::where('id', '=', $id)
                    ->with('inv_items')
                    ->get();

            $totalNumberOfChild = 0;
            foreach ($children as $child) {
                $totalNumberOfChild += sizeof($child->getRelations()["inv_items"]);
            }

            if ($totalNumberOfChild == 0) {
                throw new Exception("INVOICE_CAN_NOT_UPDATE", getStatusCodes('EXCEPTION'));
            }

            $invInst->delivery_date = now();
            $invInst->save();
            DB::commit();

            addToLog('invoice delivered invoice id : ' . $id, $this->enumSuccess);

            return response()->json([
                        'data' => $invInst,
                        'message' => 'INVOICE_DELIVERY_UPDATE_OK'
            ]);
        } catch (Exception $exception) {
            DB::rollBack();
            addToLog($exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], $exception->getCode() == 0 ? getStatusCodes('VALIDATION_ERROR') : $exception->getCode());
        }
    }

    /**
     * {@inheritdoc}
     * update complete status
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

            $request->validate([
                'complete_status' => 'required|numeric|digits:1|in:1,3'
            ]);

            $invInst = InvInvoice::where('id', '=', $id)->first();

            if (!$invInst) {
                throw new Exception("INVOICE_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }

            if ($invInst->complete_status != 2) {
                throw new Exception("INVOICE_NOT_PENDING", getStatusCodes('EXCEPTION'));
            }

            $children = InvInvoice::where('id', '=', $id)
                    ->with('inv_items')
                    ->get();

            $totalNumberOfChild = 0;
            foreach ($children as $child) {
                $totalNumberOfChild += sizeof($child->getRelations()["inv_items"]);
            }

            if ($totalNumberOfChild == 0) {
                throw new Exception("INVOICE_CAN_NOT_UPDATE", getStatusCodes('EXCEPTION'));
            }

            $invInst->complete_status = $request->complete_status;
            $invInst->save();
            DB::commit();

            addToLog('invoice complete_status changed invoice id : ' . $request->complete_status . ' ,invoice id' . $id, $this->enumSuccess);

            return response()->json([
                        'data' => $invInst,
                        'message' => 'INVOICE_UPDATE_OK'
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

            $invInst = InvInvoice::where('id', '=', $id)
                    ->where('buyer_id', '=', $userId)
                    ->first();

            if (!$invInst) {
                throw new Exception("PRODUCT_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }

            $invInst->complete_status = 4;
            $invInst->save();
            DB::commit();

            addToLog('invoice cancel success id - ' . $id, $this->enumSuccess);
            return response()->json([
                        'message' => 'INVOICE_CANCELLED_OK'
            ]);
        } catch (Exception $exception) {
            DB::rollBack();
            addToLog($exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], $exception->getCode());
        }
    }

}
