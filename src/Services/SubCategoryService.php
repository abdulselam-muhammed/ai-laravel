<?php

namespace App\Services;

use App\Models\FileManager;
use App\Models\SubCategory;
use App\Models\UserFavorite;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\DB;

class SubCategoryService
{
    use ResponseTrait;

    public function getAll()
    {
        return SubCategory::all();
    }

    public function getActiveAll()
    {
        return SubCategory::where('status', ACTIVE)->get();
    }

    public function getById($id)
    {
        return SubCategory::query()->findOrFail($id);;
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $id = $request->get('id', '');
            if ($id != '') {
                $subCategory = SubCategory::findOrFail($request->id);
            } else {
                $subCategory = new SubCategory();
            }
            $data = [];
            $prompt = $request->prompt;
            if ($request->items == null) {
                throw new Exception('Content can\'t be empty');
            }
            for ($i = 0; $i < count($request->items['types']); $i++) {
                $name = strtolower(preg_replace("/\s+/", "_", $request->items['labels'][$i]));
                $data[] = [
                    'label' => $request->items['labels'][$i],
                    'type' => $request->items['types'][$i],
                    'name' => $name,
                    'placeholder' => $request->items['placeholders'][$i] ?? 'Placeholder'
                ];

                $prompt = str_replace("#" . $request->items['labels'][$i] . "#", '#' . $name . '#', $prompt);
            };
            $subCategory->name = $request->name;
            $subCategory->content = json_encode($data);
            $subCategory->category_id = $request->category_id;
            $subCategory->status = $request->status;
            $subCategory->prompt = $prompt;
            $subCategory->summery = $request->summery;
            $subCategory->save();

            /*File Manager Call upload*/
            if ($request->hasFile('image')) {
                $new_file = FileManager::where('origin_type', 'App\Models\SubCategory')->where('origin_id', $subCategory->id)->first();

                if ($new_file) {
                    $new_file->removeFile();
                    $upload = $new_file->updateUpload($new_file->id, 'SubCategory', $request->image);
                } else {
                    $new_file = new FileManager();
                    $upload = $new_file->upload('SubCategory', $request->image);
                }

                if ($upload['status']) {
                    $upload['file']->origin_id = $subCategory->id;
                    $upload['file']->origin_type = "App\Models\SubCategory";
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
            $ticket = SubCategory::findOrFail($id);
            $ticket->delete();
            return redirect()->back()->with('success', __(DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function favoriteTemplate($request)
    {
        DB::beginTransaction();
        try {
            $resultItem = UserFavorite::where(['user_id' => auth()->id(), 'sub_category_id' => $request->id])->first();
            if (is_null($resultItem)) {
                UserFavorite::create([
                    'user_id' => auth()->id(),
                    'sub_category_id' => $request->id,
                ]);
                $message = __("Template favorite successfully");
            } else {
                UserFavorite::where([
                    'user_id' => auth()->id(),
                    'sub_category_id' => $request->id,
                ])->delete();
                $message = __("Template unfavorite successfully");
            }

            DB::commit();
            return $this->success([], $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }
}
