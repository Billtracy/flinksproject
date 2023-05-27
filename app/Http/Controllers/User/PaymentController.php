<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\Models\Setting;
use App\Models\StripePayment;
use App\Models\PaypalPayment;
use App\Mail\OrderSuccessfully;
use App\Helpers\MailHelper;
use App\Models\EmailTemplate;
use App\Models\RazorpayPayment;
use App\Models\Flutterwave;
use App\Models\PaystackAndMollie;
use App\Models\InstamojoPayment;
use App\Models\PaymongoPayment;
use App\Models\BankPayment;
use App\Models\AppointmentSchedule;


use Mail;
Use Stripe;
use Cart;
use Session;
use Str;
use Razorpay\Api\Api;
use Exception;
use Redirect;

use Mollie\Laravel\Facades\Mollie;


use App\Models\BreadcrumbImage;
use App\Models\Service;
use App\Models\AdditionalService;
use App\Models\Order;
use App\Models\Schedule;
use Auth;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web');
    }

    public function ready_to_booking($slug){
        $breadcrumb = BreadcrumbImage::where(['id' => 8])->first();
        $service = Service::with('category','provider')->where(['slug' => $slug, 'approve_by_admin' => 1, 'status' => 1, 'is_banned' => 0])->first();

        if(!$service){
            abort(404);
        }


        $what_you_will_get = array();
        if($service->what_you_will_provide){
            $provides = json_decode($service->what_you_will_provide);
            foreach($provides as $provide){
                $what_you_will_get [] = $provide;
            }
        }

        $benifits = array();
        if($service->benifit){
            $exist_benifits = json_decode($service->benifit);
            foreach($exist_benifits as $exist_benifit){
                $benifits [] = $exist_benifit;
            }
        }

        $package_features = array();
        if($service->package_features){
            $features = json_decode($service->package_features);
            foreach($features as $feature){
                $package_features [] = $feature;
            }
        }

        $additional_services = AdditionalService::where('service_id', $service->id)->get();

        $setting = Setting::first();
        $currency_icon = (object) array('icon' => $setting->currency_icon);

        $selected_theme = Session::get('selected_theme');
        if ($selected_theme == 'theme_one'){
            $active_theme = 'layout';
        }elseif($selected_theme == 'theme_two'){
            $active_theme = 'layout2';
        }elseif($selected_theme == 'theme_three'){
            $active_theme = 'layout3';
        }else{
            $active_theme = 'layout';
        }

        return view('ready_to_booking')->with([
            'active_theme' => $active_theme,
            'breadcrumb' => $breadcrumb,
            'service' => $service,
            'package_features' => $package_features,
            'benifits' => $benifits,
            'additional_services' => $additional_services,
            'currency_icon' => $currency_icon,
            'what_you_will_get' => $what_you_will_get,
        ]);
    }

    public function get_available_schedule(Request $request){
        $day = date('l',strtotime($request->date));
        $appointment_schedules = AppointmentSchedule::where(['user_id' => $request->provider_id, 'day' => $day])->get();

        $available_schedule_arr = array();
        foreach($appointment_schedules as $appointment_schedule){
            $exist = Order::where(['provider_id' => $request->provider_id, 'appointment_schedule_id' => $appointment_schedule->id, 'booking_date' => $request->date])->count();
            if($exist == 0){
                $available_schedule_arr[] = $appointment_schedule->id;
            }
        }

        $available_schedules = AppointmentSchedule::whereIn('id', $available_schedule_arr)->orderBy('start_time','asc')->get();
        $is_available = $available_schedules->count() > 0 ? true : false;
        $html = "<option value=''>".trans('user_validation.Select')."</option>";
        foreach($available_schedules as $index=> $schedule){
            $html.='<option value="'.$schedule->id.'">'.strtoupper(date('h:i A', strtotime($schedule->start_time))).' - '.strtoupper(date('h:i A', strtotime($schedule->end_time))).'</option>';
        }

        return response()->json(['is_available' => $is_available, 'available_schedules' => $html]);
    }

    public function booking_information(Request $request, $slug){
        $extras = (object) array(
            'ids' => $request->ids,
            'prices' => $request->prices,
            'quantities' => $request->quantities,
            'names' => $request->names,
            'extra_total' => $request->extra_total,
            'sub_total' => $request->sub_total,
            'total' => $request->total,
            'date' => $request->date,
        );

        $breadcrumb = BreadcrumbImage::where(['id' => 8])->first();
        $service = Service::with('category','provider')->where(['slug' => $slug, 'approve_by_admin' => 1, 'status' => 1, 'is_banned' => 0])->first();

        $service = Service::with('category','provider')->where(['slug' => $slug, 'approve_by_admin' => 1, 'status' => 1, 'is_banned' => 0])->first();

        if(!$service){
            abort(404);
        }

        $what_you_will_get = array();
        if($service->what_you_will_provide){
            $provides = json_decode($service->what_you_will_provide);
            foreach($provides as $provide){
                $what_you_will_get [] = $provide;
            }
        }

        $benifits = array();
        if($service->benifit){
            $exist_benifits = json_decode($service->benifit);
            foreach($exist_benifits as $exist_benifit){
                $benifits [] = $exist_benifit;
            }
        }

        $package_features = array();
        if($service->package_features){
            $features = json_decode($service->package_features);
            foreach($features as $feature){
                $package_features [] = $feature;
            }
        }

        $setting = Setting::first();
        $currency_icon = (object) array('icon' => $setting->currency_icon);

        $selected_theme = Session::get('selected_theme');
        if ($selected_theme == 'theme_one'){
            $active_theme = 'layout';
        }elseif($selected_theme == 'theme_two'){
            $active_theme = 'layout2';
        }elseif($selected_theme == 'theme_three'){
            $active_theme = 'layout3';
        }else{
            $active_theme = 'layout';
        }

        return view('booking_information')->with([
            'active_theme' => $active_theme,
            'breadcrumb' => $breadcrumb,
            'service' => $service,
            'package_features' => $package_features,
            'benifits' => $benifits,
            'what_you_will_get' => $what_you_will_get,
            'extra_services' => $extras,
            'extras' => json_encode($extras),
            'currency_icon' => $currency_icon,
        ]);
    }


    public function payment(Request $request ,$slug){
        $user = Auth::guard('web')->user();
        if($user->is_provider == 1){
            $notification = trans('user_validation.You are log-in as a provider, you can not book any service');
            $notification = array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->back()->with($notification);
        }



        if(Session::has('return_from_mollie')){
            $breadcrumb = BreadcrumbImage::where(['id' => 8])->first();
            $service = Service::with('category','provider')->where(['slug' => $slug, 'approve_by_admin' => 1, 'status' => 1, 'is_banned' => 0])->first();

            if(!$service){
                abort(404);
            }

            $what_you_will_get = array();
            if($service->what_you_will_provide){
                $provides = json_decode($service->what_you_will_provide);
                foreach($provides as $provide){
                    $what_you_will_get [] = $provide;
                }
            }

            $benifits = array();
            if($service->benifit){
                $exist_benifits = json_decode($service->benifit);
                foreach($exist_benifits as $exist_benifit){
                    $benifits [] = $exist_benifit;
                }
            }

            $package_features = array();
            if($service->package_features){
                $features = json_decode($service->package_features);
                foreach($features as $feature){
                    $package_features [] = $feature;
                }
            }

            $setting = Setting::first();
            $currency_icon = (object) array('icon' => $setting->currency_icon);

            $order_info = Session::get('order_info');
            $customer = $order_info->customer;

            $extras = $order_info->extras;
            $extra_services = json_decode($extras);

            $extra_price = 0;
            if($extra_services->ids){
                foreach($extra_services->ids as $index => $extra_service){
                    $addition = AdditionalService::find($extra_services->ids[$index]);
                    $extra_price += ($extra_services->quantities[$index] * $addition->price);
                }
            }


            $total_price = $service->price + $extra_price;
            $total_price = round($total_price,2);

            $order_info = (object) array(
                'customer' => $customer,
                'extras' => $extras,
                'what_you_will_get' => $what_you_will_get,
                'benifits' => $benifits,
                'package_features' => $package_features,
                'extra_price' => $extra_price,
                'total_price' => $total_price,
                'package_price' => $service->price,
                'date' => $extra_services->date,
            );

            $user = Auth::guard('web')->user();
            $bankPayment = BankPayment::select('id','status','account_info','image')->first();
            $stripe = StripePayment::first();
            $paypal = PaypalPayment::first();
            $razorpay = RazorpayPayment::first();
            $flutterwave = Flutterwave::first();
            $mollie = PaystackAndMollie::first();
            $paystack = $mollie;
            $instamojoPayment = InstamojoPayment::first();

            $selected_theme = Session::get('selected_theme');
            if ($selected_theme == 'theme_one'){
                $active_theme = 'layout';
            }elseif($selected_theme == 'theme_two'){
                $active_theme = 'layout2';
            }elseif($selected_theme == 'theme_three'){
                $active_theme = 'layout3';
            }else{
                $active_theme = 'layout';
            }

            return view('payment')->with([
                'active_theme' => $active_theme,
                'breadcrumb' => $breadcrumb,
                'service' => $service,
                'customer' => $customer,
                'package_features' => $package_features,
                'benifits' => $benifits,
                'what_you_will_get' => $what_you_will_get,
                'extra_services' => $extra_services,
                'currency_icon' => $currency_icon,
                'bankPayment' => $bankPayment,
                'stripe' => $stripe,
                'paypal' => $paypal,
                'razorpay' => $razorpay,
                'total_price' => $total_price,
                'flutterwave' => $flutterwave,
                'user' => $user,
                'mollie' => $mollie,
                'instamojoPayment' => $instamojoPayment,
                'paystack' => $paystack,
            ]);
        }else{
            $rules = [
                'name'=>'required',
                'phone'=>'required',
                'address'=>'required',
                'agree_with'=>'required',
            ];
            $customMessages = [
                'name.required' => trans('user_validation.Name is required'),
                'email.required' => trans('user_validation.Email is required'),
                'phone.required' => trans('user_validation.Phone is required'),
                'address.required' => trans('user_validation.Address is required'),
                'agree_with.required' => trans('user_validation.Agree with terms and conditions is required'),
            ];
            $this->validate($request, $rules,$customMessages);

            $breadcrumb = BreadcrumbImage::where(['id' => 8])->first();
            $service = Service::with('category','provider')->where(['slug' => $slug, 'approve_by_admin' => 1, 'status' => 1, 'is_banned' => 0])->first();

            if(!$service){
                abort(404);
            }

            $what_you_will_get = array();
            if($service->what_you_will_provide){
                $provides = json_decode($service->what_you_will_provide);
                foreach($provides as $provide){
                    $what_you_will_get [] = $provide;
                }
            }

            $benifits = array();
            if($service->benifit){
                $exist_benifits = json_decode($service->benifit);
                foreach($exist_benifits as $exist_benifit){
                    $benifits [] = $exist_benifit;
                }
            }

            $package_features = array();
            if($service->package_features){
                $features = json_decode($service->package_features);
                foreach($features as $feature){
                    $package_features [] = $feature;
                }
            }

            $setting = Setting::first();
            $currency_icon = (object) array('icon' => $setting->currency_icon);

            $customer = (object) array(
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'post_code' => $request->post_code,
                'order_note' => $request->order_note,
            );

            $extras = html_decode($request->extras);
            $extra_services = json_decode($extras);

            $extra_price = 0;
            if($extra_services->ids){
                foreach($extra_services->ids as $index => $extra_service){
                    $addition = AdditionalService::find($extra_services->ids[$index]);
                    $extra_price += ($extra_services->quantities[$index] * $addition->price);
                }
            }

            $total_price = $service->price + $extra_price;
            $total_price = round($total_price,2);

            $order_info = (object) array(
                'customer' => $customer,
                'extras' => $extras,
                'what_you_will_get' => $what_you_will_get,
                'benifits' => $benifits,
                'package_features' => $package_features,
                'extra_price' => $extra_price,
                'total_price' => $total_price,
                'package_price' => $service->price,
                'date' => $extra_services->date,
            );



            Session::put('order_info', $order_info);
            $user = Auth::guard('web')->user();
            $bankPayment = BankPayment::select('id','status','account_info','image')->first();
            $stripe = StripePayment::first();
            $paypal = PaypalPayment::first();
            $razorpay = RazorpayPayment::first();
            $flutterwave = Flutterwave::first();
            $mollie = PaystackAndMollie::first();
            $paystack = $mollie;
            $instamojoPayment = InstamojoPayment::first();

            $selected_theme = Session::get('selected_theme');
            if ($selected_theme == 'theme_one'){
                $active_theme = 'layout';
            }elseif($selected_theme == 'theme_two'){
                $active_theme = 'layout2';
            }elseif($selected_theme == 'theme_three'){
                $active_theme = 'layout3';
            }else{
                $active_theme = 'layout';
            }

            return view('payment')->with([
                'active_theme' => $active_theme,
                'breadcrumb' => $breadcrumb,
                'service' => $service,
                'customer' => $customer,
                'package_features' => $package_features,
                'benifits' => $benifits,
                'what_you_will_get' => $what_you_will_get,
                'extra_services' => $extra_services,
                'currency_icon' => $currency_icon,
                'bankPayment' => $bankPayment,
                'stripe' => $stripe,
                'paypal' => $paypal,
                'razorpay' => $razorpay,
                'total_price' => $total_price,
                'flutterwave' => $flutterwave,
                'user' => $user,
                'mollie' => $mollie,
                'paystack' => $paystack,
                'instamojoPayment' => $instamojoPayment,
            ]);
        }

    }


    public function check_schedule_during_payment($slug){
        $service = Service::where(['slug' => $slug, 'approve_by_admin' => 1, 'status' => 1, 'is_banned' => 0])->first();
        $order_info = Session::get('order_info');

        $exist = $this->checkAvaibalityBeforPayment($service, $order_info->date);

        if($exist > 0){
            $notification = trans('user_validation.This schedule already booked. please choose another schedule');
            return response()->json(['is_available' => false, 'message' => $notification]);
        }else{
            return response()->json(['is_available' => true]);
        }

    }
    public function checkAvaibalityBeforPayment($service, $date){
        // $exist = Order::where(['provider_id' => $service->provider_id, 'appointment_schedule_id' => $schedule_id, 'booking_date' => $date])->count();
        // return $exist;
        return false;
    }


    public function bankPayment(Request $request, $slug){
        $service = Service::where(['slug' => $slug, 'approve_by_admin' => 1, 'status' => 1, 'is_banned' => 0])->first();
        $user = Auth::guard('web')->user();
        $order_info = Session::get('order_info');
        $provider_id = $service->provider_id;
        $client_id = $user->id;

        // $exist = $this->checkAvaibalityBeforPayment($service, $order_info->date);

        // if($exist > 0){
        //     $notification = trans('user_validation.This schedule already booked. please choose another schedule');
        //     $notification = array('messege'=>$notification,'alert-type'=>'error');
        //     return redirect()->route('ready-to-booking', $service->slug)->with($notification);
        // }

        $order = $this->createOrder($user, $service, $order_info, $provider_id, $client_id, 'Bank Payment', 'pending', $request->tnx_info);

        $this->sendMailToClient($user, $order);
        Session::forget('order_info');

        $notification = trans('Your order has been placed. Please wait for approval');
        $notification = array('messege'=>$notification,'alert-type'=>'success');
        return redirect()->route('dashboard')->with($notification);
    }

    public function payWithStripe(Request $request, $slug){

        if(env('APP_MODE') == 'DEMO'){
            $notification = trans('user_validation.This Is Demo Version. You Can Not Change Anything');
            $notification=array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->back()->with($notification);
        }

        $service = Service::where(['slug' => $slug, 'approve_by_admin' => 1, 'status' => 1, 'is_banned' => 0])->first();
        $user = Auth::guard('web')->user();
        $order_info = Session::get('order_info');
        $provider_id = $service->provider_id;
        $client_id = $user->id;

        $exist = $this->checkAvaibalityBeforPayment($service, $order_info->date);

        if($exist > 0){
            $notification = trans('user_validation.This schedule already booked. please choose another schedule');
            $notification = array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->route('ready-to-booking', $service->slug)->with($notification);
        }

        $total_price = $service->price + $order_info->extra_price;

        $stripe = StripePayment::first();
        $payableAmount = round($total_price * $stripe->currency_rate,2);
        Stripe\Stripe::setApiKey($stripe->stripe_secret);

        try {
            $result = Stripe\Charge::create ([
                "amount" => $payableAmount * 100,
                "currency" => $stripe->currency_code,
                "source" => $request->stripeToken,
                "description" => env('APP_NAME')
            ]);
        } catch (\Throwable $th) {
           return back()->with("error","Something Went Wrong");
        }

        $order = $this->createOrder($user, $service, $order_info, $provider_id, $client_id, 'Stripe', 'success', $result->balance_transaction);

        $provider = $service->provider;
        $this->sendMailToClient($user, $order);
        $this->sendMailToProvider($provider, $order);

        Session::forget('order_info');

        $notification = trans('user_validation.Your order has been placed. Thanks for your new order');
        $notification = array('messege'=>$notification,'alert-type'=>'success');
        return redirect()->route('dashboard')->with($notification);

    }

    public function payWithRazorpay(Request $request, $slug){
        if(env('APP_MODE') == 'DEMO'){
            $notification = trans('user_validation.This Is Demo Version. You Can Not Change Anything');
            $notification=array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->back()->with($notification);
        }

        $razorpay = RazorpayPayment::first();
        $input = $request->all();
        $api = new Api($razorpay->key,$razorpay->secret_key);
        $payment = $api->payment->fetch($input['razorpay_payment_id']);
        if(count($input)  && !empty($input['razorpay_payment_id'])) {
            try {
                $response = $api->payment->fetch($input['razorpay_payment_id'])->capture(array('amount'=>$payment['amount']));
                $payId = $response->id;

                $service = Service::where(['slug' => $slug, 'approve_by_admin' => 1, 'status' => 1, 'is_banned' => 0])->first();
                $user = Auth::guard('web')->user();
                $order_info = Session::get('order_info');
                $provider_id = $service->provider_id;
                $client_id = $user->id;

                $exist = $this->checkAvaibalityBeforPayment($service, $order_info->date);

                if($exist > 0){
                    $notification = trans('user_validation.This schedule already booked. please choose another schedule');
                    $notification = array('messege'=>$notification,'alert-type'=>'error');
                    return redirect()->route('ready-to-booking', $service->slug)->with($notification);
                }

                $order = $this->createOrder($user, $service, $order_info, $provider_id, $client_id, 'Razorpay', 'success', $payId);

                $provider = $service->provider;
                $this->sendMailToClient($user, $order);
                $this->sendMailToProvider($provider, $order);

                Session::forget('order_info');

                $notification = trans('user_validation.Your order has been placed. Thanks for your new order');
                $notification = array('messege'=>$notification,'alert-type'=>'success');
                return redirect()->route('dashboard')->with($notification);

            }catch (Exception $e) {
                $notification = trans('user_validation.Payment Faild');
                $notification = array('messege'=>$notification,'alert-type'=>'error');
                return redirect()->back()->with($notification);
            }
        }else{
            $notification = trans('user_validation.Payment Faild');
            $notification = array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->back()->with($notification);
        }
    }

    public function payWithFlutterwave(Request $request, $slug){

        if(env('APP_MODE') == 'DEMO'){
            $notification = trans('user_validation.This Is Demo Version. You Can Not Change Anything');
            $notification=array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->back()->with($notification);
        }

        $flutterwave = Flutterwave::first();
        $curl = curl_init();
        $tnx_id = $request->tnx_id;
        $url = "https://api.flutterwave.com/v3/transactions/$tnx_id/verify";
        $token = $flutterwave->secret_key;
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Authorization: Bearer $token"
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response);
        if($response->status == 'success'){
            $service = Service::where(['slug' => $slug, 'approve_by_admin' => 1, 'status' => 1, 'is_banned' => 0])->first();
            $user = Auth::guard('web')->user();
            $order_info = Session::get('order_info');
            $provider_id = $service->provider_id;
            $client_id = $user->id;

            $order = $this->createOrder($user, $service, $order_info, $provider_id, $client_id, 'Flutterwave', 'success', $tnx_id);

            $provider = $service->provider;
            $this->sendMailToClient($user, $order);
            $this->sendMailToProvider($provider, $order);

            Session::forget('order_info');

            $notification = trans('user_validation.Your order has been placed. Thanks for your new order');
            return response()->json(['status' => 'success' , 'message' => $notification]);
        }else{
            $notification = trans('user_validation.Payment Faild');
            return response()->json(['status' => 'faild' , 'message' => $notification]);
        }
    }

    public function payWithMollie(Request $request, $slug){

        if(env('APP_MODE') == 'DEMO'){
            $notification = trans('user_validation.This Is Demo Version. You Can Not Change Anything');
            $notification=array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->back()->with($notification);
        }

        $service = Service::where(['slug' => $slug, 'approve_by_admin' => 1, 'status' => 1, 'is_banned' => 0])->first();
        $user = Auth::guard('web')->user();
        $order_info = Session::get('order_info');
        $total_price = $service->price + $order_info->extra_price;

        $exist = $this->checkAvaibalityBeforPayment($service, $order_info->date);

        if($exist > 0){
            $notification = trans('user_validation.This schedule already booked. please choose another schedule');
            $notification = array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->route('ready-to-booking', $service->slug)->with($notification);
        }

        $mollie = PaystackAndMollie::first();
        $price = $total_price * $mollie->mollie_currency_rate;
        $price = round($price,2);
        $price = sprintf('%0.2f', $price);

        $mollie_api_key = $mollie->mollie_key;
        $currency = strtoupper($mollie->mollie_currency_code);
        Mollie::api()->setApiKey($mollie_api_key);
        $payment = Mollie::api()->payments()->create([
            'amount' => [
                'currency' => $currency,
                'value' => ''.$price.'',
            ],
            'description' => env('APP_NAME'),
            'redirectUrl' => route('mollie-payment-success'),
        ]);

        $payment = Mollie::api()->payments()->get($payment->id);
        session()->put('payment_id',$payment->id);
        session()->put('service',$service);
        return redirect($payment->getCheckoutUrl(), 303);
    }

    public function molliePaymentSuccess(Request $request){


        $service = Session::get('service');
        Session::put('return_from_mollie','payment_faild');
        $order_info = Session::get('order_info');

        $exist = $this->checkAvaibalityBeforPayment($service, $order_info->date);

        if($exist > 0){
            $notification = trans('user_validation.This schedule already booked. please choose another schedule');
            $notification = array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->route('ready-to-booking', $service->slug)->with($notification);
        }

        $mollie = PaystackAndMollie::first();
        $mollie_api_key = $mollie->mollie_key;
        Mollie::api()->setApiKey($mollie_api_key);
        $payment = Mollie::api()->payments->get(session()->get('payment_id'));
        if ($payment->isPaid()){
            $service = Service::where(['slug' => $service->slug, 'approve_by_admin' => 1, 'status' => 1, 'is_banned' => 0])->first();
            $user = Auth::guard('web')->user();
            $order_info = Session::get('order_info');
            $provider_id = $service->provider_id;
            $client_id = $user->id;

            $order = $this->createOrder($user, $service, $order_info, $provider_id, $client_id, 'Mollie', 'success', session()->get('payment_id'));

            $provider = $service->provider;
            $this->sendMailToClient($user, $order);
            $this->sendMailToProvider($provider, $order);

            Session::forget('order_info');
            Session::forget('return_from_mollie');

            $notification = trans('user_validation.Your order has been placed. Thanks for your new order');
            $notification = array('messege'=>$notification,'alert-type'=>'success');
            return redirect()->route('dashboard')->with($notification);
        }else{
            $notification = trans('user_validation.Payment Faild');
            $notification = array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->route('payment', $service->slug)->with($notification);
        }
    }

    public function payWithPayStack(Request $request, $slug){

        if(env('APP_MODE') == 'DEMO'){
            $notification = trans('user_validation.This Is Demo Version. You Can Not Change Anything');
            $notification=array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->back()->with($notification);
        }

        $paystack = PaystackAndMollie::first();

        $reference = $request->reference;
        $transaction = $request->tnx_id;
        $secret_key = $paystack->paystack_secret_key;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/$reference",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_SSL_VERIFYHOST =>0,
            CURLOPT_SSL_VERIFYPEER =>0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $secret_key",
                "Cache-Control: no-cache",
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        $final_data = json_decode($response);
        if($final_data->status == true) {

            $service = Service::where(['slug' => $slug, 'approve_by_admin' => 1, 'status' => 1, 'is_banned' => 0])->first();

            $user = Auth::guard('web')->user();
            $order_info = Session::get('order_info');
            $provider_id = $service->provider_id;
            $client_id = $user->id;

            $order = $this->createOrder($user, $service, $order_info, $provider_id, $client_id, 'Paystack', 'success', $transaction);

            $provider = $service->provider;
            $this->sendMailToClient($user, $order);
            $this->sendMailToProvider($provider, $order);

            Session::forget('order_info');

            $notification = trans('user_validation.Your order has been placed. Thanks for your new order');
            return response()->json(['status' => 'success' , 'message' => $notification]);
        }else{
            $notification = trans('user_validation.Payment Faild');
            return response()->json(['status' => 'faild' , 'message' => $notification]);
        }
    }

    public function payWithInstamojo(Request $request, $slug){

        if(env('APP_MODE') == 'DEMO'){
            $notification = trans('user_validation.This Is Demo Version. You Can Not Change Anything');
            $notification=array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->back()->with($notification);
        }

        $service = Service::where(['slug' => $slug, 'approve_by_admin' => 1, 'status' => 1, 'is_banned' => 0])->first();
        $user = Auth::guard('web')->user();
        $order_info = Session::get('order_info');
        $provider_id = $service->provider_id;
        $client_id = $user->id;

        $exist = $this->checkAvaibalityBeforPayment($service, $order_info->date);

        if($exist > 0){
            $notification = trans('user_validation.This schedule already booked. please choose another schedule');
            $notification = array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->route('ready-to-booking', $service->slug)->with($notification);
        }

        $total_price = $service->price + $order_info->extra_price;

        $instamojoPayment = InstamojoPayment::first();
        $price = $total_price * $instamojoPayment->currency_rate;
        $price = round($price,2);

        $price = "20.00";
        $environment = $instamojoPayment->account_mode;
        $api_key = $instamojoPayment->api_key;
        $auth_token = $instamojoPayment->auth_token;

        if($environment == 'Sandbox') {
            $url = 'https://test.instamojo.com/api/1.1/';
        } else {
            $url = 'https://www.instamojo.com/api/1.1/';
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url.'payment-requests/');
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array("X-Api-Key:$api_key",
                "X-Auth-Token:$auth_token"));
        $payload = Array(
            'purpose' => env("APP_NAME"),
            'amount' => $price,
            'phone' => '918160651749',
            'buyer_name' => Auth::user()->name,
            'redirect_url' => route('response-instamojo'),
            'send_email' => true,
            'webhook' => 'http://www.example.com/webhook/',
            'send_sms' => true,
            'email' => Auth::user()->email,
            'allow_repeated_payments' => false
        );
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response);
        Session::put('service', $service);
        return redirect($response->payment_request->longurl);
    }

    public function instamojoResponse(Request $request){

        Session::put('return_from_mollie','payment_faild');
        $service = Session::get('service');

        $order_info = Session::get('order_info');

        $exist = $this->checkAvaibalityBeforPayment($service, $order_info->date);

        if($exist > 0){
            $notification = trans('user_validation.This schedule already booked. please choose another schedule');
            $notification = array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->route('ready-to-booking', $service->slug)->with($notification);
        }

        $input = $request->all();
        $instamojoPayment = InstamojoPayment::first();
        $environment = $instamojoPayment->account_mode;
        $api_key = $instamojoPayment->api_key;
        $auth_token = $instamojoPayment->auth_token;

        if($environment == 'Sandbox') {
            $url = 'https://test.instamojo.com/api/1.1/';
        } else {
            $url = 'https://www.instamojo.com/api/1.1/';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url.'payments/'.$request->get('payment_id'));
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array("X-Api-Key:$api_key",
                "X-Auth-Token:$auth_token"));
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            $notification = trans('user_validation.Payment Faild');
            $notification = array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->route('payment', $service->slug)->with($notification);
        } else {
            $data = json_decode($response);
        }

        if($data->success == true) {
            if($data->payment->status == 'Credit') {

                $service = Service::where(['slug' => $service->slug, 'approve_by_admin' => 1, 'status' => 1, 'is_banned' => 0])->first();
                $user = Auth::guard('web')->user();
                $order_info = Session::get('order_info');
                $provider_id = $service->provider_id;
                $client_id = $user->id;

                $order = $this->createOrder($user, $service, $order_info, $provider_id, $client_id, 'Instamojo', 'success', $request->get('payment_id'));

                $provider = $service->provider;
                $this->sendMailToClient($user, $order);
                $this->sendMailToProvider($provider, $order);

                Session::forget('order_info');
                Session::forget('return_from_mollie');
                Session::forget('service');

                $notification = trans('user_validation.Your order has been placed. Thanks for your new order');
                $notification = array('messege'=>$notification,'alert-type'=>'success');
                return redirect()->route('dashboard')->with($notification);
            }
        }else{
            $notification = trans('user_validation.Payment Faild');
            $notification = array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->route('payment', $service->slug)->with($notification);
        }
    }



    public function createOrder($user, $service, $order_info, $provider_id, $client_id, $payment_method, $payment_status, $tnx_info){

        $extra_services = json_decode($order_info->extras);
        $additional_amount = $order_info->extra_price;
        $additional_services = array();
        if($extra_services->ids){
            foreach($extra_services->ids as $index => $extra_service){
                $addition = AdditionalService::find($extra_services->ids[$index]);
                $single_extra_service = array(
                    'service_name' => $extra_services->names[$index],
                    'qty' => $extra_services->quantities[$index],
                    'price' => ($extra_services->quantities[$index] * $addition->price),
                );
                $additional_services[] = $single_extra_service;
            }
        }


        $order_additional_services = json_encode($additional_services);
        $order_note = $order_info->customer->order_note;
        $client_address = $order_info->customer;


        $order = new Order();
        $order->order_id = substr(rand(0,time()),0,10);
        $order->booking_date = $order_info->date;
        $order->appointment_schedule_id = $order_info->schedule_time_slot ?? 0;
        $order->schedule_time_slot = 0;
        $order->client_id = $client_id;
        $order->provider_id = $provider_id;
        $order->service_id = $service->id;
        $order->package_amount = $service->price;
        $order->additional_amount = $additional_amount;
        $order->total_amount = ($service->price + $additional_amount);
        $order->payment_method = $payment_method;
        $order->transection_id = $tnx_info;
        $order->payment_status = $payment_status;
        $order->order_status = 'awaiting_for_provider_approval';
        $order->package_features = $service->package_features;
        $order->additional_services = $order_additional_services;
        $order->order_note = $order_note;
        $order->client_address = json_encode($client_address);
        $order->save();

        return $order;
    }


    public function sendMailToClient($user, $order){
        MailHelper::setMailConfig();

        $setting = Setting::first();

        $template=EmailTemplate::where('id',8)->first();
        $subject=$template->subject;
        $message=$template->description;
        $message = str_replace('{{name}}',$user->name,$message);
        $message = str_replace('{{amount}}',$setting->currency_icon.$order->total_amount,$message);
        $message = str_replace('{{schedule_date}}',$order->booking_date,$message);
        $message = str_replace('{{order_id}}',$order->order_id,$message);
        Mail::to($user->email)->send(new OrderSuccessfully($message,$subject));
    }

    public function sendMailToProvider($provider, $order){
        // MailHelper::setMailConfig();

        // $setting = Setting::first();

        // $template=EmailTemplate::where('id',9)->first();
        // $subject=$template->subject;
        // $message=$template->description;
        // $message = str_replace('{{name}}',$provider->name,$message);
        // $message = str_replace('{{amount}}',$setting->currency_icon.$order->total_amount,$message);
        // $message = str_replace('{{schedule_date}}',$order->booking_date,$message);
        // $message = str_replace('{{order_id}}',$order->order_id,$message);
        // Mail::to($provider->email)->send(new OrderSuccessfully($message,$subject));
    }

    public function payWithPaymongo(Request $request){
        $tax_amount = 0;
        $total_price = 0;
        $coupon_price = 0;
        $shipping_fee = 0;

        $user = Auth::guard('web')->user();
        $billing = BillingAddress::where('user_id', $user->id)->first();
        $shipping = ShippingAddress::where('user_id', $user->id)->first();
        $cartContents = Cart::content();
        $shipping_method = Session::get('shipping_method');
        $shippingMethod = ShippingMethod::where('id',$shipping_method)->first();
        $shipping_fee = $shippingMethod->fee;
        foreach ($cartContents as $key => $content) {
            $tax = $content->options->tax * $content->qty;
            $tax_amount = $tax_amount + $tax;
        }

        $subTotal = 0;
        foreach ($cartContents as $cartContent) {
            $variantPrice = 0;
            foreach ($cartContent->options->variants as $indx => $variant) {
                $variantPrice += $cartContent->options->prices[$indx];
            }
            $productPrice = $cartContent->price ;
            $total = $productPrice * $cartContent->qty ;
            $subTotal += $total;
        }

        $total_price = $tax_amount + $subTotal;
        if(Session::get('coupon_price') && Session::get('offer_type')) {
            if(Session::get('offer_type') == 1) {
                $coupon_price = Session::get('coupon_price');
                $coupon_price = ($coupon_price / 100) * $total_price;
            }else {
                $coupon_price = Session::get('coupon_price');
            }
        }

        $total_price = $total_price - $coupon_price ;
        $total_price += $shipping_fee;
        $total_price = str_replace( array( '\'', '"', ',' , ';', '<', '>' ), '', $total_price);
        $setting = Setting::first();

        $additional_information = '';
        if(Session::get('addition_information')){
            $additional_information = Session::get('addition_information');
        }
        $agree_terms_condition = 'no';
        if(Session::get('agree_terms_condition')){
            $agree_terms_condition = Session::get('agree_terms_condition');
        }

        $amount_real_currency = $total_price;
        $amount_usd = round($total_price / $setting->currency_rate,2);
        $currency_rate = $setting->currency_rate;
        $currency_icon = $setting->currency_icon;
        $currency_name = $setting->currency_name;

        $paymongoPayment = PaymongoPayment::first();
        $price = $total_price * $paymongoPayment->currency_rate;
        $price = round($price);
        $currency_code = $paymongoPayment->currency_code;
        $setting=Setting::first();

        // create payment method
        require_once('vendor/autoload.php');
        $client = new \GuzzleHttp\Client();
        $card_number = $request->card_number;
        $cvc = $request->cvc;
        $month = $request->month;
        $year = $request->year;
        $code = base64_encode($paymongoPayment->public_key.':'.$paymongoPayment->secret_key);

        try{
            $response = $client->request('POST', 'https://api.paymongo.com/v1/payment_methods', [
                'body' => '{"data":{"attributes":{"details":{"card_number":"'.$card_number.'","exp_month":'.$month.',"exp_year":'.$year.',"cvc":"'.$cvc.'"},"type":"card"}}}',
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic '.$code.'',
                    'Content-Type' => 'application/json',
                ],
            ]);

        }catch (Exception $e) {
            $notification = trans('user_validation.Please provide valid card information');
            $notification = array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->back()->with($notification);
        }

        $response = json_decode($response->getBody(), true);
        $payment_method_id = $response['data']['id'];

        if($price < 100){
            $notification = trans('user_validation.Amount cannot be less than 100₱');
            $notification = array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->back()->with($notification);
        }

        $price = $price * 100;

        // create payment instant
        $client = new \GuzzleHttp\Client();
        $secret_code = base64_encode($paymongoPayment->secret_key);
        $response = $client->request('POST', 'https://api.paymongo.com/v1/payment_intents', [
        'body' => '{"data":{"attributes":{"amount":'.$price.',"payment_method_allowed":["card"],"payment_method_options":{"card":{"request_three_d_secure":"any"}},"currency":"'.$currency_code.'","capture_type":"automatic"}}}',
        'headers' => [
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.$secret_code.'',
            'Content-Type' => 'application/json',
        ],
        ]);

        $intent_response = json_decode($response->getBody(), true);
        $intent_client_key = $intent_response['data']['attributes']['client_key'];
        $intent_id = $intent_response['data']['id'];

        $client = new \GuzzleHttp\Client();

        // create payment
        $payment_response = $client->request('POST', 'https://api.paymongo.com/v1/payment_intents/'.$intent_id.'/attach', [
            'body' => '{"data":{"attributes":{"payment_method":"'.$payment_method_id.'","client_key":"'.$intent_client_key.'"}}}',
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Basic '.$secret_code.'',
                'Content-Type' => 'application/json',
            ],
        ]);

        $payment_response = json_decode($response->getBody(), true);

        if($payment_response['data']['attributes']['status'] != 'faild'){
            $order = new Order();
            $orderId = substr(rand(0,time()),0,10);
            $order->order_id = $orderId;
            $order->user_id = $user->id;
            $order->sub_total = $subTotal;
            $order->amount_real_currency = $total_price;
            $order->amount_usd = $amount_usd;
            $order->currency_rate = $currency_rate;
            $order->currency_icon = $currency_icon;
            $order->currency_name = $currency_name;
            $order->transection_id = $payment_response['data']['id'];
            $order->product_qty = Cart::count();
            $order->payment_method = 'Paymongo';
            $order->payment_status = 1;
            $order->shipping_method = $shippingMethod->title;
            $order->shipping_cost = $shippingMethod->fee;
            $order->coupon_coast = $coupon_price;
            $order->order_vat = $tax_amount;
            $order->order_status = 0;
            $order->cash_on_delivery = 0;
            $order->additional_info = $additional_information;
            $order->agree_terms_condition = $agree_terms_condition;
            $order->save();

            if(Session::get('coupon_name')){
                $coupon = Coupon::where(['code' => Session::get('coupon_name')])->first();
                $qty = $coupon->apply_qty;
                $qty = $qty +1;
                $coupon->apply_qty = $qty;
                $coupon->save();
            }

            $order_details = '';
            foreach ($cartContents as $key => $cartContent) {

                $productUnitPrice = 0;
                $variantPrice = 0;
                foreach ($cartContent->options->variants as $indx => $variant) {
                    $variantPrice += $cartContent->options->prices[$indx];
                }
                $productUnitPrice = $cartContent->price;

                $product = Product::find($cartContent->id);
                $orderProduct = new OrderProduct();
                $orderProduct->order_id = $order->id;
                $orderProduct->product_id = $cartContent->id;
                $orderProduct->seller_id = $product->vendor_id;
                $orderProduct->product_name = $cartContent->name;
                $orderProduct->unit_price = $productUnitPrice;
                $orderProduct->qty = $cartContent->qty;
                $orderProduct->vat = $cartContent->options->tax * $cartContent->qty;
                $orderProduct->save();

                $productStock = Product::find($cartContent->id);
                $qty = $productStock->qty - $cartContent->qty;
                $productStock->qty = $qty;
                $productStock->save();

                if(count($cartContent->options->variants) > 0) {
                    foreach($cartContent->options->variants as $index => $variant) {
                        $productVariant = new OrderProductVariant();
                        $productVariant->order_product_id = $orderProduct->id;
                        $productVariant->product_id = $cartContent->id;
                        $productVariant->variant_name = $variant;
                        $productVariant->variant_value = $cartContent->options->values[$index];
                        $productVariant->variant_price = $cartContent->options->prices[$index];
                        $productVariant->save();
                    }
                }

                $order_details.='Product: '.$cartContent->name. '<br>';
                $order_details.='Quantity: '. $cartContent->qty .'<br>';
                $order_details.='Price: '.$setting->currency_icon . $cartContent->qty * $productUnitPrice .'<br>';
            }

            $orderAddress = new OrderAddress();
            $orderAddress->order_id = $order->id;
            $orderAddress->billing_name = $billing->name;
            $orderAddress->billing_email = $billing->email;
            $orderAddress->billing_phone = $billing->phone;
            $orderAddress->billing_address = $billing->address;
            $orderAddress->billing_country = $billing->country ? $billing->country->name : '';
            $orderAddress->billing_state = $billing->countryState ? $billing->countryState->name : '';
            $orderAddress->billing_city = $billing->city ? $billing->city->name : '';
            $orderAddress->billing_zip_code = $billing->zip_code;
            $orderAddress->shipping_name = $shipping->name;
            $orderAddress->shipping_email = $shipping->email;
            $orderAddress->shipping_phone = $shipping->phone;
            $orderAddress->shipping_address = $shipping->address;
            $orderAddress->shipping_country = $shipping->country ? $shipping->country->name : '';
            $orderAddress->shipping_state = $shipping->countryState ? $shipping->countryState->name : '';
            $orderAddress->shipping_city = $shipping->city ? $shipping->city->name : '';
            $orderAddress->shipping_zip_code = $shipping->zip_code;
            $orderAddress->save();

            MailHelper::setMailConfig();

            $template=EmailTemplate::where('id',6)->first();
            $subject=$template->subject;
            $message=$template->description;
            $message = str_replace('{{user_name}}',$user->name,$message);
            $message = str_replace('{{total_amount}}',$setting->currency_icon.$total_price,$message);
            $message = str_replace('{{payment_method}}','Paymongo',$message);
            $message = str_replace('{{payment_status}}','Success',$message);
            $message = str_replace('{{order_status}}','Pending',$message);
            $message = str_replace('{{order_date}}',$order->created_at->format('d F, Y'),$message);
            $message = str_replace('{{order_detail}}',$order_details,$message);
            Mail::to($user->email)->send(new OrderSuccessfully($message,$subject));

            Session::forget('hipping_method');
            Session::forget('coupon_price');
            Session::forget('coupon_name');
            Session::forget('offer_type');
            Session::forget('agree_terms_condition');
            Session::forget('addition_information');
            Cart::destroy();

            $notification = trans('user_validation.Payment Successfully');
            $notification = array('messege'=>$notification,'alert-type'=>'success');
            return redirect()->route('user.order')->with($notification);
        }else{
            $notification = trans('user_validation.Payment Faild');
            $notification = array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->back()->with($notification);
        }

    }

    public function payWithPaymongoGrabPay(){
        $tax_amount = 0;
        $total_price = 0;
        $coupon_price = 0;
        $shipping_fee = 0;

        $user = Auth::guard('web')->user();
        $billing = BillingAddress::where('user_id', $user->id)->first();
        $shipping = ShippingAddress::where('user_id', $user->id)->first();
        $cartContents = Cart::content();
        $shipping_method = Session::get('shipping_method');
        $shippingMethod = ShippingMethod::where('id',$shipping_method)->first();
        $shipping_fee = $shippingMethod->fee;
        foreach ($cartContents as $key => $content) {
            $tax = $content->options->tax * $content->qty;
            $tax_amount = $tax_amount + $tax;
        }

        $subTotal = 0;
        foreach ($cartContents as $cartContent) {
            $variantPrice = 0;
            foreach ($cartContent->options->variants as $indx => $variant) {
                $variantPrice += $cartContent->options->prices[$indx];
            }
            $productPrice = $cartContent->price ;
            $total = $productPrice * $cartContent->qty ;
            $subTotal += $total;
        }

        $total_price = $tax_amount + $subTotal;
        if(Session::get('coupon_price') && Session::get('offer_type')) {
            if(Session::get('offer_type') == 1) {
                $coupon_price = Session::get('coupon_price');
                $coupon_price = ($coupon_price / 100) * $total_price;
            }else {
                $coupon_price = Session::get('coupon_price');
            }
        }

        $total_price = $total_price - $coupon_price ;
        $total_price += $shipping_fee;
        $total_price = str_replace( array( '\'', '"', ',' , ';', '<', '>' ), '', $total_price);
        $setting = Setting::first();

        $paymongoPayment = PaymongoPayment::first();
        $price = $total_price * $paymongoPayment->currency_rate;
        $price = round($price);
        $success_url = route('user.checkout.paymongo-payment-success');
        $faild_url = route('user.checkout.paymongo-payment-cancled');
        $currency_code = $paymongoPayment->currency_code;

        if($price < 100){
            $notification = trans('user_validation.Amount cannot be less than 100₱');
            $notification = array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->route('user.checkout.payment')->with($notification);
        }

        $price = $price * 100;

        require_once('vendor/autoload.php');
        $code = base64_encode($paymongoPayment->public_key.':'.$paymongoPayment->secret_key);
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', 'https://api.paymongo.com/v1/sources', [
        'body' => '{"data":{"attributes":{"amount":'.$price.',"redirect":{"success":"'.$success_url.'","failed":"'.$faild_url.'"},"type":"grab_pay","currency":"'.$currency_code.'"}}}',
        'headers' => [
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.$code.'',
            'Content-Type' => 'application/json',
        ],
        ]);
        $response = json_decode($response->getBody(), true);
        session()->put('payment_id',$response['data']['id']);
        return redirect()->to($response['data']['attributes']['redirect']['checkout_url']);
    }

    public function payWithPaymongoGcash(){
        $tax_amount = 0;
        $total_price = 0;
        $coupon_price = 0;
        $shipping_fee = 0;

        $user = Auth::guard('web')->user();
        $billing = BillingAddress::where('user_id', $user->id)->first();
        $shipping = ShippingAddress::where('user_id', $user->id)->first();
        $cartContents = Cart::content();
        $shipping_method = Session::get('shipping_method');
        $shippingMethod = ShippingMethod::where('id',$shipping_method)->first();
        $shipping_fee = $shippingMethod->fee;
        foreach ($cartContents as $key => $content) {
            $tax = $content->options->tax * $content->qty;
            $tax_amount = $tax_amount + $tax;
        }

        $subTotal = 0;
        foreach ($cartContents as $cartContent) {
            $variantPrice = 0;
            foreach ($cartContent->options->variants as $indx => $variant) {
                $variantPrice += $cartContent->options->prices[$indx];
            }
            $productPrice = $cartContent->price ;
            $total = $productPrice * $cartContent->qty ;
            $subTotal += $total;
        }

        $total_price = $tax_amount + $subTotal;
        if(Session::get('coupon_price') && Session::get('offer_type')) {
            if(Session::get('offer_type') == 1) {
                $coupon_price = Session::get('coupon_price');
                $coupon_price = ($coupon_price / 100) * $total_price;
            }else {
                $coupon_price = Session::get('coupon_price');
            }
        }

        $total_price = $total_price - $coupon_price ;
        $total_price += $shipping_fee;
        $total_price = str_replace( array( '\'', '"', ',' , ';', '<', '>' ), '', $total_price);
        $setting = Setting::first();

        $paymongoPayment = PaymongoPayment::first();
        $price = $total_price * $paymongoPayment->currency_rate;
        $price = round($price);
        $success_url = route('user.checkout.paymongo-payment-success');
        $faild_url = route('user.checkout.paymongo-payment-cancled');
        $currency_code = $paymongoPayment->currency_code;

        if($price < 100){
            $notification = trans('user_validation.Amount cannot be less than 100₱');
            $notification = array('messege'=>$notification,'alert-type'=>'error');
            return redirect()->route('user.checkout.payment')->with($notification);
        }

        $price = $price * 100;

        require_once('vendor/autoload.php');
        $code = base64_encode($paymongoPayment->public_key.':'.$paymongoPayment->secret_key);
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', 'https://api.paymongo.com/v1/sources', [
        'body' => '{"data":{"attributes":{"amount":'.$price.',"redirect":{"success":"'.$success_url.'","failed":"'.$faild_url.'"},"type":"gcash","currency":"'.$currency_code.'"}}}',
        'headers' => [
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.$code.'',
            'Content-Type' => 'application/json',
        ],
        ]);
        $response = json_decode($response->getBody(), true);
        session()->put('payment_id',$response['data']['id']);
        return redirect()->to($response['data']['attributes']['redirect']['checkout_url']);
    }

    public function paymongoPaymentSuccess(Request $request){
        $tax_amount = 0;
        $total_price = 0;
        $coupon_price = 0;
        $shipping_fee = 0;

        $user = Auth::guard('web')->user();
        $billing = BillingAddress::where('user_id', $user->id)->first();
        $shipping = ShippingAddress::where('user_id', $user->id)->first();
        $cartContents = Cart::content();
        $shipping_method = Session::get('shipping_method');
        $shippingMethod = ShippingMethod::where('id',$shipping_method)->first();
        $shipping_fee = $shippingMethod->fee;
        foreach ($cartContents as $key => $content) {
            $tax = $content->options->tax * $content->qty;
            $tax_amount = $tax_amount + $tax;
        }

        $subTotal = 0;
        foreach ($cartContents as $cartContent) {
            $variantPrice = 0;
            foreach ($cartContent->options->variants as $indx => $variant) {
                $variantPrice += $cartContent->options->prices[$indx];
            }
            $productPrice = $cartContent->price ;
            $total = $productPrice * $cartContent->qty ;
            $subTotal += $total;
        }

        $total_price = $tax_amount + $subTotal;
        if(Session::get('coupon_price') && Session::get('offer_type')) {
            if(Session::get('offer_type') == 1) {
                $coupon_price = Session::get('coupon_price');
                $coupon_price = ($coupon_price / 100) * $total_price;
            }else {
                $coupon_price = Session::get('coupon_price');
            }
        }

        $total_price = $total_price - $coupon_price ;
        $total_price += $shipping_fee;
        $total_price = str_replace( array( '\'', '"', ',' , ';', '<', '>' ), '', $total_price);
        $setting = Setting::first();

        $additional_information = '';
        if(Session::get('addition_information')){
            $additional_information = Session::get('addition_information');
        }
        $agree_terms_condition = 'no';
        if(Session::get('agree_terms_condition')){
            $agree_terms_condition = Session::get('agree_terms_condition');
        }

        $amount_real_currency = $total_price;
        $amount_usd = round($total_price / $setting->currency_rate,2);
        $currency_rate = $setting->currency_rate;
        $currency_icon = $setting->currency_icon;
        $currency_name = $setting->currency_name;

        $order = new Order();
        $orderId = substr(rand(0,time()),0,10);
        $order->order_id = $orderId;
        $order->user_id = $user->id;
        $order->sub_total = $subTotal;
        $order->amount_real_currency = $total_price;
        $order->amount_usd = $amount_usd;
        $order->currency_rate = $currency_rate;
        $order->currency_icon = $currency_icon;
        $order->currency_name = $currency_name;
        $order->transection_id = session()->get('payment_id');
        $order->product_qty = Cart::count();
        $order->payment_method = 'Paymongo';
        $order->payment_status = 1;
        $order->shipping_method = $shippingMethod->title;
        $order->shipping_cost = $shippingMethod->fee;
        $order->coupon_coast = $coupon_price;
        $order->order_vat = $tax_amount;
        $order->order_status = 0;
        $order->cash_on_delivery = 0;
        $order->additional_info = $additional_information;
        $order->agree_terms_condition = $agree_terms_condition;
        $order->save();

        if(Session::get('coupon_name')){
            $coupon = Coupon::where(['code' => Session::get('coupon_name')])->first();
            $qty = $coupon->apply_qty;
            $qty = $qty +1;
            $coupon->apply_qty = $qty;
            $coupon->save();
        }

        $order_details = '';
        foreach ($cartContents as $key => $cartContent) {

            $productUnitPrice = 0;
            $variantPrice = 0;
            foreach ($cartContent->options->variants as $indx => $variant) {
                $variantPrice += $cartContent->options->prices[$indx];
            }
            $productUnitPrice = $cartContent->price;

            $product = Product::find($cartContent->id);
            $orderProduct = new OrderProduct();
            $orderProduct->order_id = $order->id;
            $orderProduct->product_id = $cartContent->id;
            $orderProduct->seller_id = $product->vendor_id;
            $orderProduct->product_name = $cartContent->name;
            $orderProduct->unit_price = $productUnitPrice;
            $orderProduct->qty = $cartContent->qty;
            $orderProduct->vat = $cartContent->options->tax * $cartContent->qty;
            $orderProduct->save();

            $productStock = Product::find($cartContent->id);
            $qty = $productStock->qty - $cartContent->qty;
            $productStock->qty = $qty;
            $productStock->save();

            if(count($cartContent->options->variants) > 0) {
                foreach($cartContent->options->variants as $index => $variant) {
                    $productVariant = new OrderProductVariant();
                    $productVariant->order_product_id = $orderProduct->id;
                    $productVariant->product_id = $cartContent->id;
                    $productVariant->variant_name = $variant;
                    $productVariant->variant_value = $cartContent->options->values[$index];
                    $productVariant->variant_price = $cartContent->options->prices[$index];
                    $productVariant->save();
                }
            }

            $order_details.='Product: '.$cartContent->name. '<br>';
            $order_details.='Quantity: '. $cartContent->qty .'<br>';
            $order_details.='Price: '.$setting->currency_icon . $cartContent->qty * $productUnitPrice .'<br>';
        }

        $orderAddress = new OrderAddress();
        $orderAddress->order_id = $order->id;
        $orderAddress->billing_name = $billing->name;
        $orderAddress->billing_email = $billing->email;
        $orderAddress->billing_phone = $billing->phone;
        $orderAddress->billing_address = $billing->address;
        $orderAddress->billing_country = $billing->country ? $billing->country->name : '';
        $orderAddress->billing_state = $billing->countryState ? $billing->countryState->name : '';
        $orderAddress->billing_city = $billing->city ? $billing->city->name : '';
        $orderAddress->billing_zip_code = $billing->zip_code;
        $orderAddress->shipping_name = $shipping->name;
        $orderAddress->shipping_email = $shipping->email;
        $orderAddress->shipping_phone = $shipping->phone;
        $orderAddress->shipping_address = $shipping->address;
        $orderAddress->shipping_country = $shipping->country ? $shipping->country->name : '';
        $orderAddress->shipping_state = $shipping->countryState ? $shipping->countryState->name : '';
        $orderAddress->shipping_city = $shipping->city ? $shipping->city->name : '';
        $orderAddress->shipping_zip_code = $shipping->zip_code;
        $orderAddress->save();

        MailHelper::setMailConfig();

        $template=EmailTemplate::where('id',6)->first();
        $subject=$template->subject;
        $message=$template->description;
        $message = str_replace('{{user_name}}',$user->name,$message);
        $message = str_replace('{{total_amount}}',$setting->currency_icon.$total_price,$message);
        $message = str_replace('{{payment_method}}','Paymongo',$message);
        $message = str_replace('{{payment_status}}','Success',$message);
        $message = str_replace('{{order_status}}','Pending',$message);
        $message = str_replace('{{order_date}}',$order->created_at->format('d F, Y'),$message);
        $message = str_replace('{{order_detail}}',$order_details,$message);
        Mail::to($user->email)->send(new OrderSuccessfully($message,$subject));

        Session::forget('hipping_method');
        Session::forget('coupon_price');
        Session::forget('coupon_name');
        Session::forget('offer_type');
        Session::forget('agree_terms_condition');
        Session::forget('addition_information');
        Cart::destroy();

        $notification = trans('user_validation.Payment Successfully');
        $notification = array('messege'=>$notification,'alert-type'=>'success');
        return redirect()->route('user.order')->with($notification);
    }


    public function paymongoPaymentCancled(Request $request){
        $notification = trans('user_validation.Payment Faild');
        $notification = array('messege'=>$notification,'alert-type'=>'error');
        return redirect()->route('user.checkout.payment')->with($notification);
    }






}
