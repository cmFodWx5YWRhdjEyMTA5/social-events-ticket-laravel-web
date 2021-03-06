<?php

namespace App\Http\Controllers\Api;

use App\EventScanner;
use App\Helpers\ValidUserScannerPassword;
use App\Http\Traits\UniversalMethods;
use App\Scanner;
use App\Ticket;
use App\TicketScan;
use App\Transformers\ScannerTransformer;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ScannerAuthController extends Controller
{
    use SendsPasswordResetEmails, AuthenticatesUsers;


    public function index()
    {
        $scanners = Scanner::all();

        return response()->json([
            'success'=>true,
            'message'      => 'Found '.count($scanners),
            'data' => fractal($scanners,ScannerTransformer::class),
        ]);
    }


    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'email'    => 'required|email|exists:scanners,email',
                    'password' => ['required', new ValidUserScannerPassword()],
                ],
                [
                    'email.required'    => 'Please provide an email address',
                    'email.email'       => 'Email address is invalid',
                    'email.exists'      => 'You do not have an account. Kindly sign up!',
                    'password.required' => 'Please provide a password',
                ]
            );

            if ($validator->fails()) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => '' . UniversalMethods::getValidationErrorsAsString($validator->errors()->toArray()),
                        'data'    => null
                    ], 200
                );
            } else {
                //attempt to authenticate user
                $credentials = $request->only('email', 'password');
                if (!$token = auth('scanner')->attempt($credentials)) {
                    return response()->json(
                        [
                            'success' => false,
                            'message' => 'Email or Password is Incorrect!',
                            'data'    => null,
                        ], 200
                    );
                } else {
                    $scanner = $this->guard()->user();

                    return response()->json(
                        [
                            'success'      => true,
                            'message'      => 'Scanner Successfully Logged In. Welcome!',
                            'data'         => fractal($scanner, ScannerTransformer::class),
                            'access_token' => $token,
                            'expires_at'   => Carbon::now()->addSeconds(auth('scanner')->factory()->getTTL() * 60)->toDateTimeString()
                        ], 200
                    );

                }
            }
        } catch ( \Exception $exception ) {
            logger("error logging in a scanner: ".$exception->getMessage()."\n".$exception->getTraceAsString());
            return response()->json([
                'success' => false,
                'message'   => 'Sorry, something unexpected happened! Try again!',
            ]);
        }
    }

    public function refresh()
    {
        try {
            return response()->json([
            'access_token' => auth('scanner')->refresh(),
            'success' => true,
            'message' => 'new token generated',
            'expires_at' => Carbon::now()->addSeconds(auth('scanner')->factory()->getTTL() * 60)->toDateTimeString()
        ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired! Logout and login to continue.',
            ]);
        }
    }

    public function reset_password(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'email' => 'required|email|exists:scanners,email'
            ],
            [
                'email.required' => 'Please provide an email address',
                'email.email'    => 'Email address is invalid',
                'email.exists'   => 'You do not have an account. Kindly sign up!',
            ]
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => '' . UniversalMethods::getValidationErrorsAsString($validator->errors()->toArray()),
                    'data'    => null
                ], 200
            );
        } else {

            //send an email
            $response = $this->sendResetLinkEmail($request);

            return $response;
        }

    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );
        if ($response == Password::RESET_LINK_SENT){
            return response()->json(
                [
                    'success' => true,
                    'message' => 'Password Reset Link Email sent successfully. Kindly check your inbox!',
                    'data'    => null
                ], 200
            );
        }else{
            return response()->json(
                [
                    'success' => true,
                    'message' => 'Sending the Reset Link Email Failed!',
                    'data'    => null
                ], 200
            );

        }

    }
    //custom function to check ticket validity when scanning
    public function events_tickets(Request $request){

        try{
            $validator =Validator::make($request->all(),
            [
                'validation_token' => 'required|exists:tickets,validation_token'
            ],
            [
                'validation_token.required' => 'Please provide a token',
                'validation_token.exists' => 'Ticket not found'
            ]);

            if ($validator->fails()) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => '' . UniversalMethods::getValidationErrorsAsString($validator->errors()->toArray()),
                        'data'    => []
                    ], 201
                );
            } else{
                //
                $ticket = Ticket::where('validation_token',$request->validation_token)->first();
                $ticket_id = $ticket->id;

                $ticketScanQueryBuilder = TicketScan::where('ticket_id',$ticket_id);

                if(!$ticketScanQueryBuilder->exists()){

                    $ticket = new TicketScan();
                    $ticket->ticket_id = $ticket_id;
                    $ticket->scanner_id = $request->scanner_id;
                    $ticket->save();
                    return response()->json([
                        'success' => true,
                        'message' => "Ticket is valid",
                        'data' => $ticket
                    ],200);

                }else{
                    $ticket_scan = $ticketScanQueryBuilder->first();

                    $time_difference = $ticket_scan->created_at->diffForHumans();

                    return response()->json([
                        'success' =>false,
                        'message'=> "Ticket was scanned ".$time_difference,
                    ],201
                    );
                }
            }


        }catch(\Exception $exception){
            return $exception->getMessage();
        }

    }


    //Custom guard for scanner
    protected function guard()
    {
        return Auth::guard('scanner');
    }

    //Password Broker for admin Model
    public function broker()
    {
        return Password::broker('scanners');
    }
}
