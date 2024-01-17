<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Models\Category;
use App\Models\SubCategory;
use App\Services\SearchService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    use ResponseTrait;
    public $searchService;

    public function __construct()
    {
        $this->searchService = new SearchService;
    }
    public function search($categoryId = null)
    {
            $locale = app()->getLocale();
        $data['pageTitle'] = __('Search');
        $subCategories = Category::where('sub_categories.status', ACTIVE)
                        ->where('categories.status', ACTIVE)
                        ->whereIn('sub_categories.id', getLimit(RULES_USE_CASE))
                        ->join('sub_categories', 'sub_categories.category_id', '=', 'categories.id')
                        ->leftJoin('file_managers', ['file_managers.origin_id' =>  'sub_categories.id','file_managers.origin_type' => DB::raw("'App\\\Models\\\SubCategory'")])
                        ->select("sub_categories.*","sub_categories.name_$locale as name_locale","categories.name_$locale as category_name",DB::raw("CONCAT(file_managers.folder_name,'/',file_managers.file_name) AS sub_category_icon"))
                        ->get();
        $data['categories'] = $subCategories->groupBy('category_id');
        $data['subCategoryId'] = $categoryId;
        return view('user.search.result', $data);
    }


    public function searchStore(SearchRequest $request)
    {
        if(getLimit(RULES_CHARACTER) < 10){
            return $this->error([],  __("Your character Limit finished"));
        }
        if(is_null(getOption('chat_gpt_api', NULL))){
            return $this->error([],  __("API credentials not found"));
        }

        return $this->searchService->searchStore($request);
    }

    public function searchItemUpdate(Request $request)
    {
        return $this->searchService->searchItemUpdate($request);
    }

    public function searchItemFavorite(Request $request)
    {
        return $this->searchService->searchItemFavorite($request);
    }

    public function searchItemReact(Request $request)
    {
        return $this->searchService->searchItemReact($request);
    }

    public function searchItemDelete(Request $request)
    {
        return $this->searchService->searchItemDelete($request);
    }

    public function getCategoryContent(Request $request)
    {
        $subCategory = $this->searchService->getSubCategoryById($request->id);
        $data['view'] = view('user.search.category-content', compact('subCategory'))->render();
        return $this->success($data);
    }
}
