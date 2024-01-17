<?php

namespace App\Services;

use iyzico\Options;
use iyzico\ThreedsInitialize;
// Diğer iyzico sınıflarını buraya ekleyin

class IyzicoService
{
    public static function initializePayment($price, $currency, $basketId, $conversationId)
    {
        $options = new Options();
        $options->setApiKey(env('IYZICO_API_KEY'));
        $options->setSecretKey(env('IYZICO_SECRET_KEY'));
        $options->setBaseUrl('https://api.iyzipay.com');

        $threedsInitialize = new ThreedsInitialize();
        $threedsInitialize->setLocale(ThreedsInitialize::LOCALE_TR);
        $threedsInitialize->setPrice($price);
        $threedsInitialize->setPaidPrice($price);
        $threedsInitialize->setCurrency($currency);
        $threedsInitialize->setBasketId($basketId);
        $threedsInitialize->setPaymentGroup(ThreedsInitialize::PAYMENT_GROUP_PRODUCT);
        // Diğer gerekli parametreleri buraya ekleyin

        $result = $threedsInitialize->retrieveForm($options);
        $formContent = $result->getRawResult();

        return $formContent;
    }
}