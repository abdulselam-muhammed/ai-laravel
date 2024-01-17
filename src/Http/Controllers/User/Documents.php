<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use App\Models\Document;
use App\Models\FileManager;
use App\Models\User;
use App\Models\Folder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class Documents extends Controller
{
    /**
     * For Create new folder in home page not in folder.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required'
        ]);

        $check_title = Document::where('title', $request->title)->where('Folder_id', null)->first();

        if ($check_title) {
            return response()->json(['success' => false, 'msg' => 'This title already exists']);
        } else {
            $document = Document::create([
                'title' => $request->title,
                'User_id' => Auth::id()
            ]);

            return response()->json(['success' => true, 'msg' => 'Created Successfully', 'document_id' => $document->id, 'created_at' => $document->created_at, 'title' => $document->title]);
        }
    }

    /**
     * For create new document in folder.
     */
    public function store_in_folder(Request $request, $folder_id)
    {
        $request->validate([
            'title' => 'required'
        ]);

        $fathers = array();
        $check_title = Document::where('title', $request->title)->where('Folder_id', $folder_id)->first();

        if ($check_title) {
            return response()->json(['msg' => 'This Title is Already Exist'], 400);
        }

        $folder = Folder::findOrFail($folder_id);

        if ($folder->Folder_id == null) {
            $document = Document::create([
                'title' => $request->title,
                'Folder_id' => $folder_id,
                'Father_id' => $folder_id,
                'User_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'msg' => 'Created Successfully',
                'document_id' => $document->id,
                'created_at' => $document->created_at,
                'title' => $document->title
            ]);
        } else {
            array_push($fathers, $folder->Father_id, $folder_id);

            $document = Document::create([
                'title' => $request->title,
                'Folder_id' => $folder_id,
                'Father_id' => implode(',', $fathers),
                'User_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'msg' => 'Created Successfully',
                'document_id' => $document->id,
                'created_at' => $document->created_at,
                'title' => $document->title
            ]);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $document = Document::findOrFail($id);
        return view('user.content.Star.Document.text', compact('document'));
    }
    /**
     * For insert and update the text content.
     */
    public function insert_content(Request $request, $document_ud)
    {

        $convertedText = mb_convert_encoding($request->contnet, 'UTF-8', 'auto');
        Document::where('id', $document_ud)->update(array(
            'content' => $convertedText
        ));
        return redirect::back()->withErrors(['msg' => 'Seuccessfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $document = Document::FindOrFail($id);
        $document->delete();
        return redirect::back()->withErrors(['msg' => 'Deleted Seuccesfully']);
    }

    public function favorite($id, $favorite)
    {
        Document::where('id', $id)->update(array(
            'favorite' => $favorite
        ));
        if ($favorite == 1) {
            return redirect::back()->withErrors(['msg' => 'Favorited']);
        } else {
            return redirect::back()->withErrors(['msg' => 'Removed from favourites']);
        }
    }

    public function rename(Request $request , $Document_id){

        if($request->title){
            Document::where('id',$Document_id)->update(array('title'=>$request->title));
            return redirect::back()->withErrors(['msg'=>"rename to $request->title"]);
        }else{
            return '10001-Erorr'; //hata inputtan bir bilgi gelmedi
        } 
        
    }

    public function move(Request $request , $id){
        
        $father_folder_data = Folder::where('id',$request->new_father_id)->first();

        if($request->new_father_id == 'home'){
            Document::where('id',$id)->update(array('Folder_id'=>null, 'Father_id'=>null));
        }
        elseif($father_folder_data->Folder_id == null){
            Document::where('id',$id)->update(array('Folder_id'=>$father_folder_data->id, 'Father_id'=>$father_folder_data->id));
        }
        else{
            Document::where('id',$id)->update(array('Folder_id'=>$father_folder_data->id,'Father_id'=>$father_folder_data->Father_id.','.$father_folder_data->id));
        }

        return redirect::back()->withErrors(['msg'=>'Moved seccessfully']);
        
    }
}
