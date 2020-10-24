<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Services\Invoice\InvoiceItemsService;
use Illuminate\Http\Request;

class InvoiceItemsController extends Controller
{

    private $invoiceItemsService;

    public function __construct()
    {
        $this->invoiceItemsService = new InvoiceItemsService();
    }

    /**
     * {@inheritdoc}
     */
    public function create(Request $request)
    {
        return $this->invoiceItemsService->create($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        return $this->invoiceItemsService->getById($id);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Request $request, $id)
    {
        return $this->invoiceItemsService->update($request, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Request $request, $id)
    {
        return $this->invoiceItemsService->delete($request, $id);
    }

}
