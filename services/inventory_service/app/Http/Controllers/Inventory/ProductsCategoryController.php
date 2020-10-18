<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Services\Inventory\ProductCategoryService;
use Illuminate\Http\Request;

class ProductsCategoryController extends Controller
{

    private $productCategoryService;

    public function __construct()
    {
        $this->productCategoryService = new ProductCategoryService();
    }

    /**
     * {@inheritdoc}
     */
    public function create(Request $request)
    {
        return $this->productCategoryService->create($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllCategories()
    {
        return $this->productCategoryService->getCategoriesWithProducts();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllNestedCategories($id)
    {
        return $this->productCategoryService->getAllNestedCategories($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllWithProducts($id)
    {
        return $this->productCategoryService->getAllWithProducts($id);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Request $request, $id)
    {
        return $this->productCategoryService->update($request, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Request $request, $id)
    {
        return $this->productCategoryService->delete($request, $id);
    }

}
