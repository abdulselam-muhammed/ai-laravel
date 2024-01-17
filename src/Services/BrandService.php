<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\FileManager;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\DB;

class BrandService
{
    use ResponseTrait;

    public function getAll()
    {
        return Brand::all();
    }

    public function getActiveAll()
    {
        return Brand::where('status', ACTIVE)->get();
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $id = $request->get('id', '');
            if ($id != '') {
                $ticket = Brand::findOrFail($request->id);
            } else {
                $ticket = new Brand();
            }
            $ticket->name = $request->name;
            $ticket->status = $request->status;
            $ticket->save();

             /*File Manager Call upload*/
             if ($request->hasFile('image')) {
                $new_file = FileManager::where('origin_type', 'App\Models\Brand')->where('origin_id', $ticket->id)->first();

                if ($new_file) {
                    $new_file->removeFile();
                    $upload = $new_file->updateUpload($new_file->id, 'Brand', $request->image);
                } else {
                    $new_file = new FileManager();
                    $upload = $new_file->upload('Brand', $request->image);
                }

                if ($upload['status']) {
                    $upload['file']->origin_id = $ticket->id;
                    $upload['file']->origin_type = "App\Models\Brand";
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
            $ticket = Brand::findOrFail($id);
            $ticket->delete();
            return redirect()->back()->with('success', __(DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return redirect()->back()->with('error', __(SOMETHING_WENT_WRONG));
        }
    }
}
