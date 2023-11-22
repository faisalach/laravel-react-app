<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vehicles;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
	protected $duration_jwt     = 60 * 24 * 7;

	public function register(Request $request) {
		$request->validate([
			'username' => 'required|unique:users,username',
			'email' 	=> 'required|unique:users,email',
			'password' => 'required|min:8',
		]);

		$user    = User::create([
			"username"  => $request->input("username"),
			"email"  	=> $request->input("email"),
			"password"  => Hash::make($request->input("password")),
		]);

		if($user){

			$vehicle    = Vehicles::create([
				'user_id' => $user->id,
				'vehicle_brand' => "Default",
				'vehicle_model' => "Vehicle 1",
				'number_plate' => "X XXXX XXX",
			]);

			$auth       = Auth::attempt(['username' => $request->input("username"), 'password' => $request->input("password")]);        
			if(!$auth){
				return response([
					"message" => "Incorrect username and password"
				],Response::HTTP_UNAUTHORIZED);
			}
	
			$user   = Auth::user();
	
			$token  = $user->createToken("token")->plainTextToken;
	
			$cookie     = cookie("jwt",$token,$this->duration_jwt);
	
			return response([
				"message" => "Successfuly register",
				"user"  => $user,
				"token" 	=> $token
			])->withCookie($cookie);

		}else{
			return response([
				"message" => "error",
			],422);
		}
	}
	public function login(Request $request) {
		$request->validate([
			'username' => 'required',
			'password' => 'required',
		]);
		
		$username   = $request->input("username");
		$password   = $request->input("password");
		$auth       = Auth::attempt(['username' => $username, 'password' => $password]);
		if(!$auth){
			$auth       = Auth::attempt(['email' => $username, 'password' => $password]);
			if(!$auth){
				return response([
					"message" => "Incorrect username and password"
				],Response::HTTP_UNAUTHORIZED);
			}
		}

		$user   = Auth::user();

		$token  = $user->createToken("token")->plainTextToken;

		$cookie     = cookie("jwt",$token,$this->duration_jwt);

		return response([
			"message" => "Successfuly login",
			"user"  => $user
		])->withCookie($cookie);
	}
	public function user() {
		return Auth::user();
	}

	public function logout(){
		$cookie     = Cookie::forget("jwt");

		return response([
			"message" => "Successfuly logout",
		])->withCookie($cookie);
	}

	public function forgot_password(Request $request){
		$request->validate(['email' => 'required|email']);
		$status = Password::sendResetLink(
			$request->only('email')
		);
		if($status === Password::RESET_LINK_SENT){
			return response([
				"message" => "Successfuly send token",
			]);
		}else{
			return response([
				"message" => "Failed, try again in a few moments",
			],422);
		}
	}

	public function reset_password(Request $request){
		$request->validate([
			'token' => 'required',
			'email' => 'required|email',
			'password' => 'required|min:8|confirmed',
		]);
	 
		$status = Password::reset(
			$request->only('email', 'password', 'password_confirmation', 'token'),
			function (User $user, string $password) {
				$user->forceFill([
					'password' => Hash::make($password)
				])->setRememberToken(\Str::random(60));
	 
				$user->save();
	 
				event(new PasswordReset($user));
			}
		);

		if($status === Password::PASSWORD_RESET){
			return response([
				"message" => "Successfuly reset password",
			]);
		}else{
			return response([
				"message" => "Failed, Please try again",
			],422);
		}
	 
	}
}
