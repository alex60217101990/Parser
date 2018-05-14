<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

use App\Http\Requests;
use App\User;
use Illuminate\Support\Facades\Hash;

class TokenAuthController extends Controller
{
    /**
     * Method for User authenticate.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        // if no errors are encountered we can return a JWT
        return response()->json(compact('token'));
    }

    /**
     * Method will provide us with information
     * about the registered user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuthenticatedUser()
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

        return response()->json(compact('user'));
    }

    /**
     * Method for register new User.
     *
     * @param Request $request
     * @return mixed
     */
    public function register(Request $request){
        try {
            //   $request = json_decode($r);
            //  $password=Hash::make($request->input('password'));
            $password = Hash::make($request->password);
            $newuser['password'] = $password;

            $user = new User;
            $user->fill(['name' => $request->login, 'email' => $request->email, 'password' => Hash::make($request->password)]);
            $user->save();
            return json_encode(['result' => true]);
        }catch (\Exception $exception){
            return json_encode(['result' =>$exception->getMessage()]);
        }
    }

}
