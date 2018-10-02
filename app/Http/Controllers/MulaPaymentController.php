<?php

namespace App\Http\Controllers;

use App\Event;
use App\Http\Traits\UniversalMethods;
use App\PaymentRequest;
use App\PaymentResponse;
use App\TicketCustomer;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MulaPaymentController extends Controller
{

    public $secretKey;
    public $ivKey;

    public function __construct()
    {
        $this->secretKey = "rHkgZYVKnCGLXFRb";
        $this->ivKey = "HtJBgFPQh3qwcRmk";

    }

    public function index()
    {
        return view('payments.button');
    }

    /**
     * we need to pass 
     * event_id, event_ticket_cattegory_id & its no of ticke
     */

    public function encryptData(Request $request)
    {
        $data_array = [];

      
       parse_str($request->getContent(),$data);

       return response()->json(['data_array'=> $data_array]);

    //    dd($request->all());
    //    $validator = Validator::make($data_array, [
    //         'first_name'=>'required',
    //         'last_name'=>'required',            
    //         'email'=>'required',
    //         'phone'=>'required',
    //         'event_id'=>'required',
    //         'subtotal'=>'required',
    //         'ticket_sale_end_date_time'=>'required'
    //     ]); 

    //     if($validator->fails()){

    //         return redirect()->back()->withInput();
    //     }
        //     return response()->json([
        //         'message' => 'failed because of '.UniversalMethods::getValidationErrorsAsString($validator->errors()->toArray())
        //     ]);
        // }


        $ticket_customer = new TicketCustomer;
        $ticket_customer->first_name = $data_array['first_name'];
        $ticket_customer->last_name = $data_array['last_name'];        
        $ticket_customer->email = $data_array['email'];
        $ticket_customer->phone_number = $data_array['phone'];
        if($user = User::where('email',$data_array['email'])->first()!==null){
            $ticket_customer->user_id = $user->id;
        }
        $ticket_customer->save();


        $event_id = $request->event_id;
        // $ticket_customer_id = $data_array['customer_id'];

        $event = Event::find($event_id);
        // $ticket_customer = TicketCustomer::find($ticket_customer_id);

        $payload = [
            "merchantTransactionID" => now()->timestamp."". uniqid(),
            "customerFirstName"     => $ticket_customer->first_name,
            "customerLastName"      => $ticket_customer->last_name,
            "MSISDN"                => UniversalMethods::formatPhoneNumber($ticket_customer->phone_number),
            "customerEmail"         => $ticket_customer->email,
            "amount"                => $data_array['subtotal'], //TODO::get the amount for the type of ticket the customer has decided to purchase
            "currencyCode"          => "KES",
            "accountNumber"         => "123456",
            "serviceCode"           => "APISBX3857",
            "dueDate"               => $data_array['ticket_sale_end_date_time'], //TODO::this is to be replaced by the ticket_sale_end_date_time
            "serviceDescription"    => "Payment for ".$event->name,
            "accessKey"             => '$2a$08$Ga/jSxv1qturlAr8SkHhzOaprXnfOJUTqB6fLRrc/0nSYpRlAd96e',
            "countryCode"           => "KE",
            "languageCode"          => "en",
            "successRedirectUrl"    =>  route("success_url"),
            "failRedirectUrl"       =>  route("failure_url"),
            "paymentWebhookUrl"     =>  route("process_payment"),
        ];

        //attach a pending payment request
        PaymentRequest::create([
            'merchantTransactionID'     => $payload['merchantTransactionID'],
            'MSISDN'                    =>$payload['MSISDN'],
            'customerEmail'             => $payload['customerEmail'],
            'amount'                    => $payload['amount']
        ]);

        //The encryption method to be used
        $encrypt_method = "AES-256-CBC";

        // Hash the secret key
        $key = hash('sha256', $this->secretKey);

        // Hash the iv - encrypt method AES-256-CBC expects 16 bytes
        $iv = substr(hash('sha256', $this->ivKey), 0, 16);
        $encrypted = openssl_encrypt(
            json_encode($payload, true), $encrypt_method, $key, 0, $iv
        );

        //Base 64 Encode the encrypted payload
        $encryptedPayload = base64_encode($encrypted);

        return response()->json([
            'params' => $encryptedPayload,
            'accessKey' => $payload['accessKey'],
            'countryCode' => $payload['countryCode']
        ]);

    }

    public function processPayment(Request $request)
    {
        $payload = $request->getContent();
        $result = json_decode($payload);
        try {


            //save the response to the db
            PaymentResponse::create([
                'type'     => 'webhook',
                'response' => $payload
            ]);

            //log the response
            logger("PROCESS PAYMENT::  " . $payload);


            //confirm whether the payment should be accepted or not
            //check whether the MSISDN is recognized
            $pending_payment_request = PaymentRequest::where('merchantTransactionID', $result->merchantTransactionID)
                ->where('MSISDN', UniversalMethods::formatPhoneNumber($result->MSISDN))
                ->where('amount','=', $result->amountPaid)
                ->where('pending', true)
                ->first();

            if ($pending_payment_request != null) {

                //TODO:: create an tickets record and capture
                //TODO:: ticket_customer_id, event_ticket_category_id, number_of_tickets

                //accept the payment
                return response()->json([
                    'checkoutRequestID'     => $result->checkoutRequestID,
                    'merchantTransactionID' => $result->merchantTransactionID,
                    'statusCode'            => 183,
                    'statusDescription'     => "Successful Payment",
                    'receiptNumber'         => $result->merchantTransactionID,
                ]);
            }else{
                //reject the payment
                return response()->json([
                    'checkoutRequestID'     => $result->checkoutRequestID,
                    'merchantTransactionID' => $result->merchantTransactionID,
                    'statusCode'            => 180,
                    'statusDescription'     => "Payment failed",
                    'receiptNumber'         => $result->merchantTransactionID,
                ]);
            }

        } catch ( \Exception $exception ) {
            logger("PAYMENT PROCESS error:: " . $exception->getMessage() . "\nTrace::: " . $exception->getTraceAsString());
            //reject the payment
            return response()->json([
                'checkoutRequestID'     => $result->checkoutRequestID,
                'merchantTransactionID' => $result->merchantTransactionID,
                'statusCode'            => 180,
                'statusDescription'     => "Payment declined",
                'receiptNumber'         => $result->merchantTransactionID,
            ]);
        }
    }

    public function success(Request $request)
    {
        try {
            $payload = $request->getContent();
            //save the response to the db
            PaymentResponse::create([
                'type'     => 'success',
                'response' => $payload
            ]);
            //log the payment
            logger("PAYMENT SUCCESS::  " . $payload);

            //display a success message to the user
            return view('payments.success');

        } catch ( \Exception $exception ) {
            logger("PAYMENT SUCCESS error:: " . $exception->getMessage() . "\nTrace::: " . $exception->getTraceAsString());
        }
    }

    public function failure(Request $request)
    {
        try {
            $payload = $request->getContent();
            //save the response to the db
            PaymentResponse::create([
                'type'     => 'failure',
                'response' => $payload
            ]);
            //log the payment
            logger("PAYMENT FAILURE::  " . $payload);

            //display a success message to the user
            return view('payments.failure');
        } catch ( \Exception $exception ) {
            logger("PAYMENT FAILURE error:: " . $exception->getMessage() . "\nTrace::: " . $exception->getTraceAsString());
        }
    }

    /*
     * Mobile METHODS
     */

    public function mobile_success(Request $request)
    {
        try {
            $payload = $request->getContent();
            //save the response to the db
            PaymentResponse::create([
                'type'     => 'mobile_success',
                'response' => $payload
            ]);
            //log the payment
            logger("PAYMENT SUCCESS::  " . $payload);

            //display a success message to the user
            return view('payments.mobile_success');

        } catch ( \Exception $exception ) {
            logger("PAYMENT SUCCESS error:: " . $exception->getMessage() . "\nTrace::: " . $exception->getTraceAsString());
        }
    }

    public function mobile_failure(Request $request)
    {
        try {
            $payload = $request->getContent();
            //save the response to the db
            PaymentResponse::create([
                'type'     => 'mobile_failure',
                'response' => $payload
            ]);
            //log the payment
            logger("PAYMENT FAILURE::  " . $payload);

            //display a success message to the user
            return view('payments.mobile_failure');
        } catch ( \Exception $exception ) {
            logger("PAYMENT FAILURE error:: " . $exception->getMessage() . "\nTrace::: " . $exception->getTraceAsString());
        }
    }
}
