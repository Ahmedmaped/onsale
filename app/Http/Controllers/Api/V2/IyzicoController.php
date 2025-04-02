<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\CombinedOrder;
use App\Models\User;
use App\Models\CustomerPackage;
use App\Models\SellerPackage;
use App\Models\BusinessSetting;

class IyzicoController extends Controller
{
    public function init(Request $request)
    {
        $payment_type = $request->payment_type;
        $combined_order_id = $request->combined_order_id;
        $amount = $request->amount;
        $user_id = $request->user_id;

        if ($payment_type == 'cart_payment') {
            $combined_order = CombinedOrder::find($combined_order_id);
            $amount = $combined_order->grand_total;
            $firstBasketItemName = "Cart Payment";
            $firstBasketItemCategory1 = "Accessories";
        }
        if ($payment_type == 'order_re_payment') {
            $order = Order::find($request->order_id);
            $amount = $order->grand_total;
            $firstBasketItemName = "Order Re Payment";
            $firstBasketItemCategory1 = "Accessories";
        }
        if($payment_type == 'wallet_payment'){
            $firstBasketItemName = "Wallet Payment";
            $firstBasketItemCategory1 = "Wallet";
        }
        if($payment_type == 'customer_package_payment'){
            $firstBasketItemName = "Package Payment";
            $firstBasketItemCategory1 = "Package";
        }
        if($payment_type == 'seller_package_payment'){
            $firstBasketItemName = "Package Payment";
            $firstBasketItemCategory1 = "Package";
        }

        $options = new \Iyzipay\Options();
        $options->setApiKey(env('IYZICO_API_KEY'));
        $options->setSecretKey(env('IYZICO_SECRET_KEY'));

        if (get_setting('iyzico_sandbox') == 1) {
            $options->setBaseUrl("https://sandbox-api.iyzipay.com");
        } else {
            $options->setBaseUrl("https://api.iyzipay.com");
        }

        $iyzicoRequest = new \Iyzipay\Request\CreatePayWithIyzicoInitializeRequest();
        $iyzicoRequest->setLocale(\Iyzipay\Model\Locale::TR);
        $iyzicoRequest->setConversationId(uniqid());
        $iyzicoRequest->setPrice(round($amount));
        $iyzicoRequest->setPaidPrice(round($amount));
        $iyzicoRequest->setCurrency(\Iyzipay\Model\Currency::TL);
        $iyzicoRequest->setBasketId(uniqid());
        $iyzicoRequest->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
        $iyzicoRequest->setCallbackUrl(route('api.iyzico.callback'));
        $iyzicoRequest->setEnabledInstallments(array(2, 3, 6, 9));

        $buyer = new \Iyzipay\Model\Buyer();
        $buyer->setId(uniqid());
        $buyer->setName("John");
        $buyer->setSurname("Doe");
        $buyer->setGsmNumber("+905350000000");
        $buyer->setEmail("email@email.com");
        $buyer->setIdentityNumber("74300864791");
        $buyer->setLastLoginDate("2015-10-05 12:43:35");
        $buyer->setRegistrationDate("2013-04-21 15:12:09");
        $buyer->setRegistrationAddress("Nidakule Göztepe, Merdivenköy Mah. Bora Sok. No:1");
        $buyer->setIp("85.34.78.112");
        $buyer->setCity("Istanbul");
        $buyer->setCountry("Turkey");
        $buyer->setZipCode("34732");
        $iyzicoRequest->setBuyer($buyer);

        $shippingAddress = new \Iyzipay\Model\Address();
        $shippingAddress->setContactName("Jane Doe");
        $shippingAddress->setCity("Istanbul");
        $shippingAddress->setCountry("Turkey");
        $shippingAddress->setAddress("Nidakule Göztepe, Merdivenköy Mah. Bora Sok. No:1");
        $shippingAddress->setZipCode("34742");
        $iyzicoRequest->setShippingAddress($shippingAddress);

        $billingAddress = new \Iyzipay\Model\Address();
        $billingAddress->setContactName("Jane Doe");
        $billingAddress->setCity("Istanbul");
        $billingAddress->setCountry("Turkey");
        $billingAddress->setAddress("Nidakule Göztepe, Merdivenköy Mah. Bora Sok. No:1");
        $billingAddress->setZipCode("34742");
        $iyzicoRequest->setBillingAddress($billingAddress);

        $basketItems = array();
        $firstBasketItem = new \Iyzipay\Model\BasketItem();
        $firstBasketItem->setId(uniqid());
        $firstBasketItem->setName($firstBasketItemName);
        $firstBasketItem->setCategory1($firstBasketItemCategory1);
        $firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::VIRTUAL);
        $firstBasketItem->setPrice(round($amount));
        $basketItems[0] = $firstBasketItem;
        $iyzicoRequest->setBasketItems($basketItems);

        $payWithIyzicoInitialize = \Iyzipay\Model\PayWithIyzicoInitialize::create($iyzicoRequest, $options);

        return response()->json([
            'success' => true,
            'payment_url' => $payWithIyzicoInitialize->getPayWithIyzicoPageUrl()
        ]);
    }

    public function callback(Request $request)
    {
        $options = new \Iyzipay\Options();
        $options->setApiKey(env('IYZICO_API_KEY'));
        $options->setSecretKey(env('IYZICO_SECRET_KEY'));

        if (BusinessSetting::where('type', 'iyzico_sandbox')->first()->value == 1) {
            $options->setBaseUrl("https://sandbox-api.iyzipay.com");
        } else {
            $options->setBaseUrl("https://api.iyzipay.com");
        }

        $iyzicoRequest = new \Iyzipay\Request\RetrievePayWithIyzicoRequest();
        $iyzicoRequest->setLocale(\Iyzipay\Model\Locale::TR);
        $iyzicoRequest->setConversationId(uniqid());
        $iyzicoRequest->setToken($request->token);

        $payWithIyzico = \Iyzipay\Model\PayWithIyzico::retrieve($iyzicoRequest, $options);

        if ($payWithIyzico->getStatus() == 'success') {
            return response()->json([
                'success' => true,
                'message' => 'Payment successful',
                'payment_details' => $payWithIyzico->getRawResult()
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Payment failed',
                'payment_details' => $payWithIyzico->getRawResult()
            ]);
        }
    }

    // the callback function is in the main controller of web | paystackcontroller
    public function payment_success(Request $request)
    {
        try {

            $payment_type = $request->payment_type;

            if ($payment_type == 'cart_payment') {
                checkout_done($request->combined_order_id, $request->payment_details);
            }
            if ($payment_type == 'order_re_payment') {
                order_re_payment_done($request->order_id, 'Iyzico', $request->payment_details);
            }
            elseif ($payment_type == 'wallet_payment') {
                wallet_payment_done($request->user_id, $request->amount, 'Iyzico', $request->payment_details);
            }
            elseif ($payment_type == 'seller_package_payment') {
                seller_purchase_payment_done($request->user_id, $request->package_id, 'Iyzico', $request->payment_details);
            }
            elseif ($payment_type == 'customer_package_payment') {
                customer_purchase_payment_done($request->user_id, $request->package_id, 'Iyzico', $request->payment_details);
            }

            return response()->json(['result' => true, 'message' => translate("Payment is successful")]);


        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => $e->getMessage()]);
        }
    }

}
