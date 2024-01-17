<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrandRequest;
use App\Services\BrandService;

class BrandController extends Controller
{
    public $brandService;

    public function __construct()
    {
        $this->brandService = new BrandService;
    }

    public function index()
    {
        $data['pageTitle'] = __("Brand");
        $data['subBrandActiveClass'] = 'active';
        $data['brands'] = $this->brandService->getAll();
        return view('admin.setting.brand')->with($data);
    }

    public function store(BrandRequest $request)
    {
        return $this->brandService->store($request);
    }

    public function destroy($id)
    {
        return $this->brandService->delete($id);
    }
}
