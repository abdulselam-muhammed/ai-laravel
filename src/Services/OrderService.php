<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Package;
use App\Models\UserPackage;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\DB;

class OrderService
{
    use ResponseTrait;

    public function getAllData($request)
    {
        $orders = Order::query()
            ->leftJoin('packages', 'orders.package_id', '=', 'packages.id')
            ->leftJoin('gateways', 'orders.gateway_id', '=', 'gateways.id')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('file_managers', ['orders.deposit_slip_id' => 'file_managers.id', 'file_managers.origin_type' => (DB::raw("'App\\\Models\\\Order'"))])
            ->select(['orders.*', 'packages.name as packageName', 'gateways.title as gatewayTitle', 'gateways.slug as gatewaySlug', 'file_managers.file_name', 'file_managers.folder_name']);

        return datatables($orders)
            ->addIndexColumn()
            ->addColumn('package', function ($order) {
                return '<h6>' . $order->packageName . '</h6>';
            })->addColumn('amount', function ($order) {
                return currencyPrice($order->total);
            })->addColumn('gateway', function ($order) {
                if ($order->gatewaySlug == 'bank') {
                    return  '<a href="' . getFileUrl($order->folder_name, $order->file_name) . '" title="Bank slip download" download>' . $order->gatewayTitle . '</a>';
                }
                return $order->gatewayTitle;
            })->addColumn('status', function ($order) {
                if ($order->payment_status == ORDER_PAYMENT_STATUS_PAID) {
                    return '<div class="status-btn status-btn-blue font-13 radius-4">Paid</div>';
                } elseif ($order->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
                    return '<div class="status-btn status-btn-red font-13 radius-4">Pending</div>';
                } else {
                    return '<div class="status-btn status-btn-orange font-13 radius-4">Cancelled</div>';
                }
            })->addColumn('action', function ($order) {
                $html = '<div class="tbl-action-btns d-inline-flex">';
                if ($order->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
                    $html .= '<button type="button" class="p-1 tbl-action-btn view" data-id="' . $order->id . '" title="View"><span class="iconify" data-icon="carbon:view-filled"></span></button>';
                    if ($order->gatewaySlug == 'bank') {
                        $html .=  '<a href="' . getFileUrl($order->folder_name, $order->file_name) . '"  class="p-1 tbl-action-btn" title="Bank slip download" download><span class="iconify" data-icon="fa6-solid:download"></span></a>';
                        $html .= '<button type="button" class="p-1 tbl-action-btn orderPayStatus" data-id="' . $order->id . '" title="Payment Status Change"><span class="iconify" data-icon="fluent:text-change-previous-20-filled"></span></button>';
                    }
                } elseif ($order->payment_status == ORDER_PAYMENT_STATUS_PAID) {
                    $html .=  '<button type="button" class="p-1 tbl-action-btn view" data-id="' . $order->id . '" title="View"><span class="iconify" data-icon="carbon:view-filled"></span></button>';
                }
                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['package', 'status', 'gateway', 'action'])
            ->make(true);
    }

    public function getByStatus($request)
    {
        $status = 0;
        if ($request->status == 'paid') {
            $status = ORDER_PAYMENT_STATUS_PAID;
        } else if ($request->status == 'pending') {
            $status = ORDER_PAYMENT_STATUS_PENDING;
        } else if ($request->status == 'bank') {
            $status = ORDER_PAYMENT_STATUS_PENDING;
        } else if ($request->status == 'cancelled') {
            $status = ORDER_PAYMENT_STATUS_CANCELLED;
        }
        $orders = Order::query()
            ->leftJoin('packages', 'orders.package_id', '=', 'packages.id')
            ->leftJoin('gateways', 'orders.gateway_id', '=', 'gateways.id')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('file_managers', ['orders.deposit_slip_id' => 'file_managers.id', 'file_managers.origin_type' => (DB::raw("'App\\\Models\\\Order'"))]);
        if ($request->status == 'bank') {
            $orders->whereNotNull('orders.deposit_slip_id');
        }
        $orders = $orders->select(['orders.*', 'packages.name as packageName', 'gateways.title as gatewayTitle', 'gateways.slug as gatewaySlug', 'file_managers.file_name', 'file_managers.folder_name'])
            ->where('orders.payment_status', $status);

        return datatables($orders)
            ->addIndexColumn()
            ->addColumn('package', function ($order) {
                return '<h6>' . $order->packageName . '</h6>';
            })->addColumn('amount', function ($order) {
                return currencyPrice($order->total);
            })->addColumn('gateway', function ($order) {
                if ($order->gatewaySlug == 'bank') {
                    return  '<a href="' . getFileUrl($order->folder_name, $order->file_name) . '" title="Bank slip download" download>' . $order->gatewayTitle . '</a>';
                }
                return $order->gatewayTitle;
            })->addColumn('status', function ($order) {
                if ($order->payment_status == ORDER_PAYMENT_STATUS_PAID) {
                    return '<div class="status-btn status-btn-blue font-13 radius-4">Paid</div>';
                } elseif ($order->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
                    return '<div class="status-btn status-btn-red font-13 radius-4">Pending</div>';
                } else {
                    return '<div class="status-btn status-btn-orange font-13 radius-4">Cancelled</div>';
                }
            })->addColumn('action', function ($order) {
                $html = '<div class="tbl-action-btns d-inline-flex">';
                if ($order->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
                    $html .= '<button type="button" class="p-1 tbl-action-btn view" data-id="' . $order->id . '" title="View"><span class="iconify" data-icon="carbon:view-filled"></span></button>';
                    if ($order->gatewaySlug == 'bank') {
                        $html .=  '<a href="' . getFileUrl($order->folder_name, $order->file_name) . '"  class="p-1 tbl-action-btn" title="Bank slip download" download><span class="iconify" data-icon="fa6-solid:download"></span></a>';
                        $html .= '<button type="button" class="p-1 tbl-action-btn orderPayStatus" data-id="' . $order->id . '" title="Payment Status Change"><span class="iconify" data-icon="fluent:text-change-previous-20-filled"></span></button>';
                    }
                } elseif ($order->payment_status == ORDER_PAYMENT_STATUS_PAID) {
                    $html .=  '<button type="button" class="p-1 tbl-action-btn view" data-id="' . $order->id . '" title="View"><span class="iconify" data-icon="carbon:view-filled"></span></button>';
                }
                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['package', 'status', 'gateway', 'action'])
            ->make(true);
    }

    public function orderGetInfo($id)
    {
        try {
            // return Order::findOrFail($id);
            return Order::query()
                ->join('gateways', 'orders.gateway_id', '=', 'gateways.id')
                ->select(['orders.*', 'gateways.title as gatewayTitle'])
                ->where('orders.id', $id)
                ->first();
        } catch (Exception $e) {
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function orderPaymentStatusChange($request)
    {
        DB::beginTransaction();
        try {
            $order = Order::findOrFail($request->id);
            if ($request->status == ORDER_PAYMENT_STATUS_PAID) {
                $order->payment_status = ORDER_PAYMENT_STATUS_PAID;
                $duration = 0;
                if ($order->duration_type == PACKAGE_DURATION_TYPE_MONTHLY) {
                    $duration = 30;
                } elseif ($order->duration_type == PACKAGE_DURATION_TYPE_YEARLY) {
                    $duration = 365;
                }
                $package = Package::find($order->package_id);
                setUserPackage($order->user_id, $package, $duration, $order->id);
            } elseif ($request->status == ORDER_PAYMENT_STATUS_CANCELLED) {
                $order->payment_status = ORDER_PAYMENT_STATUS_CANCELLED;
            } else {
                $order->payment_status = ORDER_PAYMENT_STATUS_PENDING;
            }
            $order->save();
            DB::commit();
            $message = __(UPDATED_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function getAllOrderByUser($userId = null)
    {
        $userId = !is_null($userId) ? $userId : auth()->id();
        $orders = UserPackage::query()
            ->join('packages', 'user_packages.package_id', '=', 'packages.id')
            ->join('orders', 'orders.id', '=', 'user_packages.order_id')
            ->where('user_packages.user_id', $userId)
            ->select(['user_packages.*', 'packages.name as packageName', 'orders.total'])
            ->get();
        return $this->success($orders);
    }
}
