<?php

namespace App\Http\Controllers\api;

use App\Models\Cars;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
class CarsController extends Controller
{
    //add
    public function add(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'img' => 'required|mimes:jpg,bmp,png,svg,ico,jpeg,gif',
            'description' => 'required',
            'price' => 'required'
        ]);
        $cars = new Cars();

        if ($validator->fails()) {
            return response()->json(['xatolik' => 'malumot turida xatolik bor','message' => $validator->messages()]);
        }else{
            if ($request->hasFile('img')) {
                $file = $request->file('img');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $file->move('cars/', $fileName);
                $cars->img = "cars/" . $fileName;
            }
            $cars->name = $request->input('name');
            $cars->description = $request->input('description');
            $cars->price = $request->input('price');
            $cars->save();
            return response()->json([
                'status'=>200,
                'xabar'=>'Cars add!',
                'xatolik' => 'Cars add!',
                'cars' => $cars
            ]);

        }
    }
    // get
    public function get(Request $request, $id=false){
        if($id){
            $cars = Cars::find($id);
            return response()->json([
               'status'=>200,
               'cars'=>$cars
            ]);
        }else{
            $cars = Cars::all();
            return response()->json([
                'status'=>200,
                'cars'=>$cars
            ]);
        }

    }
    // edit
    public function edit(Request $request,$id){
        $cars = Cars::find($id);
        $path = $cars->img;
            if ($request->hasFile('img')) {
                if (File::exists($path)) {
                    File::delete($path);
                }
                $file = $request->file('img');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $file->move('cars/', $fileName);
                $cars->img = "cars/" . $fileName;
            }

            $cars->name = $request->input('name') ?? $cars->name;
            $cars->description = $request->input('description')  ?? $cars->description ;
            $cars->price = $request->input('price') ?? $cars->price;
            $cars->save();
            return response()->json([
                'status'=>200,
                'xabar'=>'Cars Edit!',
                'cars'=>$cars
            ]);

    }
    // delete
    public function delete($id){
        $cars = Cars::find($id);
        if($cars){
            $cars->delete();
            $path = $cars->img;
            if (File::exists($path)) {
                File::delete($path);
            }
            return response()->json([
                'status'=>200,
                'xabar'=>'Cars delete!'
            ]);
        }else{
            return response()->json([
                'status'=>404,
                'xabar'=>'Cars not found!'
            ]);
        }
    }
}
