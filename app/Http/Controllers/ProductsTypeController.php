<?php

namespace App\Http\Controllers;

use App\Models\ProductsType;
use Illuminate\Http\Request;

class ProductsTypeController extends Controller
{

    public function index()
    {
        //return Unit::orderBy('id','asc')->paginate(5);
        return ProductsType::orderBy('id','asc')->paginate(5);
    }

    public function store(Request $request)
    {
         // เช็คสิทธิ์ (role) ว่าเป็น admin (1)
        //$user = auth()->user();

        //if($user->tokenCan("1")){

            // Validate form
            $request->validate([
                'name' => 'required|min:3|unique:products_types'

            ]);

            // กำหนดตัวแปรรับค่าจากฟอร์ม
            $data_unit = array(
                'name' => $request->input('name')
            );

            // Create data to tabale product
            return ProductsType::create($data_unit);

            // return response($data_product, 201);
            // return $data_product$request->all(), 201);
            // return Product::create($request->all());

        //}else{
        //    return [
        //        'status' => 'Permission denied to create'
         //   ];
       // }
    }

    public function show($id)
    {
        return ProductsType::find($id);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required'

        ]);

        $data_unit = array(
            'name' => $request->input('name')

        );


        $unit = ProductsType::find($id);
        $unit->update($data_unit);

        return $unit;
    }

    public function destroy($id)
    {
        return ProductsType::destroy($id);
    }

    public function search($keyword)
    {

        return ProductsType::where('name','like','%'.$keyword.'%')
                        ->orderBy('id','desc')
                        ->paginate(5);

        /*
        return Product::with('users','users')
                        ->where('name','like','%'.$keyword.'%')
                        ->orderBy('id','desc')
                        ->paginate(25);
        */

    }

}
