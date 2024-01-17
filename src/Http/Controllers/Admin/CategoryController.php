<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Services\CategoryService;

class CategoryController extends Controller
{
    public $categoryService;

    public function __construct()
    {
        $this->categoryService = new CategoryService;
    }

    public function index()
    {
        $data['pageTitle'] = __("Category List");
        $data['categories'] = $this->categoryService->getAll();
        return view('admin.setting.category')->with($data);
    }

    public function store(CategoryRequest $request)
    {
        return $this->categoryService->store($request);
    }

    public function destroy($id)
    {
        return $this->categoryService->delete($id);
    }
}
