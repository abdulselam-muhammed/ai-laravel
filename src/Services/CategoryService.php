<?php

namespace App\Services;

use App\Models\Category;
use App\Models\FileManager;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    use ResponseTrait;

    public function getAll()
    {
        return Category::all();
    }

    public function getActiveAll()
    {
        return Category::where('status', ACTIVE)->get();
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $id = $request->get('id', '');
            if ($id != '') {
                $category = Category::findOrFail($request->id);
            } else {
                $category = new Category();
            }
            $category->name = $request->name;
            $category->status = $request->status;
            $category->save();

              /*File Manager Call upload*/
              if ($request->hasFile('image')) {
                $new_file = FileManager::where('origin_type', 'App\Models\Category')->where('origin_id', $category->id)->first();

                if ($new_file) {
                    $new_file->removeFile();
                    $upload = $new_file->updateUpload($new_file->id, 'Category', $request->image);
                } else {
                    $new_file = new FileManager();
                    $upload = $new_file->upload('Category', $request->image);
                }

                if ($upload['status']) {
                    $upload['file']->origin_id = $category->id;
                    $upload['file']->origin_type = "App\Models\Category";
                    $upload['file']->save();
                } else {
                    throw new Exception($upload['message']);
                }
            }
            /*End*/

            DB::commit();
            $message = $request->id ? __(UPDATED_SUCCESSFULLY) : __(CREATED_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function delete($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();
            return redirect()->back()->with('success', __(DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return redirect()->back()->with('error', __(SOMETHING_WENT_WRONG));
        }
    }
}
