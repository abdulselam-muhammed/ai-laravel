<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use App\Models\SearchResultItem;
use App\Services\SearchService;
use App\Services\SubCategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use App\Models\Link;
use App\Models\Visitor;
use Auth;
class ContentController extends Controller
{
    public $searchService;

    public function __construct()
    {
        $this->searchService = new SearchService;
    }

    public function list()
    {
        $authId = auth()->id();
        $data['pageTitle'] = __('All Template');
        $subCategories = Category::where('sub_categories.status', ACTIVE)
            ->where('categories.status', ACTIVE)
            ->whereIn('sub_categories.id', getLimit(RULES_USE_CASE, $authId))
            ->join('sub_categories', 'sub_categories.category_id', '=', 'categories.id')
            ->leftJoin('user_favorites', ['user_favorites.user_id' =>  DB::raw("'$authId'"), 'user_favorites.sub_category_id' => 'sub_categories.id'])
            ->leftJoin('file_managers', ['file_managers.origin_id' =>  'sub_categories.id', 'file_managers.origin_type' => DB::raw("'App\\\Models\\\SubCategory'")])
            ->leftJoin(DB::raw('file_managers AS cat'), ['cat.origin_id' =>  'sub_categories.category_id', 'cat.origin_type' => DB::raw("'App\\\Models\\\Category'")])
            ->select(
                'sub_categories.*',
                'categories.name as category_name',
                DB::raw("CONCAT(file_managers.folder_name,'/',file_managers.file_name) AS sub_category_icon"),
                DB::raw("CONCAT(cat.folder_name,'/',cat.file_name) AS category_icon"),
                'user_favorites.user_id as favorite'
            )
            ->get();
        $data['categories'] = $subCategories->groupBy('category_id');
        return view('user.content.list', $data);
    }

    public function favorite()
    {
        $authId = auth()->id();
        $data['pageTitle'] = __('Favorite Templates');
        $subCategories = Category::where('sub_categories.status', ACTIVE)
            ->where('categories.status', ACTIVE)
            ->whereIn('sub_categories.id', getLimit(RULES_USE_CASE, $authId))
            ->join('sub_categories', 'sub_categories.category_id', '=', 'categories.id')
            ->join('user_favorites', ['user_favorites.user_id' =>  DB::raw("'$authId'"), 'user_favorites.sub_category_id' => 'sub_categories.id'])
            ->leftJoin('file_managers', ['file_managers.origin_id' =>  'sub_categories.id', 'file_managers.origin_type' => DB::raw("'App\\\Models\\\SubCategory'")])
            ->leftJoin(DB::raw('file_managers AS cat'), ['cat.origin_id' =>  'sub_categories.category_id', 'cat.origin_type' => DB::raw("'App\\\Models\\\Category'")])
            ->select(
                'sub_categories.*',
                'categories.name as category_name',
                DB::raw("CONCAT(file_managers.folder_name,'/',file_managers.file_name) AS sub_category_icon"),
                DB::raw("CONCAT(cat.folder_name,'/',cat.file_name) AS category_icon")
            )
            ->get();
            $favorites = SearchResultItem::where('user_id',Auth::id())->where('is_favorite',1)->get();
            // return $favorites;
        $data['categories'] = $subCategories->groupBy('category_id');

        return view('user.content.favorite', $data,compact('favorites'));
    }

    public function subCategoryFavorite(Request $request)
    {
        $subCategoryService = new SubCategoryService;
        return $subCategoryService->favoriteTemplate($request);
    }

    public function trash()
    {
        $data['pageTitle'] = __('Trashed Content');
        $data['items'] = $this->searchService->getTrashItem();
        return view('user.content.trash', $data);
    }

    public function like()
    {
        $data['pageTitle'] = __('Liked Content');
        $data['items'] = $this->searchService->getReactItem(REACT_LIKE);
        return view('user.content.react', $data);
    }

    public function dislike()
    {
        $data['pageTitle'] = __('Disliked Content');
        $data['items'] = $this->searchService->getReactItem(REACT_DISLIKE);
        return view('user.content.react', $data);
    }

    public function history()
    {
        $data['pageTitle'] = __('History');
        $data['items'] = $this->searchService->getAll();
        return view('user.content.history', $data);
    }

    public function download(Request $request)
    {
        return $data['items'] = $this->searchService->downloadContent($request);
    }
    public function users(){
        $users = User::all();
        return view('user.content.users',compact('users'));
    }
}
