<?php

namespace App\Services;

use App\Models\FileManager;
use App\Models\Package;
use App\Models\UserPackage;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    use ResponseTrait;
    public function getAll()
    {
        return Package::where('status', ACTIVE)->where('is_trail', '!=', ACTIVE)->get();
    }
    public function getAllForHome()
    {
        return Package::where('status', ACTIVE)->get();
    }

    public function getById($id)
    {
        return Package::query()->findOrFail($id);
    }

    public function cancelSubscription()
    {
        return UserPackage::where(['user_id' => auth()->id(), 'user_packages.status' => ACTIVE])->whereDate('end_date', '>=', now())->update(['status' => DEACTIVATE]);
    }

    public function getCurrentPlan()
    {
        return UserPackage::where('user_id', auth()->id())->whereIn('user_packages.status', [ACTIVE, INITIATE])->whereDate('end_date', '>=', now())
                    ->select('user_packages.*')
                    ->first();
    }

    public function getLastPlan()
    {
        return UserPackage::where('user_id', auth()->id())->whereIn('user_packages.status', [ACTIVE, INITIATE])->orderBy('end_date', 'DESC')
                    ->select('user_packages.*')
                    ->first();
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $id = $request->get('id', '');
            if ($id != '') {
                $ticket = Package::findOrFail($request->id);
            } else {
                $ticket =  new Package();
            }
            $user = auth()->user();

            $ticket->user_id = $user->id;
            $ticket->title = $request->title;
            $ticket->details = $request->details;
            $ticket->topic_id = $request->topic_id;
            $ticket->save();

            if ($request->hasFile('attachments')) {
                foreach ($request->attachments as $key => $attachment) {
                    $newFile = new FileManager();
                    $upload = $newFile->upload('Package', $attachment);
                    if ($upload['status']) {
                        $upload['file']->origin_id = $ticket->id;
                        $upload['file']->origin_type = "App\Models\Package";
                        $upload['file']->save();
                    } else {
                        throw new Exception($upload['message']);
                    }
                }
            }
            DB::commit();
            $message = __(CREATED_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function getInvoice($id)
    {
        return UserPackage::where(['user_packages.id' => $id])
                    ->join('packages', 'packages.id', '=', 'user_packages.package_id')
                    ->join('orders', 'orders.id', '=', 'user_packages.order_id')
                    ->join('gateways', 'orders.gateway_id', '=', 'gateways.id')
                    ->join('users', 'users.id', '=', 'user_packages.user_id')
                    ->select('packages.name as packageName', 'users.*', 'orders.transaction_id', 'orders.total', 'orders.gateway_currency', 'orders.transaction_amount', 'user_packages.start_date as order_date', 'orders.payment_id', 'gateways.title as gatewayName')
                    ->firstOrFail();
    }

    public function updateUserPackage($id, $request)
    {
        try{
            $userPackage = UserPackage::where(['id' => $id, 'user_id' => auth()->id()])->firstOrFail();
            $userPackage->update([
                'write_languageses' => $request->write_languageses,
                'access_toneses' => $request->use_toneses,
                'status' => ACTIVE,
            ]);
            return $this->success([],  UPDATED_SUCCESSFULLY);
        }
        catch(\Exception $e){
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }
}
