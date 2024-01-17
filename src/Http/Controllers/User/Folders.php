<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use App\Models\Folder;
use App\Models\Category;
use Carbon\Carbon;
use App\Models\FileManager;
use App\Models\User;
use App\Models\Document;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Services\SubscriptionService;

class Folders extends Controller
{
    public $subscriptionService;

    public function __construct()
    {
        $this->subscriptionService = new SubscriptionService;
    }
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required'
        ]);

        $check_title = Folder::where('title', $request->title)->where('Folder_id', null)->first();

        if ($check_title) {
            return response()->json(['success' => false, 'msg' => 'This title already exists']);
        } else {
            $folder = Folder::create([
                'title' => $request->title,
                'User_id' => Auth::id()
            ]);

            return response()->json(['success' => true, 'msg' => 'Created Successfully', 'folder_id' => $folder->id, 'created_at' => $folder->created_at, 'title' => $folder->title]);
        }
    }

    public function Folder_store(Request $request, $Folder_id)
    {
        request()->validate([
            'title' => 'required'
        ]);
        $fathers = array();
        $chek_title = Folder::where('title', $request->title)->where('Folder_id', $Folder_id)->first();
        if ($chek_title) {
            return response()->json(['success' => false, 'msg' => 'This title already exists']);
        } else {
            $folder = Folder::findOrFail($Folder_id);
            if ($folder->Folder_id == null) {
                $folder =  Folder::create([
                    'title' => $request->title,
                    'User_id' => Auth::id(),
                    'Folder_id' => $Folder_id,
                    'Father_id' => $Folder_id
                ]);
                return response()->json(['success' => true, 'msg' => 'Created Successfully', 'folder_id' => $folder->id, 'created_at' => $folder->created_at, 'title' => $folder->title]);
            } else {
                if ($Folder_id == $folder->Father_id) {
                    $folder =  Folder::create([
                        'title' => $request->title,
                        'User_id' => Auth::id(),
                        'Folder_id' => $Folder_id,
                        'Father_id' => $folder->Father_id
                    ]);
                    return response()->json(['success' => true, 'msg' => 'Created Successfully', 'folder_id' => $folder->id, 'created_at' => $folder->created_at, 'title' => $folder->title]);
                } else {
                    array_push($fathers, $folder->Father_id, $Folder_id);
                    $folder = Folder::create([
                        'title' => $request->title,
                        'User_id' => Auth::id(),
                        'Folder_id' => $Folder_id,
                        'Father_id' => implode(',', $fathers)
                    ]);
                    return response()->json(['success' => true, 'msg' => 'Created Successfully', 'folder_id' => $folder->id, 'created_at' => $folder->created_at, 'title' => $folder->title]);
                }
            }
        }
    }
    public function show($folder_id)
    {
        $data['userPlan'] = $this->subscriptionService->getLastPlan();
        $folder = Folder::findOrFail($folder_id);
        $folders_ = Folder::where('Folder_id', $folder_id)->get();
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $folder->created_at);
        $month_name = $date->formatLocalized('%B');
        $guide = 'From Show Function';

        $f_folders = Folder::where('User_id', Auth::id())->where('favorite', 1)->get();
        $all_folders = Folder::all();

        $categoryId = null;
        $locale = app()->getLocale();
        $data['pageTitle'] = __('Search');
        $subCategories = Category::where('sub_categories.status', ACTIVE)
            ->where('categories.status', ACTIVE)
            ->whereIn('sub_categories.id', getLimit(RULES_USE_CASE))
            ->join('sub_categories', 'sub_categories.category_id', '=', 'categories.id')
            ->leftJoin('file_managers', ['file_managers.origin_id' =>  'sub_categories.id', 'file_managers.origin_type' => DB::raw("'App\\\Models\\\SubCategory'")])
            ->select("sub_categories.*", "sub_categories.name_$locale as name_locale", "categories.name_$locale as category_name", DB::raw("CONCAT(file_managers.folder_name,'/',file_managers.file_name) AS sub_category_icon"))
            ->get();
        $data['categories'] = $subCategories->groupBy('category_id');
        $data['subCategoryId'] = $categoryId;

        return view('user.dashboard', $data, compact('folder', 'folders_', 'guide', 'month_name', 'f_folders', 'all_folders'));
    }


    public function destroy(Request $request)
    {
        
        // return $request->all();
        if(isset($request->Folder_id)){
        $Folder_id =$request->Folder_id;
        $folder = Folder::findOrFail($Folder_id);
        $find_father = Folder::where('Father_id', 'like', "%$Folder_id%")->get();
        $find_by_document = Document::where('Father_id', 'like', "%$Folder_id%")->get();

        if (count($find_father) != 0) {
            $find_father = Folder::where('Father_id', 'like', "%$Folder_id%")->delete();
            $folder = Folder::findOrFail($Folder_id);
            $folder->delete();
        } else {
            $folder = Folder::findOrFail($Folder_id);
            $folder->delete();
        }
        $find_document = Document::where('Father_id', 'like', "%$Folder_id%")->get();
        if (count($find_document) != 0) {
            Document::where('Father_id', 'like', "%$Folder_id%")->delete();
        }
        return response()->json(['folder_id'=>$Folder_id]);
        }else{
            return 'id no';
        }
        // return redirect::back()->withErrors(['msg' => 'Deleted Seuccesfully']);
    }

    public function result_after_delete(){
        return view('user.result_folder_after_delete');
    }
    public function favorite($id, $favorite)
    {
        Folder::where('id', $id)->update(array(
            'favorite' => $favorite
        ));
        if ($favorite == 1) {
            return redirect::back()->withErrors(['msg' => 'Favorited']);
        } else {
            return redirect::back()->withErrors(['msg' => 'Removed from favourites']);
        }
    }

    public function rename(Request $request, $Folder_id)
    {

        if ($request->title) {
            Folder::where('id', $Folder_id)->update(array('title' => $request->title));
            return redirect::back()->withErrors(['msg' => "rename to $request->title"]);
        } else {
            return '10001-Erorr'; //hata inputtan bir bilgi gelmedi
        }
    }

    public function move(Request $request, $id)
    {

        $father_folder_data = Folder::where('id', $request->new_father_id)->first();

        if ($request->new_father_id == 'home') {
            Folder::where('id', $id)->update(array('Folder_id' => null, 'Father_id' => null));
        } elseif ($father_folder_data->Folder_id == null) {
            Folder::where('id', $id)->update(array('Folder_id' => $father_folder_data->id, 'Father_id' => $father_folder_data->id));
        } else {
            Folder::where('id', $id)->update(array('Folder_id' => $father_folder_data->id, 'Father_id' => $father_folder_data->Father_id . ',' . $father_folder_data->id));
        }

        return redirect::back()->withErrors(['msg' => 'Moved seccessfully']);
    }
}
