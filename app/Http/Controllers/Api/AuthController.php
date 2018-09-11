<?php

namespace App\Http\Controllers\Api;

use App\Follower;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Traits\UniversalMethods;
use App\Notification;
use App\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    //Sends Password Reset emails
    use SendsPasswordResetEmails;

    CONST APPROVED_FOLLOW_REQUEST = 1;
    CONST PENDING_FOLLOW_REQUEST = 2;
    CONST REJECTED_FOLLOW_REQUEST = 3;


    /**
     * Register an app user
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register_user(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'username'   => 'required',
                'first_name' => 'required',
                'last_name'  => 'required',
                'email'      => 'required|email|unique:users,email',
                'password'   => 'required|min:8'
            ],
            [
                'username.required'   => 'Please provide a username',
                'first_name.required' => 'Please provide your first name',
                'last_name.required'  => 'Please provide your last name',
                'email.required'      => 'Please provide your email',
                'email.email'         => 'Email address is invalid',
                'email.unique'        => 'The email address is already in use',
                'password.required'   => 'Please provide a password',
                'password.min'        => 'Password must be at least 8 characters',
            ]
        );

        if ($validator->fails()) {

            return response()->json(
                [
                    'success' => false,
                    'message' => '' . UniversalMethods::getValidationErrorsAsString($validator->errors()->toArray()),
                    'data'    => []
                ], 200
            );
        } else {

            $user = User::create($request->all());

            if ($user) {

                return response()->json(
                    [
                        'success' => true,
                        'message' => 'User Account Created Successfully. Welcome!',
                        'data'    => UserResource::make($user),
                    ], 200
                );
            } else {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'User Account Creation Failed!',
                        'data'    => [],
                    ], 500
                );
            }
        }
    }


    public function login_user(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'email'    => 'required|email|exists:users,email',
                'password' => 'required|min:8'
            ],
            [
                'email.required'    => 'Please provide an email address',
                'email.email'       => 'Email address is invalid',
                'email.exists'      => 'You do not have an account. Kindly sign up!',
                'password.required' => 'Please provide a password',
                'password.min'      => 'Password must be at least 8 characters',
            ]
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => '' . UniversalMethods::getValidationErrorsAsString($validator->errors()->toArray()),
                    'data'    => []
                ], 200
            );
        } else {
            //attempt to authenticate user
            if (Auth::attempt($request->only(['email', 'password']))) {

                return response()->json(
                    [
                        'success' => true,
                        'message' => 'User Successfully Logged In. Welcome!',
                        'data'    => UserResource::make(Auth::user()),
                    ], 200
                );
            } else {
                return response()->json(
                    [
                        'success' => true,
                        'message' => 'Email or Password is Incorrect!',
                        'data'    => [],
                    ], 200
                );
            }
        }
    }

    public function reset_password_user(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'email' => 'required|email|exists:users,email'
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
                    'data'    => []
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
     * @param  \Illuminate\Http\Request $request
     *
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
        if ($response == Password::RESET_LINK_SENT) {
            return response()->json(
                [
                    'success' => true,
                    'message' => 'Password Reset Link Email sent successfully. Kindly check your inbox!',
                    'data'    => []
                ], 200
            );
        } else {
            return response()->json(
                [
                    'success' => true,
                    'message' => 'Sending the Reset Link Email Failed!',
                    'data'    => []
                ], 200
            );

        }

    }

    public function user_relations($user_id)
    {
        try {
            $data = $this->fetch_relations($user_id);

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Successfully fetched your people!',
                    'data'    => $data,
                ]
            );
        } catch ( \Exception $exception ) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Something went wrong, sorry: ' . $exception->getMessage(),
                    'data'    => [],
                ]
            );
        }
    }

    /**
     * @param $user_id
     *
     * @return array
     */
    public function fetch_relations($user_id): array
    {
        $user = User::findOrFail($user_id);
        $data = [];
        if ($user) {
            $data =
                [
                    'followers' => $user->followers,
                    'following' => $user->following
                ];
        }

        return $data;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function follow(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'follower_id'     => 'required|exists:users,id',
                    'followed_id'     => 'required|exists:users,id|different:follower_id',
                    'follow_request_response'          => 'sometimes'
                ],
                [
                    'follower_id.required'      => 'Kindly sign up',
                    'follower_id.exists'        => 'Kindly sign up!',
                    'followed_id:required'    => 'Kindly sign up!',
                    'followed_id:exists'      => 'Kindly sign up!',
                    'followed_id.different'   => 'You cannot follow yourself!'
                ]
            );

            if ($validator->fails()) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => '' . UniversalMethods::getValidationErrorsAsString($validator->errors()->toArray()),
                        'data'    => []
                    ], 200
                );
            }

            $follower_id = $request->follower_id;
            $followed_id = $request->followed_id;
            $follow_request_response = (bool) $request->follow_request_response;

//            dd($follow_request_response);

            $follow_record = Follower::where('follower_id', $follower_id)
                ->where('followed_id', $followed_id)
                ->first();

            $follower = User::find($follower_id);
            $followed = User::find($followed_id);

            //relationship doesn't exist
            if ($follow_record == null) {

                //check whether the user allows to be followed automatically
                //otherwise, send a follow request
                if ($followed->auto_follow_status) {

                    //raise a follow record with pending status
                    $follower->follows($followed_id);

                    //raise a follow notification for the followed user
                    Notification::create(
                        [
                            'initializer_id' => $follower_id,
                            'recipient_id'  => $followed_id,
                            'type' => 3,
                            'model_id'  =>  null,
                            'seen'      => false
                        ]
                    );

                } else {
                    //raise a follow record with pending status
                    $follower->follows($followed_id, AuthController::PENDING_FOLLOW_REQUEST);

                    //raise follow_request notification for the followed user
                    Notification::create(
                        [
                            'initializer_id' => $follower_id,
                            'recipient_id'  => $followed_id,
                            'type' => 4,
                            'model_id'  =>  null,
                            'seen'      => false
                        ]
                    );

                    //TODO:: also send them an FCM notification

                }
            } //relationship exists
            else {
                if ($follow_record->status == 1) {
                    $follower->unfollows($followed_id);
                }
                elseif ($follow_record->status == 2) {
                    if ($follow_request_response == true) {
                        $follower->approve_following($followed_id);

                        //raise a follow notification for the followed user
                        Notification::where('initializer_id',$follower_id)
                        ->where('recipient_id', $followed_id)
                        ->where('type',4)
                        ->update(
                            [
                                'type' => 3,
                                'model_id'  =>  null,
                                'seen'      => false
                            ]
                        );

                    }else{
                        $follower->unfollows($followed_id);
                    }
                }
            }

            $data = $this->fetch_relations($follower_id);

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Successfully fetched your people!',
                    'data'    => $data,
                ]
            );
        } catch ( \Exception $exception ) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Something went wrong, sorry: \n' . $exception->getMessage().' ------ '.$exception->getTraceAsString(),
                    'data'    => [],
                ]
            );
        }
    }

    public function follow_request(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'follower_id'     => 'required|exists:users,id',
                'followed_id'     => 'required|exists:users,id|different:follower_id',
                'status'
            ],
            [
                'follower_id.required'      => 'Kindly sign up',
                'follower_id.exists'        => 'Kindly sign up!',
                'followed_id:required'    => 'Kindly sign up!',
                'followed_id:exists'      => 'Kindly sign up!',
                'followed_id.different'   => 'You cannot follow yourself!'
            ]
        );

    }


    public function index()
    {
        $users = User::all();

        return response()->json(
            [
                'success' => true,
                'message' => 'Found ' . count($users) . ' users',
                'data'    => UserResource::collection($users),
            ], 200
        );

    }


}