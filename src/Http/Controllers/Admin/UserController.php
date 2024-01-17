<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\UsersService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ResponseTrait;
    public $usersService;

    public function __construct()
    {
        $this->usersService = new UsersService;
    }
    public function index()
    {
        $data['pageTitle'] = "User";
        $data['subUserActiveClass'] = 'active';
        $data['users'] = User::all();
        return view('admin.setting.user')->with($data);
    }

    public function store(UserRequest $request)
    {
        try {
            $user = new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->role = $request->role;
            $user->contact_number = $request->contact_number;
            $user->status = $request->status;
            $user->save();
        } catch (\Exception $e) {
            return  redirect()->back()->with('success', $e->getMessage());
        }

        return  redirect()->back()->with('success', 'Created Successfully');
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            if (auth()->id() != $user->id && User::whereRole(USER_ROLE_ADMIN)->first()->id != $user->id) {
                $user->delete();
                return  redirect()->back()->with('success', __('Deleted Successfully'));
            }

            return redirect()->back()->with('info', __('You can\'t delete this data'));
        } catch (\Exception $e) {
            return redirect()->back()->with('info', $e->getMessage());
        }
    }

    public function list(Request $request)
    {
        if ($request->ajax()) {
            return $this->usersService->getAllUsersData($request);
        } else {
            $data['pageTitle'] = __('Users');
            return view('admin.users.list', $data);
        }
    }

    public function getInfo(Request $request)
    {
        $data = $this->usersService->getInfo($request->id);
        return $this->success($data);
    }

    public function status(Request $request)
    {
        return $this->usersService->statusChange($request);
    }
}
