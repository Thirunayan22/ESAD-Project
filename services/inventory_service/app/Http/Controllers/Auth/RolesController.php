<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
//use JWTAuth;
//use App\Http\Requests\Rbac\RolesRequest;
use App\Services\Auth\RolesService;

class RolesController extends Controller
{

    private $user;
    private $rolesService;

    public function __construct()
    {
//        $this->user = JWTAuth::parseToken()->authenticate();
        $this->rolesService = new RolesService();
    }

    /**
     * {@inheritdoc}
     */
    public function create($request)
    {
        return $this->rolesService->create($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->rolesService->getRoles();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleById($id)
    {
        return $this->rolesService->getById($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getByName($name)
    {
        return $this->rolesService->getByName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function update($request, $id)
    {
        return $this->rolesService->update($request, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->rolesService->delete($id);
    }

}
