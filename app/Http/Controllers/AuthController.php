<?php

namespace App\Http\Controllers;

use App\ad_image;
use App\citie;
//use Illuminate\Auth\Access\Gate;
use App\Events\ParsePusherEvent;
use App\Jobs\Parsing;
use App\new_ad;
use App\new_ad_image;
use Illuminate\Support\Facades\Storage;
use JWTAuth;
use Psy\Util\Json;
use Pusher\Laravel\Facades\Pusher;
use Sse\SSE;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;

use Gate;
use App\users_avatar;
use Sujip\Guid\Guid;
use Illuminate\Filesystem\Filesystem;
use Timer;
use App\Modules\ParserLogic;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\ad;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt.auth', ['except' => ['login']]);
    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            if ($token = $this->guard()->attempt($credentials/*['email'=>$request->email,'password'=>$request->password/*, 'role_code'=>3]*/)){
               if($this->guard()->user()->hasRole('user')) {
                   //$this->guard()->user()->assignRole('admin');
                   return response()->json(compact('token'));
               }
                //return $this->respondWithToken($token);

                // if no errors are encountered we can return a JWT
                else return response()->json(['error' => 'you do not have access rights'], 401);
            }else{
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        }
        catch (JWTException $e) {
            // something went wrong
            return response()->json(['error' => 'could not create token'], 500);
        }
      //  return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json($this->guard()->user());
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }


    /**
     * Get all Users list.
     * @return Json
     * @param Request
     */
    public function getUsersList(Request $request){
        $user = new User;
        try {
            if(Gate::denies('getUsersInfo', $user))
              response()->json(['error' => 'You have no rights.'], 505);
            //$user = $this->guard()->user();
            return response()->json(['users' => User::all()]);
        }catch (\Exception $exception){
            return response()->json(['error_text' => $exception], 507);
        }
    }

    /**
     * Get all Aps.
     * @param Request
     * @return Json
     */
    public function getAps(Request $request){
        try {
            //Storage::get('file.jpg');
            $data = ad::all();

            if($this->guard()->user()->hasRole('user') || $this->guard()->user()->hasRole('admin') ||
                $this->guard()->user()->hasPermissionTo('Look ads list')) {
                return response()->json(['ads' => $data/*, 'images' => _ad_image::all()*/]);
            }
            return response()->json(['error'=>'You have no rights.'], 505);
        }catch (\Exception $exception){
            return response()->json(['error'=>$exception->getMessage()],507);
        }
    }

    public function getApPhotos(Request $request){
        try{
          //  $ad = _ad_image::where('ad_id',$request->id)->get();
           // $add = ad::find($request->id)->Images;
            $ad = ad::find($request->id);
            if($this->guard()->user()->hasRole('user') || $this->guard()->user()->hasRole('admin')) {
                if (!empty($ad)) {
                    try {
                        return response()->json(['ad' => $ad, 'images' => $ad->allImg, 'id' => $request->id]);
                    }catch (\Exception $exception1){
                        return response()->json(['ad' => $ad, 'images' => null, 'error'=>$exception1->getMessage()]);
                    }
                }
                else
                    return response()->json(['message' => 'Images absent.']);
            }
            else
                return response()->json(['message'=>'You have no rights.']);
        }catch (\Exception $exception){
            return response()->json(['error'=>$exception->getMessage()], 510);
        }
    }

    /**
     * Delete City.
     * @param Request
     * @return Json
     */
    public function deleteCityAction(Request $request){
        if(Gate::allows('deleteCity',$this->guard()->user())) {

            return response()->json(['']);
        }
    }

    /**
     * Method for upload photo on server.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadPhoto(Request $request)
    {
        try {
            //$this->guard()->user()->assignRole('admin');
            if($this->guard()->user()->hasRole('admin')||$this->guard()->user()->hasRole('user')) {
                if (count($request->file('avatar')) > 0) {
                    $files = $request->file('avatar');
                    $user = users_avatar::where('user_id', '=', $this->guard()->id())->first();
                    if ((!Storage::exists('public/avatars/' . $request->userName . '.png')) &&
                        (empty($user))) {
                        $part = 1;
                        $photo = new users_avatar;
                        $photo->photo_name = $request->userName . '.'.$request->type;
                        $photo->url = 'storage/avatars/' . $request->userName . '.'.$request->type;
                        $photo->user_id = $this->guard()->id();
                        $photo->save();

                        $filePath = storage_path('app/public/avatars');
                        $dir = new Filesystem;
                        if(!$dir->exists($filePath)){
                            $dir->makeDirectory($filePath);  //follow the declaration to see the complete signature
                        }
                        $ppath = $request->file('avatar')->storeAs(
                            'public/timeImg', $request->userName.'.'.$request->type
                        );
                        $this->cropImage($files->getRealPath(),
                            $filePath.'/'.$request->userName.'.'.$request->type,
                            intval($request->width), intval($request->height));
                        Storage::delete($ppath);

                        /*Storage::put(
                            'public/avatars/' . $request->userName . '.png',
                            file_get_contents($files->getRealPath())
                        );*/

                        //Photo success was saved.
                        return response()->json(['success' => 'file was save.',
                            'name' => $request->userName . '.png']);
                    } else {
                        $part = 2;
                        if(Storage::exists('public/avatars/' . $user->photo_name))
                            Storage::delete('public/avatars/' . $user->photo_name);
                        $user->photo_name = $request->userName.'.'.$request->type;
                        $user->url = 'storage/avatars/'.$request->userName.'.'.$request->type;
                        $user->user_id = $this->guard()->id();
                        $user->save();


                        $filePath = storage_path('app/public/avatars');
                        $dir = new Filesystem;
                        if(!$dir->exists($filePath)){
                            $dir->makeDirectory($filePath);  //follow the declaration to see the complete signature
                        }
                        $ppath = $request->file('avatar')->storeAs(
                            'public/timeImg', $request->userName.'.'.$request->type
                        );
                        $this->cropImage($files->getRealPath(),
                            $filePath.'/'.$request->userName.'.'.$request->type,
                            intval($request->width), intval($request->height));
                        Storage::delete($ppath);

                        // When avatar of user exist.
                        return response()->json(['success' => 'file always exist.', 'name' => $user->photo_name]);
                    }
                    // Now you have your file in a variable that you can do things with
                    return response()->json(['message' => 'file wasn\'t save.',
                        'rez' => Storage::exists('public/avatars/Macc' . '.png')]);
                }
                return response()->json(['message' => 'files absent.']);
            }
            else
                return response()->json(['message' => 'You have no rights.']);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage(), 'path'=>$ppath, 'part' => $part]);
        }
    }

    /**
     * Method for control Avatar of user.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserAva(Request $request){
        try{
            $user = users_avatar::where('user_id', '=', $this->guard()->id())->first();
            if(!empty($user)){
                    return response()->json(['photo' => $user->url,'info' => '']);
            }
            return response()->json(['photo' => '','info' => false]);
        }catch (\Exception $exception){
            return response()->json(['error' => $exception->getMessage()]);
        }
    }

    /**
     * Load All User permissions.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loadPermissions(Request $request){
        try{
            if($this->guard()->user()->hasRole('user') || $this->guard()->user()->hasRole('admin')) {
                // Все разрешения, применяемые к пользователю (унаследованные и прямые)
                //$user->getAllPermissions ();
                $permissionsList = [];
                foreach ($this->guard()->user()->getAllPermissions() as $permission)
                    array_push($permissionsList, $permission->name);
                return response()->json($permissionsList);
            }
            else{
                return response()->json(['message' => 'You have no rights.']);
            }
        }catch (\Exception $exception){
            return response()->json(['error' => $exception->getMessage()], 520);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadAdImages(Request $request){
        try{
            if($this->guard()->user()->hasRole('admin')||$this->guard()->user()->hasRole('user')) {
                if (!empty($request->ad_id) && !empty($request->size) &&
                    (intval($request->size) > 0) && (intval($request->ad_id)>0)) {
                    $filePath = storage_path('app/public/images');
                    $dir = new Filesystem;
                    if(!$dir->exists($filePath)){
                        $dir->makeDirectory($filePath);  //follow the declaration to see the complete signature
                    }
                    for($i=0; $i<$request->size; $i++) {
                        $path = $request->file('AdImages' . $i)->store('public/timeImg');
                        $arr = explode('/',$path);
                        $newPath = guid();
                        $this->cropImage($request->file('AdImages'.$i)->getRealPath(),
                            $filePath.'/'.$newPath.'.'.pathinfo($path)['extension'],
                            intval($request->width), intval($request->height));
                        Storage::delete($path);
                        ad_image::create(['img_name' => $newPath.'.'.pathinfo($path)['extension'],
                            'img_path' => 'storage/images/'.$newPath.'.'.pathinfo($path)['extension'],
                            'ad_id' => intval($request->ad_id)]);
                    }
                    return response()->json(['success' => 'Files was saved.'.$request->ad_id]);
                }else{
                    return response()->json(['message' => 'Files absent.']);
                }
            }
        }catch (\Exception $exception){
            return response()->json(['error' => $exception->getMessage()], 517);
        }
    }

    /**
     * Метод для обработки полученного изображения (масштабирования или обрезания),
     * сохраняет прозрачный фон для .png формата.
     * @param $aInitialImageFilePath
     * @param $aNewImageFilePath
     * @param $aNewImageWidth
     * @param $aNewImageHeight
     * @return bool
     */
    protected function cropImage($aInitialImageFilePath, $aNewImageFilePath, $aNewImageWidth, $aNewImageHeight){
        if (($aNewImageWidth < 0) || ($aNewImageHeight < 0)) {
            return false;
        }

        // Массив с поддерживаемыми типами изображений
        $lAllowedExtensions = array(1 => "gif", 2 => "jpeg", 3 => "png");

        // Получаем размеры и тип изображения в виде числа
        list($lInitialImageWidth, $lInitialImageHeight, $lImageExtensionId) = getimagesize($aInitialImageFilePath);

        if (!array_key_exists($lImageExtensionId, $lAllowedExtensions)) {
            return false;
        }
        $lImageExtension = $lAllowedExtensions[$lImageExtensionId];

        // Получаем название функции, соответствующую типу, для создания изображения
        $func = 'imagecreatefrom' . $lImageExtension;
        // Создаём дескриптор исходного изображения
        $lInitialImageDescriptor = $func($aInitialImageFilePath);

        // Определяем отображаемую область
        $lCroppedImageWidth = 0;
        $lCroppedImageHeight = 0;
        $lInitialImageCroppingX = 0;
        $lInitialImageCroppingY = 0;
        if ($aNewImageWidth / $aNewImageHeight > $lInitialImageWidth / $lInitialImageHeight) {
            $lCroppedImageWidth = floor($lInitialImageWidth);
            $lCroppedImageHeight = floor($lInitialImageWidth * $aNewImageHeight / $aNewImageWidth);
            $lInitialImageCroppingY = floor(($lInitialImageHeight - $lCroppedImageHeight) / 2);
        } else {
            $lCroppedImageWidth = floor($lInitialImageHeight * $aNewImageWidth / $aNewImageHeight);
            $lCroppedImageHeight = floor($lInitialImageHeight);
            $lInitialImageCroppingX = floor(($lInitialImageWidth - $lCroppedImageWidth) / 2);
        }

        // Создаём дескриптор для выходного изображения
        $lNewImageDescriptor = imagecreatetruecolor($aNewImageWidth, $aNewImageHeight);

        $transparent = imagecolorallocatealpha($lNewImageDescriptor, 0, 0, 0, 127);
        imagefill($lNewImageDescriptor, 0, 0, $transparent);
        imagesavealpha($lNewImageDescriptor, true); // save alphablending setting (important);

        //обрезание изображения.
       // imagecopyresampled($lNewImageDescriptor, $lInitialImageDescriptor, 0, 0, $lInitialImageCroppingX, $lInitialImageCroppingY, $aNewImageWidth, $aNewImageHeight, $lCroppedImageWidth, $lCroppedImageHeight);
        //изменение масштаба изображения.
        imagecopyresized($lNewImageDescriptor, $lInitialImageDescriptor, 0, 0,
            $lInitialImageCroppingX, $lInitialImageCroppingY,
            $aNewImageWidth, $aNewImageHeight, $lCroppedImageWidth, $lCroppedImageHeight); // собственно само масштабирование
        $func = 'image' . $lImageExtension;

        // сохраняем полученное изображение в указанный файл
        return $func($lNewImageDescriptor, $aNewImageFilePath);
    }

    public function saveAvatarGlobalUrl(Request $request){
        try{
            if($this->guard()->user()->hasRole('admin')||$this->guard()->user()->hasRole('user')) {
                if (!empty($request->url)) {
                    $avatar = users_avatar::where('user_id','=',$this->guard()->id())->first();
                    if(empty($avatar)) {
                        $avatar = new users_avatar;
                    }
                   // $avatar->photo_name = guid();
                    $avatar->url = $request->url;
                    $avatar->user_id = $this->guard()->id();
                    $avatar->save();
                    return response()->json(['success' => 'Path was saved.']);
                }
            }else{
                return response()->json(['message' => 'You have no rights.']);
            }
        }catch (\Exception $exception){
            return response()->json(['error' => $exception->getMessage()],518);
        }
    }

    /**
     * Method for save URL of new ad image.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveAdImgGlobalUrl(Request $request){
        try{
            if($this->guard()->user()->hasRole('admin')||$this->guard()->user()->hasRole('user')) {
                if (!empty($request->url) && !empty($request->ad)) {
                    $ad = new ad_image;
                    $name = pathinfo($request->url)['filename'].'.'.pathinfo($request->url)['extension'];
                    $ad->create(['img_name'=>$name, 'img_path' => $request->url, 'ad_id' => $request->ad]);
                   /* $ad->img_name = $name;
                    $ad->img_path = $request->url;
                    $ad->ad_id = $request->ad;
                    $ad->save();*/
                    return response()->json(['success' => 'Link was saved.','file'=>$name]);
                } else {
                    return response()->json(['message' => 'Incomplete data.']);
                }
            }
            else{
                return response()->json(['message' => 'You have no rights.']);
            }
        }catch (\Exception $exception){
            return response()->json(['error' => $exception->getMessage()],519);
        }
    }

    public function deleteAdImage(Request $request){
        try{
            if($this->guard()->user()->hasRole('admin')||$this->guard()->user()->hasRole('user')) {
                if(!empty($request->id)) {
                    $image = ad_image::find($request->id);
                    if(!empty($image)){
                        $filePath = storage_path('app/public/images');
                        if(Storage::exists('public/images/'.$image->img_name))
                            Storage::delete($filePath.'/'.$image->img_name);
                        $name = $image->img_name;
                        $image->delete();
                        return response()->json(['success' => $name.' was deleted.']);
                    }else
                        return response()->json(['message' => $image->img_name.' wasn\'t deleted.']);
                }
                else
                    return response()->json(['message' => 'Incomplete data.']);
            }
            return response()->json(['message' => 'You have no rights.']);
        }catch (\Exception $exception){
            return response()->json(['error' => $exception->getMessage()],500);
        }
    }

    public function parseStart(Request $request){
        /*
        if(!empty($request->activate)){
            Timer::timerStart('timer-name');
          //  Timer::timerStop('timer-name');
            $let = 0;
            while($let<10000)
                $let = Timer::timerRead('timer-name');
            Timer::timerStop('timer-name');
            return response()->json(['result'=>empty($let)]);
        }
        */
        try{
            if($this->guard()->user()->hasRole('admin')||$this->guard()->user()->hasRole('user')) {
                $link = 'https://www.olx.ua/obyavlenie/prodam-3-iz-kvartiru-saltovka-IDz4Yvy.html#f3b99f3961;promoted';
                $link2 = 'https://www.olx.ua/obyavlenie/prodam-svoe-pomeschenie-pod-supermarketmzhk-internatsionalist-IDxEzuS.html#e6af7e745e';
                $parser = new ParserLogic();
                $region = 'pol/'; $page_size = 1;
           //     $region = (!empty($request->region))?$request->region:'kha/';
           //     $page_size = (!empty($request->number_of_pages))?$request->number_of_pages:1;
                dispatch(new Parsing($region, $page_size))
                    ->onQueue('parser')
                    ->delay(15);

             //   $links = $parser->ParseCategory($region, $page_size);
              //  $proxy = $parser->parseProxy();

            //    $result = $parser->GetAllLinks($region, $page_size);
           //     $result1 = $parser->StartParsePage($result);
            //    $parser->save_image('http://poradu.pp.ua/uploads/posts/2015-03/scho-take-ram-v-kompyuter-telefon_923.jpeg',
            //        storage_path('app/public/testing').'/yesy5.jpeg', '173.212.202.65:80');

                  //$proxy = $parser->proxy_two();

                return response()->json(['result' => 0/*$result*/], 200);
            }
        }catch (\Exception $exception){
            return response()->json(['error'=>$exception->getTrace()]);
        }
    }

    public function getAdById(Request $request){
        try{
            if($this->guard()->user()->hasRole('admin')||$this->guard()->user()->hasRole('user')) {
                if(!empty($request->id)){
                    $ad = ad::find($request->id);
                    if(!empty($ad)){
                        return response()->json(['element' => $ad]);
                    }
                }else
                    return response()->json(['message' => 'Incomplete data.']);
            }else{
                return response()->json(['message' => 'You have no rights.']);
            }
        }catch (\Exception $exception){
            return response()->json(['error' => $exception->getMessage()],500);
        }
    }

    public function ParseProxyService(Request $request){
        try{
            $parser = new ParserLogic();
            $proxy = $parser->proxy_two();
            return response()->json(['result' => $proxy], 200);
        }catch (\Exception $exception){
            return response()->json(['error'=>$exception->getMessage()],503);
        }
    }

}


/*
 php artisan queue:work --daemon --queue=parser --tries=1
*/