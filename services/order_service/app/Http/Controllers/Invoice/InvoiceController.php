<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Services\Invoice\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{

    private $invoiceService;

    public function __construct()
    {
        $this->invoiceService = new InvoiceService();
    }

    /**
     * {@inheritdoc}
     */
    public function create(Request $request)
    {
        return $this->invoiceService->create($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllInvoices(Request $request)
    {
        return $this->invoiceService->getAllInvoices($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        return $this->invoiceService->getById($id);
    }

    /**
     * {@inheritdoc}
     */
    public function delivered(Request $request, $id)
    {
        return $this->invoiceService->delivered($request, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Request $request, $id)
    {
        return $this->invoiceService->update($request, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Request $request, $id)
    {
        return $this->invoiceService->delete($request, $id);
    }

}
