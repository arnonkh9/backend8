<?php

namespace App\Http\Controllers;

//use App\Models\Product;
use App\Models\Unit;

use Illuminate\Http\Request;
use Image;

class UnitController extends Controller
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
        return Unit::orderBy('id','asc')->paginate(5);
        //return Unit::all();
       // return Product::with('users','users')->orderBy('id','desc')->paginate(25);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // เช็คสิทธิ์ (role) ว่าเป็น admin (1)
        //$user = auth()->user();

        //if($user->tokenCan("1")){

            // Validate form
            $request->validate([
                'name' => 'required|min:3|unique:units',
                'description' => 'required'
            ]);

            // กำหนดตัวแปรรับค่าจากฟอร์ม
            $data_unit = array(
                'name' => $request->input('name'),
                'description' => $request->input('description')
            );

            // Create data to tabale product
            return Unit::create($data_unit);

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
        return Unit::find($id);
    }


    public function update(Request $request, $id)
    {

            $request->validate([
                'name' => 'required',
                'description' => 'required'
            ]);

            $data_unit = array(
                'name' => $request->input('name'),
                'description' => $request->input('description')
            );


            $unit = Unit::find($id);
            $unit->update($data_unit);

            return $unit;

    }


    public function destroy($id)
    {

            return Unit::destroy($id);
    }

    /**
     * Search for a name
     *
     * @param  string $keyword
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */

    public function search($keyword)
    {

        return Unit::where('name','like','%'.$keyword.'%')
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
