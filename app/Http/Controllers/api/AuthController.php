<?php

namespace App\Http\Controllers\api;

use App\Mail\SignUp;
use App\Models\User;
use App\Mail\SendMail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //
    public function register(Request $request){
        $validationMessages = array('mail_uz'=>'mail_uz',"mail_ru"=>'mail_ru',"mail_en"=>'mail_en');
        $validator = Validator::make($request->all(), [
            "last_name" => "required|min:0|max:10000",
            "first_name" => "required",
            "email" => "required|email",
            "phone" => "required",
            "login" => "required",
            "password" => "required",
            "adress" => "required",
        ],[
            "last_name.min" => "Lastname ",
            "last_name.max" => $validationMessages["mail_".$request->headers->get('lang')] ,
            "first_name.require" => "First Name is required",
            "email.require" => "Email is required",
            "phone.require" => "Phone is required",
        ]);
        $userEmail = User::where(['email' => $request->input("email")])->get() ;
        if ($validator->fails()) {
            return response()->json(["xatolik" => "malumot turida xatolik bor", "status" => 200,'message' => $validator->messages()]);
        }else{
            if(count($userEmail)!==0){
             return response()->json(["xatolik" => "malumot turida xatolik bor", "status" => 200,'message' => array('email' => "ushbu emailda oldin ro'yhatda o;tilgan "),'email'=>$userEmail]);
            }else{
                $user = new User();
                $user->last_name =$request->input("last_name");
                $user->first_name = $request->input("first_name");
                $user->email = $request->input("email");
                $user->phone = $request->input("phone");
                $user->login = $request->input("login");
                $user->adress = $request->input("adress");
                $user->valute ='';
                $user->type =false;
                $user->auth =false;
                $user->img ='users/users.svg';
                $user->password = Hash::make($request->input("password"));
                $user->save();
                return response()->json([
                    "status"=>200,
                    "user" =>$user
                           ]);
            }
            // if($userEmail){
            // }


        }
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required",
            "password" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json(['xatolik' => 'malumot turida xatolik bor','message' => $validator->messages()] );
        } else {
            $user = User::Where("email", $request->email)->first();
            $passwords = Hash::check($request->password,$user->password) ?? false;
            if(!$user || !$passwords ){
                return response()->json(
                    ['xabar'=>'siz royhatdan otmagansiz!','status'=>401]);
            }else{
                $token =$user->createToken($user->email.'_Token')->plainTextToken;
                return response()->json(
                    ['xabar'=>'ok','status'=>200,'token'=>$token,'user'=>$user,
                    'lock'=>$user->lock,
                    ]
                );
            }
        }
    }
    public function user(Request $request)
    {
        $user = Auth::user();
        return $user;
    }
     public function emailAuth(Request $request)
     {
         $userId = Auth::user()->id;
         if($userId){
            $user = User::find($userId);
            $user->valute = $request->input("valute") ?? '';
            $user->lock = $request->input("lock") ?? '';
            $user->auth = $request->input("auth") ?? false;
            $user->save();
            return response()->json([
                "status"=>200,
                'lock'=>$user->lock,
                "user"=>$user]);
         }
    }
     public function lock(Request $request)
     {
         $userId = Auth::user()->id;
         if($userId){
            $user = User::find($userId);
            $user->lock = $request->input("lock") ?? '';
            $user->save();
            return response()->json([
                "status"=>200,
                'lock'=>$user->lock,
                "user"=>$user]);
         }
    }

    public function send($mail_data){
        $detil = [
            'title'=>"sdasjkldjsad",
            "body" => "sadasdsad12331"
        ];
        // \Mail::to('tursunboyevabdurasuldevolop@gmail.com')->send(new sendEmail($detil));
    }
    public function sendEmail(){
        // $email = Auth::user()->email;
        $name = 'tursunboyevabdurasuldevolop@gmail.com';
        Mail::to($name)->send(new SignUp($name));
    }

    public function logout(Request $request)
    {
        // auth()->user()->tokens()->delete();
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'status'=>200,
            'massage'=>'Profilgan chiqildi'
        ]);
    }

    // profile edit
    public function useredit(Request $request){
        $id = Auth::user()->id;
        $user = User::find($id);
        $user->last_name =$request->input("last_name") ?? $user->last_name;
        $user->first_name = $request->input("first_name") ?? $user->first_name  ;
        $user->email = $request->input("email") ?? $user->email ;
        $user->phone = $request->input("phone") ?? $user->phone;
        $user->login = $request->input("login") ?? $user->login;
        $user->adress = $request->input("adress") ?? $user->adress;
        if ($request->hasFile('img')) {
            $file = $request->file('img');
            $ext = $file->getClientOriginalExtension();
            $fileName = time() . '.' . $ext;
            $file->move('users/', $fileName);
            $user->img = "users/" . $fileName;
        }
        $user->save();
        return response()->json([
            "status"=>200,
            "user" =>$user
                   ]);
    }
    public function passwordChange(Request $request){
    $id = Auth::user()->id;
    $user = User::find($id);
    $validator = Validator::make($request->all(), [
        "old_password" => "required|min:8",
        "new_password" => "required|min:8",
        "con_new_password" => "required|min:8",
    ]);
    if ($validator->fails()) {
        return response()->json(["xatolik" => "malumot turida xatolik bor", "status" => 200, 'message' => $validator->messages()]);
    } else {
        $oldpas = $request->new_password===$request->con_new_password ? $request->old_password : '';
        $passwords = Hash::check($oldpas,$user->password) ?? false;
        if(!$passwords){
            return response()->json(["xatolik" => "malumot turida xatolik bor", "status" => 400,'message' => "Parol noto'g'ri kiritilgan $oldpas" ]);
        }else{
            $user->password = Hash::make($request->input("new_password")) ;
            $user->save();
            return response()->json([
                "status"=>200,
                "user" =>$user
                       ]);
        }

    }
}
}
