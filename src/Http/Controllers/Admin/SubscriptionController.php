<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    use ResponseTrait;
    public $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService;
    }

    public function orders(Request $request)
    {
        if ($request->ajax()) {
            return $this->orderService->getAllData($request);
        } else {
            $data['pageTitle'] = __('All Orders');
            return view('admin.subscriptions.orders', $data);
        }
    }

    public function ordersStatus(Request $request)
    {
        if ($request->ajax()) {
            return $this->orderService->getByStatus($request);
        }
    }

    public function orderGetInfo(Request $request)
    {
        $data = $this->orderService->orderGetInfo($request->id);
        return $this->success($data);
    }

    public function orderPaymentStatusChange(Request $request)
    {
        return $this->orderService->orderPaymentStatusChange($request);
    }
}
