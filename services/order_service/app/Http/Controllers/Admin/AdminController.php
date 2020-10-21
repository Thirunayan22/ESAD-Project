<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\AdminService;

class AdminController extends Controller
{

    private $adminService;

    public function __construct(Request $request)
    {
        $this->adminService = new AdminService();
    }

    /**
     * {@inheritdoc}
     */
    public function adminAssistCreate(Request $request)
    {
        return $this->adminService->create($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getUsers(Request $request)
    {
        return $this->adminService->getUsers($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserById($id)
    {
        return $this->adminService->getUserById($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getByName($name)
    {
        return $this->adminService->getByName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function adminAssistUpdate(Request $request, $id)
    {
        return $this->adminService->adminAssistUpdate($request, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->adminService->delete($id);
    }

    /**
     * {@inheritdoc}
     */
    public function pendingVerifications(Request $request)
    {
        return $this->adminService->pendingVerifications($request);
    }

    /**
     * {@inheritdoc}
     */
    public function verifySellerByAdmin(Request $request, $id)
    {
        return $this->adminService->verifySellerByAdmin($request, $id);
    }

}
