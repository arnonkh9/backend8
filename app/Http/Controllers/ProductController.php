<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Image;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Read all products
        // return Product::all();
        // อ่านข้อมูลแบบแบ่งหน้า
        // return Product::orderBy('id','desc')->paginate(25);
        return Product::with('users','users')->orderBy('id','desc')->paginate(5);
    }

    public function store(Request $request)
    {
        // เช็คสิทธิ์ (role) ว่าเป็น admin (1)
        $user = auth()->user();

        if($user->tokenCan("1")){

            // Validate form
            $request->validate([
                'name' => 'required|min:3',
                'slug' => 'required',
                'price' => 'required'
            ]);

            // กำหนดตัวแปรรับค่าจากฟอร์ม
            $data_product = array(
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'slug' => $request->input('slug'),
                'price' => $request->input('price'),
                'user_id' => $user->id
            );

            // รับไฟล์ภาพเข้ามา
            $image = $request->file('file');

            // เช็คว่าผู้ใช้มีการอัพโหลดภาพเข้ามาหรือไม่
            if(!empty($image)){

                // อัพโหลดรูปภาพ
                // เปลี่ยนชื่อรูปที่ได้
                $file_name = "product_".time().".".$image->getClientOriginalExtension();

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
                $data_product['image'] = url('/').'/images/products/thumbnail/'.$file_name;

            }else{
                $data_product['image'] = url('/').'/images/products/thumbnail/no_img.jpg';
            }

            // Create data to tabale product
            return Product::create($data_product);

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
        return Product::find($id);
    }

    public function update(Request $request, $id)
    {
        // เช็คสิทธิ์ (role) ว่าเป็น admin (1)
        $user = auth()->user();

        if($user->tokenCan("1")){

            $request->validate([
                'name' => 'required',
                'slug' => 'required',
                'price' => 'required'
            ]);

            $data_product = array(
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'slug' => $request->input('slug'),
                'price' => $request->input('price'),
                'user_id' => $user->id
            );

            // รับภาพเข้ามา
            $image = $request->file('file');

            if (!empty($image)) {

                $file_name = "product_" . time() . "." . $image->getClientOriginalExtension();

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

                $data_product['image'] = url('/').'/images/products/thumbnail/'.$file_name;

            }

            $product = Product::find($id);
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
            return Product::destroy($id);
        }else{
            return [
                'status' => 'Permission denied to create'
            ];
        }

    }

    public function search($keyword)
    {
        return Product::with('users','users')
                        ->where('name','like','%'.$keyword.'%')
                        ->orderBy('id','desc')
                        ->paginate(10);
        // SELECT * FROM products INNER JOIN users
        // ON (products.user_id=users.id)
        // WHERE products.name like '%sam%'
        // ORDER BY products.id DESC LIMIT 0,25
    }

}
