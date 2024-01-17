<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HowItWorkRequest;
use App\Services\HowItWorkService;

class HowItWorkController extends Controller
{
    public $howItWorkService;

    public function __construct()
    {
        $this->howItWorkService = new HowItWorkService;
    }

    public function index()
    {
        $data['pageTitle'] = __("How It Works");
        $data['subHowItWorkActiveClass'] = 'active';
        $data['howItWorks'] = $this->howItWorkService->getAll();
        return view('admin.setting.how-it-work')->with($data);
    }

    public function store(HowItWorkRequest $request)
    {
        return $this->howItWorkService->store($request);
    }

    public function destroy($id)
    {
        return $this->howItWorkService->delete($id);
    }
}
