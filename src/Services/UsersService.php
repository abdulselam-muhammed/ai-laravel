<?php

namespace App\Services;

use App\Models\User;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\DB;

class UsersService
{
    use ResponseTrait;

    public function getAllUsersData($request)
    {
        $users = User::query()
            ->where('role', USER_ROLE_USER);

        return datatables($users)
            ->addIndexColumn()
            ->addColumn('name', function ($user) {
                return '<h6>' . $user->name . '</h6>';
            })->addColumn('status', function ($user) {
                if ($user->status == ACTIVE) {
                    return '<div class="status-btn status-btn-blue font-13 radius-4">Active</div>';
                } else {
                    return '<div class="status-btn status-btn-orange font-13 radius-4">Deactivate</div>';
                }
            })->addColumn('action', function ($user) {
                $html = '<div class="tbl-action-btns d-inline-flex">';
                $html .= '<button type="button" class="p-1 tbl-action-btn statusBtn" data-id="' . $user->id . '" title="Status Change"><span class="iconify" data-icon="fluent:text-change-previous-20-filled"></span></button>';
                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['name', 'status', 'action'])
            ->make(true);
    }

    public function getInfo($id)
    {
        return User::findOrFail($id);
    }

    public function statusChange($request)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($request->id);
            $user->status = $request->status == ACTIVE ? ACTIVE : DEACTIVATE;
            $user->save();
            DB::commit();
            $message = __(UPDATED_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }
}
