<?php

namespace App\Http\Controllers;

use App\Models\User;
//use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    // Register
    public function register(Request $request) {

        // Validate field
        $fields = $request->validate([
            'fullname' => 'required|string',
            'username' => 'required|string',
            'email'=> 'required|string|unique:users,email',
            'password'=>'required|string|confirmed',
            'tel'=>'required',
            'role'=> 'required|integer'
        ]);

        // Create user
        $user = User::create([
            'fullname' => $fields['fullname'],
            'username' => $fields['username'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'tel' => $fields['tel'],
            'avatar' => 'https://via.placeholder.com/400x400.png/005429?text=udses',
            'role' => $fields['role']
        ]);

        // Create token
        $token = $user->createToken($request->userAgent(), ["$user->role"])->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);

    }




    // Login
    public function login(Request $request) {

        // Validate field
        $fields = $request->validate([
            'email'=> 'required|string',
            'password'=>'required|string'
        ]);

        // Check email
        $user = User::where('email', $fields['email'])->first();

        // Check password
        if(!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Invalid login!'
            ], 401);
        }else{

            // ลบ token เก่าออกแล้วค่อยสร้างใหม่
            $user->tokens()->delete();

            // Create token
            $token = $user->createToken($request->userAgent(), ["$user->role"])->plainTextToken;

            $response = [
                'user' => $user,
                'token' => $token
            ];

            return response($response, 201);
        }

    }

    // Logout
    public function logout(Request $request){
        auth()->user()->tokens()->delete();
        return [
            'message' => 'Logged out'
        ];
    }

//-------------------------Edit User

    public function store(Request $request)
    {
        // เช็คสิทธิ์ (role) ว่าเป็น admin (1)
        $user = auth()->user();

        if($user->tokenCan("1")){

            // Validate form
            $request->validate([
                'fullname' => 'required|string',
                'username' => 'required|string',
                'email'=> 'required|string|unique:users,email',
                'password'=>'required|string|confirmed',
                'tel'=>'required',
                'role'=> 'required|integer'
            ]);

            // กำหนดตัวแปรรับค่าจากฟอร์ม
            $data_product = array(
                'fullname' => $request->input('fullname'),
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'password' => bcrypt($request['password']),
                'tel' => $request->input('tel'),
                'role' => $request->input('role')

            );

            // รับไฟล์ภาพเข้ามา
            $image = $request->file('file');

            // เช็คว่าผู้ใช้มีการอัพโหลดภาพเข้ามาหรือไม่
            if(!empty($image)){

                // อัพโหลดรูปภาพ
                // เปลี่ยนชื่อรูปที่ได้
                $file_name = "user_".time().".".$image->getClientOriginalExtension();

                // กำหนดขนาดความกว้าง และสูง ของภาพที่ต้องการย่อขนาด
                $imgWidth = 400;
                $imgHeight = 400;
                $folderupload = public_path('/images/products/thumbnail');
                $path = $folderupload."/".$file_name;

                // อัพโหลดเข้าสู่ folder thumbnail
                $img = Image::make($image->getRealPath());
                $img->orientate()->fit($imgWidth,$imgHeight, function($constraint){
                    $constraint->upsize();
                });
                $img->save($path);

                // อัพโหลดภาพต้นฉบับเข้า folder original
                $destinationPath = public_path('/images/products/original');
                $image->move($destinationPath, $file_name);

                // กำหนด path รูปเพื่อใส่ตารางในฐานข้อมูล
                $data_product['avatar'] = url('/').'/images/products/thumbnail/'.$file_name;

            }else{
                $data_product['avatar'] = url('/').'/images/products/thumbnail/no_img.jpg';
            }

            // Create data to tabale product
        return User::create($data_product);

        // return response($data_product, 201);
        // return $data_product$request->all(), 201);
        // return Product::create($request->all());

        }else{
            return [
                'status' => 'Permission denied to create'
            ];
        }
    }

    public function show($id)
    {
        return User::find($id);
    }

    public function update(Request $request, $id)
    {
        // เช็คสิทธิ์ (role) ว่าเป็น admin (1)
        $user = auth()->user();

        if($user->tokenCan("1")){

            $request->validate([
                'fullname' => 'required',
                 'username' => 'required',
                 'tel' => 'required'
            ]);

            $data_product = array(
                'fullname' => $request->input('fullname'),
                'username' => $request->input('username'),
                 'tel' => $request->input('tel')
               // 'price' => $request->input('price'),
               // 'user_id' => $user->id
            );


            // รับภาพเข้ามา
            $image = $request->file('file');

            if (!empty($image)) {

                $file_name = "user_" . time() . "." . $image->getClientOriginalExtension();

                $imgwidth = 400;
                $imgHeight = 400;
                $folderupload = public_path('/images/products/thumbnail');
                $path = $folderupload . '/' . $file_name;

                // uploade to folder thumbnail
                $img = Image::make($image->getRealPath());
                $img->orientate()->fit($imgwidth, $imgHeight, function ($constraint) {
                    $constraint->upsize();
                });
                $img->save($path);

                // uploade to folder original
                $destinationPath = public_path('/images/products/original');
                $image->move($destinationPath, $file_name);

                $data_product['avatar'] = url('/').'/images/products/thumbnail/'.$file_name;

            }

            $product = User::find($id);
            $product->update($data_product);

            return $product;

        }else{
            return [
                'status' => 'Permission denied to create'
            ];
        }
    }

    public function destroy($id)
    {

        // เช็คสิทธิ์ (role) ว่าเป็น admin (1)
        $user = auth()->user();

        if($user->tokenCan("1")){
            return User::destroy($id);
        }else{
            return [
                'status' => 'Permission denied to create'
            ];
        }
    }


    public function index()
    {
        // Read all products
        // return Product::all();
        // อ่านข้อมูลแบบแบ่งหน้า
        return User::orderBy('id','asc')->paginate(5);
    //return Product::with('users','users')->orderBy('id','desc')->paginate(15);
    }

    public function search($keyword)
    {
        // return User::with('users','users')
        //                 ->where('name','like','%'.$keyword.'%')
        //                 ->orderBy('id','desc')
        //                 ->paginate(10);

        return User::where('fullname','like','%'.$keyword.'%')
                        ->orderBy('id','asc')
                        ->paginate(5);




    }


}
