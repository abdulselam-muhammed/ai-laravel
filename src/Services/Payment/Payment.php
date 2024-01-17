<?php

namespace App\Services\Payment;

use Iyzipay\Model\Locale;
use Iyzipay\Model\Currency;
use Iyzipay\Model\PaymentGroup;
use Iyzipay\Model\CheckoutFormInitialize;
use Iyzipay\Request\CreateCheckoutFormInitializeRequest;
use Iyzipay\Options;

class Payment
{
    public $provider = null;

    

    public static function createCheckoutFormInitialize()
{
    // Iyzipay ayarlarını yapılandır
    $options = new Options();
    $options->setApiKey('sandbox-C27eSDnS8ZF42AH75cT0asWLjjPV3e6f');
    $options->setSecretKey('sandbox-98Ngqlp3jqGJRtxhWZvoWgbbsnD3zG1M');
    $options->setBaseUrl('https://sandbox-api.iyzipay.com');

    // CheckoutFormInitializeRequest oluştur
    $request = new CreateCheckoutFormInitializeRequest();
    $request->setLocale(Locale::TR);
    $request->setConversationId("123456789");
    $request->setPrice("1");
    $request->setPaidPrice("50");
    $request->setCurrency(Currency::TL);
    $request->setPaymentGroup(PaymentGroup::PRODUCT);
    $request->setCallbackUrl("https://www.merchant.com/callback");
    $request->setEnabledInstallments(array(2, 3, 6, 9));

    // CheckoutFormInitialize işlemini oluştur
    $checkoutFormInitialize = CheckoutFormInitialize::create($request, $options);
    $paymentInput = $checkoutFormInitialize->getCheckoutFormContent();

    return $paymentInput;
}
}

