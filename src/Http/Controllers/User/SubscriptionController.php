<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserPackageRequest;
use App\Models\Bank;
use App\Models\UserPackage;
use App\Services\GatewayService;
use App\Services\OrderService;
use App\Services\SubscriptionService;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;

class   SubscriptionController extends Controller
{
    use ResponseTrait;
    public $subscriptionService;

    public function __construct()
    {
        $this->subscriptionService = new SubscriptionService;
    }

    public function index()
    {
        $data['pageTitle'] = __('My Subscription');
        $data['userPlan'] = $this->subscriptionService->getCurrentPlan();
        $orderService = new OrderService;
        $data['invoices'] = $orderService->getAllOrderByUser()->getData()->data;
        return view('user.subscriptions.index', $data);
    }

    public function payment()
    {
        $data['pageTitle'] = __('My Subscription');
        $data['userPlan'] = $this->subscriptionService->getCurrentPlan();
        $orderService = new OrderService;
        $data['invoices'] = $orderService->getAllOrderByUser()->getData()->data;
        return view('user.subscriptions.payment', $data);
    }

    public function getPlan()
    {
        $data['plans'] = $this->subscriptionService->getAll();
        $data['currentPlan'] = $this->subscriptionService->getCurrentPlan();
        return view('user.subscriptions.partials.plan-list', $data)->render();
    }

    public function cancelSubscription()
    {
        $this->subscriptionService->cancelSubscription();
        return back()->with('success', __('Canceled Successful!'));
    }

    public function subscriptionOrder(Request $request)
    {
        $gateWayService = new GatewayService;
        $data['gateways'] = $gateWayService->getActiveAll();
        $data['plan'] = $this->subscriptionService->getById($request->id);
        $data['durationType'] = $request->duration_type;
        $data['banks'] = Bank::where('status', ACTIVE)->get();
        $data['startDate'] = now();
        if ($request->duration_type == PACKAGE_DURATION_TYPE_MONTHLY) {
            $data['endDate'] = Carbon::now()->addMonth();
        } else {
            $data['endDate'] = Carbon::now()->addYear();
        }

        return view('user.subscriptions.partials.gateway-list', $data)->render();
    }

    public function getCurrencyByGateway(Request $request)
    {
        $gateWayService = new GatewayService;
        $data = $gateWayService->getCurrencyByGatewayId($request->id);
        return $this->success($data);
    }

    public function invoicePrint($id)
    {
        $data['invoice'] = $this->subscriptionService->getInvoice($id);
        return view('user.subscriptions.print', $data);
    }

    public function userPackage($id, UserPackageRequest $request)
    {
        return $this->subscriptionService->updateUserPackage($id, $request);
    }
}
