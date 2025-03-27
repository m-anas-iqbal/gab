<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use stripe\Stripe;
use stripe\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\OrderDetail;
use App\Models\SubscribedPlan;
use App\Models\Order;
use App\Models\Organization;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Mail\PurchasedMail;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class SubscriptionPlanController extends BaseController
{
private $stripe;
   public function __construct() {
    ini_set('max_execution_time', 60000);
   $this->stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
   }
   public function createPayment(Request $request)
   {
       return response()->json([
           'clientSecret' => config('services.stripe.secret'),
           'clientKey' => config('services.stripe.key'),
       ]);
   }
   public function createPaymentIntent(Request $request)
   {
       try {
           // Step 1: Create a customer
           $customer = $this->stripe->customers->create([
               'email' => $request->email,  // User's email
               'name' => $request->name,    // User's name
           ]);

           // Step 2: Attach payment method to the customer
           $this->stripe->paymentMethods->attach(
               $request->payment_method,  // Payment method ID from Stripe
               ['customer' => $customer->id]
           );

           // Step 3: Set the default payment method for the customer
           $this->stripe->customers->update($customer->id, [
               'invoice_settings' => [
                   'default_payment_method' => $request->payment_method,
               ],
           ]);

           // Step 4: Create items for the subscription (main plan + addons if any)
           $items = [
               ['price' => $request->plan_id], // Main plan ID
           ];

           // Add any addons to the subscription items
           if (!empty($request->addon) && is_array($request->addon)) {
               foreach ($request->addon as $addonPlanId) {
                   $items[] = ['price' => $addonPlanId];  // Add each addon plan ID
               }
           }

           // Step 5: Create the subscription for the customer
           $subscription = $this->stripe->subscriptions->create([
               'customer' => $customer->id,    // Customer ID
               'items' => $items,              // Subscription items (plan + addons)
               'expand' => ['latest_invoice.payment_intent'],  // Get payment intent
           ]);
        //    $plan = SubscriptionPlan::find('plan_product_id',$request->plan_id);
        // Create an order record
        $order = Order::create([
            'user_id' => $request->user_id,
            'organization_id' => $request->organization_id,
            'invoice_no' => $subscription->latest_invoice->number ?? null,
            'total_amount' => $subscription->latest_invoice->amount_due / 100, // Convert cents to dollars if needed
            'payment_type' => 'stripe',  // or other payment types if applicable
        ]);
        // Create order details for each subscription item
        foreach ($items as $item) {
            OrderDetail::create([
                'order_id' => $order->id,
                'subscription_plan_id' => $item['price'], // Subscription plan ID from Stripe
                'quantity' => 1, // Default to 1 unless you track quantity
                'amount' => $subscription->latest_invoice->amount_due / 100, // Total amount for the item
                'st_subscription_id' => $subscription->id,  // Stripe subscription ID
            ]);
        }

        $expairy= Carbon::parse($subscription->current_period_end)->format('Y-m-d');
        // Save the subscription plan to the subscribed_plans table
        SubscribedPlan::create([
            'user_id' => $request->user_id,
            'organization_id' => $request->organization_id,
            'subscription_plan_id' => $request->plan_id,  // Main plan ID
            'extra' => json_encode($request->addon), // Store addon plans as JSON (optional)
            'expaiy_at' => $expairy, // Expiry date from Stripe
        ]);
        $organization = Organization::findOrFail($request->organization_id);
        $organization->update([
            'cid_stripe' => $customer->id,
            'expires_at' => $expairy,
            'plan_id' => $request->plan_id
        ]);
        $admin = $organization->admin;
        $subscribedPlans = $organization->subscribedPlans;
        $data = SubscriptionPlan::where("plan_product_id",$organization->plan_id)->first() ?? "";
        $amount = $data->amount;
        $currency = $data->currency;
        $plan_name = $data->name;
        $folder = "uploaded/invoices/";
        // Step 7: Generate PDF Invoice
        $uploadDirectory = public_path($folder);
        if (!file_exists($uploadDirectory)) {
            mkdir($uploadDirectory, 0755, true);
        }
        $pdf = Pdf::loadView('invoice', ['user' => $admin,'organization'=> $organization,'invoice'=> $subscription->latest_invoice->number,'expairy'=> $expairy,'date'=> $order->created_at,'plan'=> $plan_name,'currency'=> $currency,'amount'=> $amount]);
        $pdfPath = $folder."{$subscription->latest_invoice->number}.pdf";
        $pdf->save($pdfPath);
        // Step 8: Save invoice URL in the order
        $order->update(['invoice_url' => $pdfPath]);
        Mail::to($admin->email)->send(new PurchasedMail($admin,$organization,$subscription->latest_invoice->number,$expairy,$order->created_at,$plan_name,$currency,$amount));

        return response()->json([
            'success' => true,
            'organization' => $organization,
            'user' => $admin,
            'subscribed_plans' => $subscribedPlans,
        ], 200);
        } catch (\Exception $e) {
            // Handle and return any errors from Stripe
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
   }
   public function plan() {

        $plans = $this->stripe->plans->all();
        $response = [];
        foreach ($plans->data as $key => $planData) {
            $productDetails = $this->stripe->products->retrieve($planData->product, []);
            $planData['product_details'] = $productDetails;
            $plan = SubscriptionPlan::updateOrCreate(
                [
                    'plan_product_id' => $planData->id,  // Directly using Stripe Plan ID
                    'product_product_id' => $productDetails->id,  // Directly using Stripe Product ID
                ],
                [
                    'name' => $productDetails->name ?? null,  // Directly using Product name
                    'plan_type' => $productDetails->unit_label ?? 'plan',  // Plan type based on unit_label or default to 'plan'
                    'description' => $productDetails->description ?? null,  // Product description
                    'details' => json_encode($productDetails),  // Store product details as JSON
                    'status' => $planData->active ? 'active' : 'inactive',  // Active status
                    'livemode' => $planData->livemode ? 'live' : 'test',  // Livemode status
                    'amount' => $planData->amount/100,  // Directly using Plan amount
                    'currency' => $planData->currency,  // Directly using Currency (e.g., USD)
                    'interval' => $planData->interval,  // Billing interval (e.g., 'month')
                ]
            );
            $response[] = $plan;
            // Add the result of this operation to the response array
            // $response[] = [
            //     'plan_product_id' => $planData->id,
            //     'plan' => $plan,
            //     'product_name' => $productDetails->name ?? 'N/A',
            //     'status' => $planData->active ? 'updated' : 'created',
            // ];
        }
        return response()->json([
            // 'message' => 'All Stripe plans have been processed successfully.',
            'plans' => $response
        ]);
    }

    public function subscribe() {
        $subscribes = $this->stripe->subscriptions->all();

            $response[] = [$subscribes];
            // Add the result of this operation to the response array
            // $response[] = [
            //     'plan_product_id' => $planData->id,
            //     'plan' => $plan,
            //     'product_name' => $productDetails->name ?? 'N/A',
            //     'status' => $planData->active ? 'updated' : 'created',
            // ];
        // }

        // Return a response with the details of each processed plan
        return response()->json([
            // 'message' => 'All Stripe plans have been processed successfully.',
            'plans' => $response
        ]);
    }

    public function recurring_handleWebhook(Request $request) {
        $payload = $request->getContent();
        $event = null;
        $sigHeader = $request->header('HTTP_STRIPE_SIGNATURE');
        $secret = config('services.stripe.webhook_secret');
        // try {
        //     $event = \Stripe\Event::constructFrom(
        //       json_decode($payload, true)
        //     );
        //   } catch(\UnexpectedValueException $e) {
        //     // Invalid payload
        //     Log::info('Payment not succeeded for subscription' );
        //   }
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $secret
            );

            // Handle specific event types
            if ($event->type === 'customer.subscription.updated') {
                $subscription = $event->data->object;
                // Update the subscribed_plans table with the new subscription plan
                $organization = Organization::where('cid_stripe',$subscription->customer)->first();
                $order = Order::create([
                    'user_id' => $organization->admin_id,
                    'organization_id' => $organization->id,
                    'invoice_no' => $subscription->latest_invoice ?? null,
                    'total_amount' => $subscription->latest_invoice->amount_due / 100, // Convert cents to dollars if needed
                    'payment_type' => 'stripe/recurring',  // or other payment types if applicable
                ]);
                // Create order details for each subscription item
                foreach ($items as $item) {
                    OrderDetail::create([
                        'order_id' => $order->id,
                        'subscription_plan_id' => $item['price'], // Subscription plan ID from Stripe
                        'quantity' => 1, // Default to 1 unless you track quantity
                        'amount' => $subscription->latest_invoice->amount_due / 100, // Total amount for the item
                        'st_subscription_id' => $subscription->id,  // Stripe subscription ID
                    ]);
                }
                // Save the subscription plan to the subscribed_plans table
                SubscribedPlan::update(['expaiy_at' => Carbon::parse($subscription->current_period_end)->format('Y-m-d'), // Expiry date from Stripe
]);
        $organization->update([
            'expires_at' => Carbon::parse($subscription->current_period_end)->toDateString(),
        ]);
            }
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            Log::info('Payment not  succeeded for subscription' );
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            Log::info('Payment not succeeded for subscription');
        }

        return response()->json(['success' => true]);
    }
}
