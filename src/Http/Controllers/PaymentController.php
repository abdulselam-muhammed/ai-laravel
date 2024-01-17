<?php

namespace App\Http\Controllers;

use App\Models\User;

use App\Http\Requests\CheckoutRequest;
use App\Models\Bank;
use App\Models\Currency;
use App\Models\FileManager;
use App\Models\Gateway;
use App\Models\GatewayCurrency;
use App\Models\Order;
use App\Models\Package;
use App\Models\UserPackage;
use App\Services\Payment\Payment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Iyzipay\Model\Address;
use Iyzipay\Model\BasketItem;
use Iyzipay\Model\BasketItemType;
use Iyzipay\Model\Buyer;
use Iyzipay\Model\Locale;
use Iyzipay\Model\PaymentGroup;
use Iyzipay\Options;
use Iyzipay\Request\CreateCheckoutFormInitializeRequest;
use \Iyzipay\Model\Subscription\SubscriptionPricingPlan;
use Iyzipay\Request\Subscription\SubscriptionCreateCheckoutFormRequest;
use Iyzipay\Model\Subscription\SubscriptionCreateCheckoutForm;
use Iyzipay\Model\Customer;

class PaymentController extends Controller
{
    public function checkout(CheckoutRequest $request)
    {
        $payment_id = uniqid();
        $package = Package::findOrFail($request->package_id);
        $durationType = $request->duration_type == PACKAGE_DURATION_TYPE_MONTHLY ? PACKAGE_DURATION_TYPE_MONTHLY : PACKAGE_DURATION_TYPE_YEARLY;
        $gateway = Gateway::where(['slug' => $request->gateway, 'status' => ACTIVE])->firstOrFail();
        $gatewayCurrency = GatewayCurrency::where(['gateway_id' => $gateway->id, 'currency' => $request->currency])->firstOrFail();

        $price = 0;
        if ($durationType == PACKAGE_DURATION_TYPE_MONTHLY) {
            $price = $package->monthly_price;
        } else {
            $price = $package->yearly_price;
        }

        $order = Order::create([
            'payment_id' => $payment_id,
            'user_id' => auth()->id(),
            'package_id' => $package->id,
            'amount' => $price,
            'system_currency' => Currency::where('current_currency', 'on')->first()->currency_code,
            'gateway_id' => $gateway->id,
            'duration_type' => $durationType,
            'gateway_currency' => $gatewayCurrency->currency,
            'conversion_rate' => $gatewayCurrency->conversion_rate,
            'subtotal' => $price,
            'total' => $price,
            'transaction_amount' => $price * $gatewayCurrency->conversion_rate,
            'payment_status' => ORDER_PAYMENT_STATUS_PENDING,
            'bank_id' => NULL,
            'bank_name' => NULL,
            'bank_account_number' => NULL,
            'deposit_by' => NULL,
            'deposit_slip_id' => NULL
        ]);

        if ($order->save()) {
            session()->put('payment_id', $payment_id);

            $paymentForm = $this->getPaymentForm($payment_id, $package, $durationType, $gatewayCurrency);
            if ($paymentForm) {
                return view('user.subscriptions.payment')->with('paymentForm', $paymentForm);
            } else {
                return back()->with('error', __('Payment form could not be created.'));
            }
        } else {
            return back()->with('error', __('Failed to create order.'));
        }
    }

    public function getPaymentForm($payment_id, $package, $durationType, $gatewayCurrency)
    {
        $user = User::find(auth()->id());
        $ref_code = "";
        if ($durationType == PACKAGE_DURATION_TYPE_MONTHLY) {
            $ref_code = $package->monthly_code;
        } else {
            $ref_code = $package->yearly_code;
        }

        $options = new Options();
        $options->setApiKey('Bhttu8RS6aW07pHs');
        $options->setSecretKey('b2gj692qALRbxhvOywNH1YmWUqsODg6f');
        $options->setBaseUrl('https://sandbox-api.iyzipay.com');

        $request = new SubscriptionCreateCheckoutFormRequest();
        $request->setLocale("tr");
        $request->setPricingPlanReferenceCode($ref_code);
        $request->setSubscriptionInitialStatus("ACTIVE");
        $request->setCallbackUrl("127.0.0.1:800/callback");
        $customer = new Customer();
        $customer->setName($user->first_name ?? "VERİ YOK");
        $customer->setSurname($user->last_name ?? "VERİ YOK");
        $customer->setGsmNumber($user->contact_number ?? "VERİ YOK");
        $customer->setEmail($user->email ?? "VERİ YOK");
        $customer->setIdentityNumber("11111111111");
        $customer->setShippingContactName($user->first_name && $user->last_name ? $user->first_name . " " . $user->last_name : "VERİ YOK");
        $customer->setShippingCity($user->city ?? "VERİ YOK");
        $customer->setShippingCountry($user->country ?? "VERİ YOK");
        $customer->setShippingAddress($user->address ?? "VERİ YOK");
        $customer->setShippingZipCode($user->postcode ?? "VERİ YOK");
        $customer->setBillingContactName($user->first_name && $user->last_name ? $user->first_name . " " . $user->last_name : "VERİ YOK");
        $customer->setBillingCity($user->city ?? "VERİ YOK");
        $customer->setBillingCountry($user->country ?? "VERİ YOK");
        $customer->setBillingAddress($user->address ?? "VERİ YOK");
        $customer->setBillingZipCode($user->postcode ?? "VERİ YOK");
        $request->setCustomer($customer);

        $checkoutFormInitialize = SubscriptionCreateCheckoutForm::create($request, $options);
        if ($checkoutFormInitialize !== null) {
            return $checkoutFormInitialize->getCheckoutFormContent();
        } else {
            return null;
        }
    }

    public function callback(Request $request)
    {
        $paymentStatus = $request->input('status');
        $paymentId = $request->input('paymentId');

        // Iyzico'dan dönen sonuçları kontrol etmek için kullanabilirsiniz.
        // $paymentStatus ve $paymentId değerlerini kullanarak işlemlerinizi yapabilirsiniz.

        if ($paymentStatus === 'success') {
            // Ödeme başarılıysa burada gerekli işlemleri yapabilirsiniz.
            // Örneğin, ödeme kaydını güncelleyebilir, sipariş durumunu güncelleyebilir veya kullanıcıya bildirim gönderebilirsiniz.
            // Aşağıda bir örnek gösterilmiştir:

            $order = Order::where('payment_id', $paymentId)->first();

            if ($order) {
                $order->payment_status = 'completed';
                $order->save();

                // Başarılı ödemeyle ilgili diğer işlemleri burada yapabilirsiniz.
            } else {
                // Sipariş bulunamazsa veya hatalı bir durum varsa burada gerekli işlemleri yapabilirsiniz.
            }
        } else {
            // Ödeme başarısız veya hatalıysa burada gerekli işlemleri yapabilirsiniz.
        }

        // İşlemlerinizin günlüklerini tutmak için Log sınıfını kullanabilirsiniz.
        Log::info('Payment callback received: ', $request->all());

        // Ödeme sonucunu Iyzico'ya yanıt olarak göndermek için 200 HTTP koduyla boş bir yanıt döndürün.
        return response()->json();
    }
}
