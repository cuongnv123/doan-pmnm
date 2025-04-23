<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ColorController extends Controller
{
    public function index(Request $request)
    {
        $colors = Color::latest('id');

        if ($request->get('keyword')) {
            $colors = $colors->where('name', 'like', '%' . $request->keyword . '%');
        }
        $colors = $colors->paginate(5);
        return view('admin.colors.list', compact('colors'));
    }
    public function create()
    {
        $products = Product::all(); // hoặc paginate nếu danh sách dài
        return view('admin.colors.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'product_id' => 'required',
        ]);
        if ($validator->passes()) {
            $color = new Color();
            $color->product_id = $request->product_id;
            $color->name = $request->name;
            $color->save();

            Session::flash('success', 'Color created successfullly');
            return response()->json([
                'status' => true,
                'message' => 'Color created successfullly',
            ]);
        }
    }
    public function edit($id, Request $request)
    {
        $color = Color::find($id);
        $products = Product::all();
        if (empty($color)) {
            return response()->json([
                'status' => false,
                'message' => 'Color not found',
            ]);
        }
    
        
    
        return view('admin.colors.edit', [
            'color' => $color,
            'products' => $products,
        ]);
    }

    public function update($id, Request $request)
    {
        $color = Color::find($id);

        if (empty($color)) {
            return response()->json([
                'status' => false,
                'message' => 'Color not found',
            ]);
        }
    
        // Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    
        // Cập nhật dữ liệu
        $color->product_id = $request->product_id;
        $color->name = $request->name;
        $color->save();
    
        Session::flash('success', 'Color updated successfully');
    
        return response()->json([
            'status' => true,
            'message' => 'Color updated successfully',
        ]);
    }


    public function delete($id, Request $request)
    {
        $colors = Color::find($id);

        if (empty($colors)) {
            return response()->json([
                'status' => false,
                'message' => 'Color not found',
            ]);
        }
        $colors->delete();
        Session::flash('success', 'Color deleted successfully');
        return response()->json([
            'status' => true,
            'message' => 'Color deleted successfully'
        ]);
    }
}
