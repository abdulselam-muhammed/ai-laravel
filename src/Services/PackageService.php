<?php

namespace App\Services;

use App\Models\Package;
use App\Models\UserPackage;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\DB;

class PackageService
{
    use ResponseTrait;
    public function getAllData($request)
    {
        $packages = Package::query();

        return datatables($packages)
            ->addColumn('name', function ($package) {
                return '<h6>' . $package->name . '</h6>';
            })->addColumn('monthly_price', function ($package) {
                return currencyPrice($package->monthly_price);
            })->addColumn('yearly_price', function ($package) {
                return currencyPrice($package->yearly_price);
            })->addColumn('status', function ($package) {
                if ($package->payment_status == ORDER_PAYMENT_STATUS_PAID) {
                    return '<div class="status-btn status-btn-blue font-13 radius-4">Paid</div>';
                } elseif ($package->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
                    return '<div class="status-btn status-btn-red font-13 radius-4">Pending</div>';
                } else {
                    return '<div class="status-btn status-btn-orange font-13 radius-4">Cancelled</div>';
                }
            })->addColumn('action', function ($package) {
                return '<div class="tbl-action-btns d-inline-flex">
                    <button type="button" class="p-1 tbl-action-btn edit" data-id="' . $package->id . '" title="Edit"><span class="iconify" data-icon="clarity:note-edit-solid"></span></button>
                    <button onclick="deleteItem(\'' . route('admin.packages.destroy', $package->id) . '\', \'allDataTable\')" class="p-1 tbl-action-btn"   title="Delete"><span class="iconify" data-icon="ep:delete-filled"></span></button>
                </div>';
            })
            ->rawColumns(['name', 'status', 'action'])
            ->make(true);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $id = $request->get('id', '');
            if ($id != '') {
                $package = Package::findOrFail($request->id);
            } else {
                $package = new Package();
            }

            // Slug exists
            $slug = getSlug($request->name);
            $slugExist = Package::where('slug', $slug)->whereNot('id', $request->id)->exists();
            if ($slugExist) {
                throw new Exception('Name Already Exist');
            }

            $package->name = $request->name;
            $package->slug = $slug;
            $package->monthly_code = $request->monthly_code;
            $package->yearly_code = $request->yearly_code;
            $package->generate_characters = $request->generate_characters;
            $package->access_use_cases = array_search(-1, $request->access_use_cases) === false ? $request->access_use_cases : ["-1"];
            $package->write_languages = $request->write_languages;
            $package->access_tones = $request->access_tones;
            $package->generate_images = $request->generate_images;
            $package->plagiarism_checker = $request->plagiarism_checker;
            $package->access_community = $request->access_community;
            $package->custom_use_cases = $request->custom_use_cases;
            $package->dedicated_account = $request->dedicated_account;
            $package->support = $request->support;
            $package->device_limit = $request->device_limit;
            $package->status = $request->status;
            $package->is_trail = $request->is_trail;
            $package->monthly_price = $request->monthly_price;
            $package->yearly_price = $request->yearly_price;
            $package->save();

            //update if trail changed
            if(is_null(Package::where(['status' => ACTIVE ])->first())){
                Package::first()->update(['status' => ACTIVE]);
            }

            //update if status changed
            if(is_null(Package::where(['is_trail' => ACTIVE, 'status' => ACTIVE ])->first())){
                Package::where(['status' => ACTIVE])->first()->update(['is_trail' => ACTIVE]);
            }

            DB::commit();
            $message = $request->id ? __(UPDATED_SUCCESSFULLY) : __(CREATED_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function getInfo($id)
    {
        return Package::findOrFail($id);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            if(Package::where('status', ACTIVE)->count() > 1){
                Package::findOrFail($id)->delete();

                //update if trail changed
                if(is_null(Package::where(['is_trail' => ACTIVE, 'status' => ACTIVE ])->first())){
                    Package::where(['status' => ACTIVE])->first()->update(['is_trail' => ACTIVE]);
                }

                DB::commit();
                $message = __(DELETED_SUCCESSFULLY);
                return $this->success([], $message);
            }
            else{
                $message = __("Trail package can not be deleted");
                return $this->error([], $message);
            }
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function getUserPackagesData($request)
    {
        $userPackages = UserPackage::query()
            ->join('users', 'user_packages.user_id', '=', 'users.id')
            ->join('packages', 'user_packages.package_id', '=', 'packages.id')
            ->join('orders', 'user_packages.order_id', '=', 'orders.id')
            ->join('gateways', 'orders.gateway_id', '=', 'gateways.id')
            ->select('user_packages.*', 'users.first_name', 'users.last_name', 'packages.name as packageName', 'orders.payment_status', 'gateways.title as gatewaysName')
            ->orderBy('user_packages.id', 'desc');

        return datatables($userPackages)
            ->addColumn('user_name', function ($userPackage) {
                return  $userPackage->first_name . ' ' . $userPackage->last_name;
            })->addColumn('package_name', function ($userPackage) {
                return  $userPackage->packageName;
            })->addColumn('gateway', function ($userPackage) {
                return  $userPackage->gatewaysName;
            })->addColumn('payment_status', function ($userPackage) {
                if ($userPackage->payment_status == ORDER_PAYMENT_STATUS_PAID) {
                    return '<div class="status-btn status-btn-green font-13 radius-4">Paid</div>';
                } elseif ($userPackage->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
                    return '<div class="status-btn status-btn-red font-13 radius-4">Pending</div>';
                } else {
                    return '<div class="status-btn status-btn-orange font-13 radius-4">Cancelled</div>';
                }
            })->addColumn('start_date', function ($userPackage) {
                return  date('Y-m-d', strtotime($userPackage->start_date));
            })->addColumn('end_date', function ($userPackage) {
                return  date('Y-m-d', strtotime($userPackage->end_date));
            })->addColumn('status', function ($userPackage) {
                if ($userPackage->status == ACTIVE) {
                    return '<div class="status-btn status-btn-blue font-13 radius-4">Active</div>';
                } else {
                    return '<div class="status-btn status-btn-orange font-13 radius-4">Deactivate</div>';
                }
            })->addColumn('action', function ($userPackage) {
                return '<div class="tbl-action-btns d-inline-flex">
                    <button type="button" class="p-1 tbl-action-btn edit" data-id="' . $userPackage->id . '" title="Edit"><span class="iconify" data-icon="clarity:note-edit-solid"></span></button>
                </div>';
            })
            ->rawColumns(['user_name', 'package_name', 'payment_status', 'start_date', 'end_date', 'status', 'action'])
            ->make(true);
    }
}
