<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubCategoryRequest;
use App\Services\CategoryService;
use App\Services\SubCategoryService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    use ResponseTrait;
    public $subCategoryService;
    public $categoryService;

    public function __construct()
    {
        $this->subCategoryService = new SubCategoryService;
        $this->categoryService = new CategoryService;
    }

    public function index()
    {
        $data['pageTitle'] = __("Sub Category List");
        $data['subCategories'] = $this->subCategoryService->getAll();
        $data['categories'] = $this->categoryService->getAll();
        return view('admin.setting.sub-category')->with($data);
    }

    public function store(SubCategoryRequest $request)
    {
        return $this->subCategoryService->store($request);
    }

    public function getInfo(Request $request)
    {
        $data = $this->subCategoryService->getById($request->id);
        return $this->success($data);
    }

    public function destroy($id)
    {
        return $this->subCategoryService->delete($id);
    }
}
