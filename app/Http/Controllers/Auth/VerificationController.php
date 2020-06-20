<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

  //  use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    // protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
      //  $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    public function verify(Request $request, User $user)
    {

        //Check if the url is a valid signed url

        if(! URL::hasValidSignature($request)) {

            return response()->json(['error' => [

                'message' => "Invalid Verification Link"
            ]],422);
        }

        //Check if User has already verified their account

        if($user->hasVerifiedEmail()) {

            return response()->json(['error' => [

                'message' => "Email address already verified"
            ]],422);
        }

        $user->markEmailAsVerified();

        event(new Verified($user));

        return response()->json(['message' => 'Email successfully verified'],200);

    }

    public function  resend(Request $request)
    {

        $this->validate($request,[

            'email' => ['email','required']
        ]);

        $user = User::where('email',$request->email)->first();

        if(!$user) {

            return response()->json(["errors" =>
            ["email" => "No user could be found with this email address"]

        ],422);
        }

        if($user->hasVerifiedEmail()) {

            return response()->json(['error' => [

                'message' => "Email address already verified"
            ]],422);
        }

        $user->sendEmailVerificationNotification();

        return  response()->json(['status' => "Verification link send"]);


    }
}
