<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Package;
use App\Models\SearchResult;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\UserPackage;
use App\Services\TicketService;

class DashboardController extends Controller
{
    public $ticketService;

    public function __construct()
    {
        $this->ticketService = new TicketService;
    }

    public function dashboard()
    {
        $data['totalUsers'] = User::where('role', USER_ROLE_USER)->count();
        $data['totalUseCase'] = SubCategory::count();
        $data['totalSearch'] = SearchResult::count();
        $data['totalSubscription'] = UserPackage::where('status', ACTIVE)->count();

        $data['pageTitle'] = 'Dashboard';

        $data['orders'] =  Order::query()
            ->leftJoin('packages', 'orders.package_id', '=', 'packages.id')
            ->leftJoin('gateways', 'orders.gateway_id', '=', 'gateways.id')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->select(['orders.*', 'packages.name as packageName', 'gateways.title as gatewayTitle', 'gateways.slug as gatewaySlug'])
            ->limit(10)
            ->get();
        $data['packages'] = Package::limit(10)->get();

        $fifteenDaysRegistration = User::where('created_at', '>=', \Carbon\Carbon::now()->subDays(7))
            ->where('role', USER_ROLE_USER)
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->get(array(
                DB::raw("date(created_at) as date"),
                DB::raw('COUNT(*) as "total"')
            ));

        for($i=0; $i <= 6; $i++){
            $data['fifteenDaysRegistration'][\Carbon\Carbon::now()->subDays($i)->format('l')] = $fifteenDaysRegistration->where('date', \Carbon\Carbon::now()->subDays($i)->format('Y-m-d'))->first()?->total ?? 0;
        }

        $data['fifteenDaysRegistration'] =  array_reverse($data['fifteenDaysRegistration']);
        $data['previousFifteenDays'] = array_keys($data['fifteenDaysRegistration']);
        $data['fifteenDaysRegistration'] = array_values($data['fifteenDaysRegistration']);
        $data['previousFifteenDaysCount'] = User::where('created_at', '>=', \Carbon\Carbon::now()->subDays(7))->where('role', USER_ROLE_USER)->count();

        return view('admin.dashboard')->with($data);
    }
}
