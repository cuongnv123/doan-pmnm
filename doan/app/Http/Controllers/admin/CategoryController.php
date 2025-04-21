<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;



class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::latest();

        if (!empty($request->get('keyword'))) {
            $categories = $categories->where('name', 'like', '%' . $request->get('keyword') . '%');
        }
        $categories = $categories->paginate(10);

        return view('admin.category.list', compact('categories'));
    }

    public function create()
    {
        return view('admin.category.create');
    }

    public function store(Request $request)
    {
        // Validate input
    $validator = Validator::make($request->all(), [
        'name' => 'required',
        'slug' => 'required|unique:categories',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ]);
    }

    try {
        
       $category = new Category();
        $category->name = $request->name;
        $category->slug = $request->slug;
        $category->status = $request->status;
        $category->showHome = $request->showHome;
        
        if ($request->has('image_id') && $request->image_id != '') {
            $tempImage = TempImage::find($request->image_id);
            if ($tempImage) {
                $imageName = $tempImage->name;
                // Di chuyển file từ temp -> thư mục chính
                File::move(public_path('uploads/temp/' . $imageName), public_path('uploads/category/' . $imageName));
                File::copy(public_path('uploads/category/' . $imageName), public_path('uploads/category/thumb/' . $imageName));
                $category->image = $imageName;
    
                // Xoá ảnh tạm (nếu cần)
                $tempImage->delete();
            }
        }
        
       
        $category->save();

        Session::flash('success', 'Category added successfully!');

        return response()->json([
            'status' => true,
            'message' => 'Category added successfully'
        ]);
    } catch (\Exception $e) {
        \Log::error('Category Store Error: ' . $e->getMessage());
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong! ' . $e->getMessage()
        ], 500);
    }
    }

    public function edit($categoryId, Request $request)
    {
        $category = Category::find($categoryId);
        if (empty($category)) {
            return redirect()->route('categories.index');
        }

        return view('admin.category.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['status' => false, 'notFound' => true]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $id,
            'status' => 'required|in:0,1',
            'showHome' => 'required|in:0,1',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    
        // Cập nhật ảnh nếu có image_id mới từ Dropzone
        if ($request->has('image_id') && $request->image_id != '') {
            $tempImage = TempImage::find($request->image_id);
            if ($tempImage) {
                $imageName = $tempImage->name;
                // Di chuyển file từ temp -> thư mục chính
                File::move(public_path('uploads/temp/' . $imageName), public_path('uploads/category/' . $imageName));
                File::copy(public_path('uploads/category/' . $imageName), public_path('uploads/category/thumb/' . $imageName));
                $category->image = $imageName;
    
                // Xoá ảnh tạm (nếu cần)
                $tempImage->delete();
            }
        }
    
        $category->name = $request->name;
        $category->slug = $request->slug;
        $category->status = $request->status;
        $category->showHome = $request->showHome;
        $category->save();
    
        return response()->json(['status' => true]);
    }
    public function destroy($categoryId, Request $request)
    {
        $category = Category::find($categoryId);
        if (empty($category)) {
            Session::flash('error', 'Category not found!');

            return response()->json([
                'status' => true,
                'message' => 'Category not found'
            ]);
            //return redirect()->route('categories.index');
        }

        File::delete(public_path() . '/uploads/category/thumb' . $category->image);
        File::delete(public_path() . '/uploads/category/' . $category->image);
        $category->delete();
        Session::flash('success', 'Category deleted successfully!');

        return response()->json([
            'status' => true,
            'message' => 'Category deleted succesfully'
        ]);
    }
}