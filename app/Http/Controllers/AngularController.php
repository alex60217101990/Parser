<?php

namespace App\Http\Controllers;

use App\new_ad;
use App\User;
use App\users_role;
use File;
//use http\Env\Request;
use Illuminate\Http\Request;
use Psy\Util\Json;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Sse\SSE;

class AngularController extends Controller
{
    /**
     * Serve the angular application.
     *
     * @return \Illuminate\View\View
     */
    public function serve()
    {
       // return File::get(public_path('dist/index.html'));

        return File::get(public_path('dist/index.html'));
    }
    public function ads(Request $request)
    {
        // return File::get(public_path('dist/index.html'));

        return File::get(public_path('dist/index.html'));
    }


    /**
     * Method Post.
     * @return Json
     */
    public function post(Request $request){
        $data = [['1'=>'fmmgbfkbnjgkbgnk', '2'=>1200],['3'=>'jnbjgbngkng','4'=>565156]];
        return json_encode($data);
    }

    /**
     * Register new User.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function newUser(Request $request){
        try {
          //  return json_encode(['register'=>true]);
       //     if ($request->ajax()) {
              //  $data = json_decode($request);
                if (isNonEmptyString($request->login) && isNonEmptyString($request->password) &&
                    isNonEmptyString($request->email)) {
                    $user = new User;
                   // $user_role = new user_role;
                    $user->fill(['name'=>$request->login,'email'=>$request->email,'password'=>Hash::make($request->password)]);
                    $user->save();
                    return json_encode(['register'=>true]);
                }
                return json_encode(['error' => 'Неполные данные', 'code' => 407]);
        //    }
        }catch (\Exception $exception){
            return json_encode(['error' => $exception->getMessage(), 'code' => 405]);
        }
    }


    public function getIcon(Request $request){
        $file_path = public_path() . '\img\\'.'exit.svg';
        return json_encode(['path' => $file_path]);
       // return File::get(public_path('img/exit.svg'));
    }

}
