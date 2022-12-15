<?php

namespace App\Http\Controllers\api;

use App\Models\User;
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
        $validator = Validator::make($request->all(), [
            "last_name" => "required",
            "first_name" => "required",
            "email" => "required|email|unique:users",
            "phone" => "required",
            "login" => "required|unique:users",
            "password" => "required",
            "adress" => "required",
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
        $user = Auth::user() ?? "not logged in";
        return $user;
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'status'=>200,
            'massage'=>'Profilgan chiqildi'
        ]);
    }
// delete accunt
    public function deleteaccount(Request $request){
    $id = Auth::user()->id;
        $user = Cars::find($id);
        if($user){
            $user->delete();
            $path = $user->img;
            if (File::exists($path)) {
                File::delete($path);
            }
            return response()->json([
                'status'=>200,
                'xabar'=>'Account delete!'
            ]);
        }else{
            return response()->json([
                'status'=>404,
                'xabar'=>'Account not found!'
            ]);
        }
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
            return response()->json(["xatolik" => "malumot turida xatolik bor", "status" => 400,'message' => "Parol noto'g'ri kiritilgan" ]);
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
