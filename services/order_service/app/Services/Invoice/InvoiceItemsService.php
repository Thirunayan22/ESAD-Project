<?php

/**
 * {@inheritdoc}
 */

namespace App\Services\Invoice;

use Illuminate\Support\Facades\DB;
use Exception;
use App\Services\Auth\ConnectAuthService;
use App\Models\InvInvoice;
use App\Models\InvItem;

class InvoiceItemsService
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

            $request->validate([
                'inv_id' => 'required|exists:inv_invoice,id',
                'product_id' => 'required|numeric',
                'unit_price' => 'required|numeric|min:0',
                'discount_amount' => 'required|numeric|min:0',
                'quantity' => 'required|numeric|min:0',
                'total_price' => 'required|numeric|min:0'
            ]);

            $userId = $userData['id'];
            $invInst = InvInvoice::where('id', '=', $request->inv_id)
                    ->where('buyer_id', '=', $userId)
                    ->with('inv_items')
                    ->first();

            if (!$invInst) {
                throw new Exception("INVOICE_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }

            if ($invInst->complete_status != 2) {
                throw new Exception("ONLY_PENDING_INVOICE_ITEMS_CAN_BE_ADDED", getStatusCodes('EXCEPTION'));
            }

            if (sizeof($invInst->inv_items) > 0) {
                foreach ($invInst->inv_items as $value) {
                    if ($value->product_id == $request->product_id) {
                        throw new Exception("INVOICE_ITEM_ALREADY_AVAILABLE", getStatusCodes('EXCEPTION'));
                    }
                }
            }


            $invoiceItemsInst = New InvItem();
            $invoiceItemsInst->inv_id = $request->inv_id;
            $invoiceItemsInst->product_id = $request->product_id;
            $invoiceItemsInst->quantity = $request->quantity;
            $invoiceItemsInst->unit_price = $request->unit_price;
            $invoiceItemsInst->discount_amount = $request->discount_amount;
            $invoiceItemsInst->total_price = $request->total_price;
            $invoiceItemsInst->created_at = now();
            $invoiceItemsInst->save();
            DB::commit();

            addToLog('invoice items created for : ' . $request->inv_id, $this->enumSuccess);

            return response()->json([
                        'data' => $invoiceItemsInst,
                        'message' => 'INVOICE_ITEMS_CREATE_OK'
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

            $invoiceItems = InvItem::orderBy('created_at', 'desc')
                    ->select(['id', 'inv_id', 'product_id', 'quantity', 'unit_price', 'discount_amount', 'total_price'])
                    ->where('id', '=', $id)
                    ->first();

            if (!$invoiceItems) {
                throw new Exception("INVOCE_ITEMS_NOT_AVAILABLE_BY_ID", getStatusCodes('EXCEPTION'));
            }


            addToLog('view data of the given invoice items ' . $id, $this->enumSuccess);

            if ($returnType) {
                return $invoiceItems;
            }

            return response()->json([
                        'message' => 'GET_INVOICE_ITEMS_BY_ID_OK',
                        'data' => $invoiceItems
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

            if (!permissionLevelCheck('BUYER_ONLY', $userData['role_id'])) {
                throw new Exception("BUYER_ONLY", getStatusCodes('UNAUTHORIZED'));
            }

            $userId = $userData['id'];

            $invoiceItemsInst = InvItem::where('id', '=', $id)->first();

            if (!$invoiceItemsInst) {
                throw new Exception("INVOICE_ITEM_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }

//            $productInst = PrProduct::where('owner_id', '=', $userId)
//                    ->where('id', '=', $invoiceItemsInst->product_id)
//                    ->first();
//
//            if (!$productInst) {
//                throw new Exception("PRODUCT_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
//            }

            $invInst = InvInvoice::where('buyer_id', '=', $userId)
                    ->where('id', '=', $invoiceItemsInst->inv_id)
                    ->first();

            if (!$invInst) {
                throw new Exception("INVOICE_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }

            if ($invInst->complete_status != 2) {
                throw new Exception("ONLY_PENDING_INVOICE_ITEMS_CAN_BE_UPDATED", getStatusCodes('EXCEPTION'));
            }

            $request->validate([
                'product_id' => 'required|numeric',
                'unit_price' => 'required|numeric|min:0',
                'discount_amount' => 'required|numeric|min:0',
                'quantity' => 'required|numeric|min:0',
                'total_price' => 'required|numeric|min:0'
            ]);

            $invoiceItemsInst->product_id = $request->product_id;
            $invoiceItemsInst->quantity = $request->quantity;
            $invoiceItemsInst->unit_price = $request->unit_price;
            $invoiceItemsInst->discount_amount = $request->discount_amount;
            $invoiceItemsInst->total_price = $request->total_price;
            $invoiceItemsInst->save();

            DB::commit();

            addToLog('Invoice item update product_id: ' . $request->product_id, $this->enumSuccess);

            return response()->json([
                        'data' => $invoiceItemsInst,
                        'message' => 'INVOICE_ITEM_UPDATE_OK'
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

            if (!permissionLevelCheck('BUYER_ONLY', $userData['role_id'])) {
                throw new Exception("BUYER_ONLY", getStatusCodes('UNAUTHORIZED'));
            }

            $userId = $userData['id'];

            $invoiceItemsInst = InvItem::where('id', '=', $id)->first();

            if (!$invoiceItemsInst) {
                throw new Exception("INVOICE_ITEM_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }

            $invInst = InvInvoice::where('buyer_id', '=', $userId)
                    ->where('id', '=', $invoiceItemsInst->inv_id)
                    ->first();

            if (!$invInst) {
                throw new Exception("INVOICE_NOT_AVAILABLE", getStatusCodes('EXCEPTION'));
            }

            if ($invInst->complete_status != 2) {
                throw new Exception("ONLY_PENDING_INVOICE_ITEMS_CAN_BE_DELETED", getStatusCodes('EXCEPTION'));
            }

            $invoiceItemsInst->delete();
            DB::commit();

            addToLog('product delete success id - ' . $id, $this->enumSuccess);
            return response()->json([
                        'message' => 'INVOICE_ITEM_DELETE_OK'
            ]);
        } catch (Exception $exception) {
            DB::rollBack();
            addToLog($exception->getMessage());
            return response()->json(['message' => $exception->getMessage()], $exception->getCode());
        }
    }

}
