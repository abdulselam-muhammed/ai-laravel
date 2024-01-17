<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SearchResult;
use App\Models\SearchResultItem;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class   DashboardController extends Controller
{
    public $subscriptionService;

    public function __construct()
    {
        $this->subscriptionService = new SubscriptionService;
    }

    public function dashboard()
    {
        $authId = auth()->id();
        $data['pageTitle'] = __('Dashboard');
        $data['totalSearchResult'] = SearchResult::query()->where('user_id', $authId)->count();
        $data['totalRemainingCharacter'] = getLimit(RULES_CHARACTER, $authId);
        $data['planRemainingDays'] = getLimit(RULES_PLAN_REMAINING_DAYS, $authId);
        $data['userPlan'] = $this->subscriptionService->getLastPlan();

        $documentMonthlyResult = SearchResultItem::query()
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%d-%m-%Y')"))
            ->whereMonth('created_at', date('m'))
            ->select(DB::raw('COUNT(*) as "total_items"'), DB::raw("DATE_FORMAT(created_at, '%e') as day"))
            ->where('user_id', $authId)
            ->get();
        $data['currentMonthDays'] = range(1, date('t') - 15);
        $documentMonthlyResultByDay = [];
        foreach ($data['currentMonthDays'] as $day) {
            foreach ($documentMonthlyResult as $result) {
                if ($result->day == $day) {
                    array_push($documentMonthlyResultByDay, $result->total_items);
                } else {
                    array_push($documentMonthlyResultByDay, 0);
                }
            }
        }
        $data['documentMonthlyResult'] = $documentMonthlyResultByDay;

        $documents = Document::where('User_id',Auth::id())->where('Folder_id',null)->get();
        $folders  = Folder::where('User_id',Auth::id())->where('Folder_id',null)->get();

        
        $f_documents = Document::where('User_id',Auth::id())->where('favorite',1)->get();
        $f_folders   = Folder::where('User_id',Auth::id())->where('favorite',1)->get();
        $all_folders = Folder::all();

        $categoryId=null;
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

        return view('user.dashboard', $data,compact('documents','folders','all_folders','f_folders','f_documents'));
    }

    public function result_test(){
        $authId = auth()->id();
        $data['pageTitle'] = __('Dashboard');
        $data['totalSearchResult'] = SearchResult::query()->where('user_id', $authId)->count();
        $data['totalRemainingCharacter'] = getLimit(RULES_CHARACTER, $authId);
        $data['planRemainingDays'] = getLimit(RULES_PLAN_REMAINING_DAYS, $authId);
        $data['userPlan'] = $this->subscriptionService->getLastPlan();

        $documentMonthlyResult = SearchResultItem::query()
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%d-%m-%Y')"))
            ->whereMonth('created_at', date('m'))
            ->select(DB::raw('COUNT(*) as "total_items"'), DB::raw("DATE_FORMAT(created_at, '%e') as day"))
            ->where('user_id', $authId)
            ->get();
        $data['currentMonthDays'] = range(1, date('t') - 15);
        $documentMonthlyResultByDay = [];
        foreach ($data['currentMonthDays'] as $day) {
            foreach ($documentMonthlyResult as $result) {
                if ($result->day == $day) {
                    array_push($documentMonthlyResultByDay, $result->total_items);
                } else {
                    array_push($documentMonthlyResultByDay, 0);
                }
            }
        }
        $data['documentMonthlyResult'] = $documentMonthlyResultByDay;

        $documents = Document::where('User_id',Auth::id())->where('Folder_id',null)->get();
        $folders  = Folder::where('User_id',Auth::id())->where('Folder_id',null)->get();

        
        $f_documents = Document::where('User_id',Auth::id())->where('favorite',1)->get();
        $f_folders   = Folder::where('User_id',Auth::id())->where('favorite',1)->get();
        $all_folders = Folder::all();

        $categoryId=null;
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

        return view('user.result_test',$data,compact('documents','folders','all_folders','f_folders','f_documents'));
    }
}
