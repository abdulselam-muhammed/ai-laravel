<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PackageRequest;
use App\Services\GatewayService;
use App\Services\PackageService;
use App\Services\SubCategoryService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    use ResponseTrait;
    public $packageService;
    public $gatewayService;

    public function __construct()
    {
        $this->packageService = new PackageService;
        $this->gatewayService = new GatewayService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->packageService->getAllData($request);
        } else {
            $data['pageTitle'] = __('All Packages');
            $subCategoryService = new SubCategoryService;
            $data['useCases'] = $subCategoryService->getActiveAll();
            return view('admin.packages.index', $data);
        }
    }

    public function store(PackageRequest $request)
    {
        return $this->packageService->store($request);
    }

    public function getInfo(Request $request)
    {
        $data = $this->packageService->getInfo($request->id);
        return $this->success($data);
    }

    public function destroy($id)
    {
        return $this->packageService->destroy($id);
    }

    public function userPackage(Request $request)
    {
        if ($request->ajax()) {
            return $this->packageService->getUserPackagesData($request);
        } else {
            $data['pageTitle'] = __('User Packages');
            return view('admin.packages.user', $data);
        }
    }

    public function pay($id)
    {
        $data['pageTitle'] = __('Package Pay');
        $data['package'] = $this->packageService->getInfo($id);
        $data['gateways'] = $this->gatewayService->getActiveAll();
        $data['banks'] = $this->gatewayService->getActiveBanks();
        return view('admin.packages.pay', $data);
    }
}
