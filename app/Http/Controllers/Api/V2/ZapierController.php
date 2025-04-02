<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Seller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Coupon;
use App\Models\Shop;
use App\Models\CombinedOrder;
use App\Models\CustomerPackage;
use App\Models\SellerPackage;
use Illuminate\Support\Facades\Hash;

class ZapierController extends Controller
{
    public function __construct()
    {
        $this->middleware('zapier.auth');
    }

    public function handleWebhook(Request $request)
    {
        // Verify Zapier webhook signature
        if (!$this->verifyZapierSignature($request)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $event = $request->input('event');
        $data = $request->input('data');

        switch ($event) {
            case 'order.created':
                return $this->handleOrderCreated($data);
            case 'order.updated':
                return $this->handleOrderUpdated($data);
            case 'order.cancelled':
                return $this->handleOrderCancelled($data);
            case 'order.delivered':
                return $this->handleOrderDelivered($data);
            case 'product.created':
                return $this->handleProductCreated($data);
            case 'product.updated':
                return $this->handleProductUpdated($data);
            case 'product.deleted':
                return $this->handleProductDeleted($data);
            case 'customer.created':
                return $this->handleCustomerCreated($data);
            case 'customer.updated':
                return $this->handleCustomerUpdated($data);
            case 'seller.created':
                return $this->handleSellerCreated($data);
            case 'seller.updated':
                return $this->handleSellerUpdated($data);
            case 'coupon.created':
                return $this->handleCouponCreated($data);
            case 'coupon.updated':
                return $this->handleCouponUpdated($data);
            case 'payment.processed':
                return $this->handlePaymentProcessed($data);
            case 'payment.failed':
                return $this->handlePaymentFailed($data);
            default:
                return response()->json(['message' => 'Unsupported event type'], 400);
        }
    }

    private function verifyZapierSignature(Request $request)
    {
        $signature = $request->header('X-Zapier-Signature');
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, env('ZAPIER_WEBHOOK_SECRET'));
        
        return hash_equals($expectedSignature, $signature);
    }

    private function handleOrderCreated($data)
    {
        try {
            $order = Order::findOrFail($data['order_id']);
            return response()->json([
                'success' => true,
                'message' => 'Order webhook processed successfully',
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function handleOrderUpdated($data)
    {
        try {
            $order = Order::findOrFail($data['order_id']);
            return response()->json([
                'success' => true,
                'message' => 'Order update webhook processed successfully',
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function handleOrderCancelled($data)
    {
        try {
            $order = Order::findOrFail($data['order_id']);
            return response()->json([
                'success' => true,
                'message' => 'Order cancellation webhook processed successfully',
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function handleOrderDelivered($data)
    {
        try {
            $order = Order::findOrFail($data['order_id']);
            return response()->json([
                'success' => true,
                'message' => 'Order delivery webhook processed successfully',
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function handleProductCreated($data)
    {
        try {
            $product = Product::findOrFail($data['product_id']);
            return response()->json([
                'success' => true,
                'message' => 'Product webhook processed successfully',
                'product' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function handleProductUpdated($data)
    {
        try {
            $product = Product::findOrFail($data['product_id']);
            return response()->json([
                'success' => true,
                'message' => 'Product update webhook processed successfully',
                'product' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function handleProductDeleted($data)
    {
        try {
            $product = Product::findOrFail($data['product_id']);
            return response()->json([
                'success' => true,
                'message' => 'Product deletion webhook processed successfully',
                'product' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function handleCustomerCreated($data)
    {
        try {
            $customer = User::findOrFail($data['customer_id']);
            return response()->json([
                'success' => true,
                'message' => 'Customer webhook processed successfully',
                'customer' => $customer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function handleCustomerUpdated($data)
    {
        try {
            $customer = User::findOrFail($data['customer_id']);
            return response()->json([
                'success' => true,
                'message' => 'Customer update webhook processed successfully',
                'customer' => $customer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function handleSellerCreated($data)
    {
        try {
            $seller = Seller::findOrFail($data['seller_id']);
            return response()->json([
                'success' => true,
                'message' => 'Seller webhook processed successfully',
                'seller' => $seller
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function handleSellerUpdated($data)
    {
        try {
            $seller = Seller::findOrFail($data['seller_id']);
            return response()->json([
                'success' => true,
                'message' => 'Seller update webhook processed successfully',
                'seller' => $seller
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function handleCouponCreated($data)
    {
        try {
            $coupon = Coupon::findOrFail($data['coupon_id']);
            return response()->json([
                'success' => true,
                'message' => 'Coupon webhook processed successfully',
                'coupon' => $coupon
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function handleCouponUpdated($data)
    {
        try {
            $coupon = Coupon::findOrFail($data['coupon_id']);
            return response()->json([
                'success' => true,
                'message' => 'Coupon update webhook processed successfully',
                'coupon' => $coupon
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function handlePaymentProcessed($data)
    {
        try {
            $payment_type = $data['payment_type'];
            $payment_details = $data['payment_details'];

            switch ($payment_type) {
                case 'cart_payment':
                    $combined_order = CombinedOrder::findOrFail($data['combined_order_id']);
                    checkout_done($data['combined_order_id'], $payment_details);
                    break;
                case 'order_re_payment':
                    $order = Order::findOrFail($data['order_id']);
                    order_re_payment_done($data['order_id'], 'Iyzico', $payment_details);
                    break;
                case 'wallet_payment':
                    wallet_payment_done($data['user_id'], $data['amount'], 'Iyzico', $payment_details);
                    break;
                case 'seller_package_payment':
                    seller_purchase_payment_done($data['user_id'], $data['package_id'], 'Iyzico', $payment_details);
                    break;
                case 'customer_package_payment':
                    customer_purchase_payment_done($data['user_id'], $data['package_id'], 'Iyzico', $payment_details);
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function handlePaymentFailed($data)
    {
        try {
            // Handle failed payment logic here
            return response()->json([
                'success' => true,
                'message' => 'Payment failure handled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 