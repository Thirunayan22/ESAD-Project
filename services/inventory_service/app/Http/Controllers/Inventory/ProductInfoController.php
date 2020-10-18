<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Services\Inventory\ProductInfoService;
use Illuminate\Http\Request;

class ProductInfoController extends Controller
{

    private $productInfoService;

    public function __construct()
    {
        $this->productInfoService = new ProductInfoService();
    }

    /**
     * {@inheritdoc}
     */
    public function create(Request $request)
    {
        return $this->productInfoService->create($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        return $this->productInfoService->getById($id);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Request $request, $id)
    {
        return $this->productInfoService->update($request, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function quantityUpdate(Request $request, $id)
    {
        return $this->productInfoService->quantityUpdate($request, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Request $request, $id)
    {
        return $this->productInfoService->delete($request, $id);
    }

}
