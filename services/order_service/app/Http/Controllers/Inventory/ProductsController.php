<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Services\Inventory\ProductsService;
use Illuminate\Http\Request;

class ProductsController extends Controller
{

    private $productService;

    public function __construct()
    {
        $this->productService = new ProductsService();
    }

    /**
     * {@inheritdoc}
     */
    public function create(Request $request)
    {
        return $this->productService->create($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllProducts()
    {
        return $this->productService->getAllProducts();
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        return $this->productService->getById($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getByName($name)
    {
        return $this->productService->getByName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Request $request, $id)
    {
        return $this->productService->update($request, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Request $request, $id)
    {
        return $this->productService->delete($request, $id);
    }

}
