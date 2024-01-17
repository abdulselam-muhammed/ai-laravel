<?php

namespace App\Services\Payment;

use Throwable;
use Iyzipay\Options;
use App\Models\Order;
use App\Models\Package;
use Iyzipay\Model\Buyer;
use Iyzipay\Model\Locale;
use Iyzipay\Model\Address;
use Iyzipay\Model\BasketItem;
use Iyzipay\Model\PaymentGroup;
use Iyzipay\Model\PayWithIyzico;
use Iyzipay\Model\BasketItemType;
use Illuminate\Support\Facades\Log;
use Iyzipay\Model\PayWithIyzicoInitialize;
use App\Services\Payment\BasePaymentService;
use Illuminate\Support\Facades\Auth;
use Iyzipay\Request\RetrievePayWithIyzicoRequest;
use Iyzipay\Request\CreatePayWithIyzicoInitializeRequest;

class IyzicoService extends BasePaymentService
{
    public $options;
    public $object;

    public function __construct($method, $object)
    {
        parent::__construct($method, $object);

        $url = $this->gateway->mode == GATEWAY_MODE_LIVE ?
            'https://sandbox-api.iyzipay.com' :
            'https://api.iyzipay.com';

        $this->options = new Options();
        $this->options->setApiKey($this->gateway->key);
        $this->options->setSecretKey($this->gateway->secret);
        $this->options->setBaseUrl($url);
        $this->object = $object;
    }

    public function makePayment($amount)
    {
        $data['success'] = false;
        $data['redirect_url'] = '';
        $data['payment_id'] = '';
        $data['message'] = SOMETHING_WENT_WRONG;

        $order = Order::find($this->object['id']);
        $package = Package::find($order->package_id);

        try {
            $this->setAmount($amount);

            $request = new CreatePayWithIyzicoInitializeRequest();
            $request->setLocale(Locale::TR);
            $request->setConversationId(str_pad($order->id, 9, 0, STR_PAD_LEFT));
            $request->setPrice($this->amount);
            $request->setPaidPrice($this->amount);
            $request->setCurrency($this->currency);
            $request->setBasketId(str()->random(10));
            $request->setPaymentGroup(PaymentGroup::LISTING);
            $request->setCallbackUrl($this->callbackUrl);

            $user = auth()->user();
            $buyer = new Buyer();
            $buyer->setId($user->id);
            $buyer->setName($user->first_name);
            $buyer->setSurname($user->last_name);
            $user->contact_number && $buyer->setGsmNumber($user->contact_number);
            $buyer->setEmail($user->email);
            $buyer->setIp(request()->ip());

            //Need to update based on the user
            $buyer->setIdentityNumber($user->id . rand(10000, 99999));
            $buyer->setRegistrationAddress("Istanbul");
            $buyer->setCity("Istanbul");
            $buyer->setCountry("Turkey");
            $buyer->setZipCode("34732");

            $request->setBuyer($buyer);

            $shippingAddress = new Address();
            $shippingAddress->setContactName($user->first_name . ' ' . $user->last_name);

            //Need to update based on the user
            $shippingAddress->setCity("Istanbul");
            $shippingAddress->setCountry("Turkey");
            $shippingAddress->setAddress("Istanbul");
            $shippingAddress->setZipCode("34742");

            $request->setShippingAddress($shippingAddress);
            $request->setBillingAddress($shippingAddress);

            $firstBasketItem = new BasketItem();
            $firstBasketItem->setId($order->id);
            $firstBasketItem->setName($package->name);
            $firstBasketItem->setCategory1("Subscription Package");
            // $firstBasketItem->setCategory2("Accessories");
            $firstBasketItem->setItemType(BasketItemType::VIRTUAL);
            $firstBasketItem->setPrice($this->amount);
            $request->setBasketItems([$firstBasketItem]);

            $payment = PayWithIyzicoInitialize::create($request, $this->options);

            if ($payment->getToken()) {
                $data['success'] = true;
                $data['redirect_url'] = $payment->getPayWithIyzicoPageUrl();
                $data['payment_id'] = $payment->getToken();

                Log::info(json_encode([
                    'payment_id' => $payment->getToken(),
                    'status' => 'processing',
                    'gateway' => IYZICO
                ]));
            } else {
                Log::info(json_encode([
                    'message' => $payment->getErrorMessage(),
                    'status' => 'exception',
                    'gateway' => IYZICO
                ]));
            }

            return $data;
        } catch (Throwable $th) {
            if(config('app.debug')) {
                throw $th;
            }
            
            Log::info(json_encode([
                'message' => $th->getMessage(),
                'status' => 'exception',
                'gateway' => IYZICO
            ]));

            $data['message'] = $th->getMessage();
            return $data;
        }
    }

    public function paymentConfirmation($payment_id)
    {
        $data['success'] = false;
        $data['data'] = null;

        /**
         * As payment gateway cleaning all the sessions for security,
         * so we need to login the user again
         */
        $order = Order::firstWhere('payment_id', $payment_id);
        Auth::loginUsingId($order->user_id);

        $request = new RetrievePayWithIyzicoRequest();
        $request->setLocale(Locale::TR);
        $request->setConversationId(rand(100000, 9999999));
        $request->setToken($payment_id);

        $payment = PayWithIyzico::retrieve($request, $this->options);
        if ($payment->getPaymentStatus() == "SUCCESS") {
            Log::info(json_encode([
                'payment_id' => $payment_id,
                'status' => $payment->getPaymentStatus(),
                'gateway' => IYZICO
            ]));
            $data['success'] = true;
            $data['data']['amount'] = $payment->getPrice();
            $data['data']['currency'] = $payment->getCurrency();
            $data['data']['payment_status'] = strtolower($payment->getPaymentStatus());
            $data['data']['payment_method'] = IYZICO;
        } else {
            Log::info(json_encode([
                'payment_id' => $payment_id,
                'status' => $payment->getPaymentStatus(),
                'gateway' => IYZICO
            ]));
        }
        return $data;
    }
}
