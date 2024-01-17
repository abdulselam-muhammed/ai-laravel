<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SubCategoryService;
use Illuminate\Http\Request;

class HomeSettingController extends Controller
{
    public $subCategoryService;

    public function __construct()
    {
        $this->subCategoryService = new SubCategoryService;
    }

    public function section()
    {
        $data['pageTitle'] = __("Home Section Setting");
        $data['subHomeSectionSettingActiveClass'] = 'active';
        $data['subCategories'] = $this->subCategoryService->getActiveAll();
        return view('admin.setting.home.section-settings', $data);
    }
}
