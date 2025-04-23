<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class ProductImageController extends Controller
{
    public function update(Request $request)
    {
        // 1. Validate
        $request->validate([
            'image'      => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'product_id' => 'required|exists:products,id',
        ]);

        // 2. Lấy file và tạo tên duy nhất
        $file      = $request->file('image');
        $ext       = $file->getClientOriginalExtension();
        $imageName = time() . '-' . uniqid() . '.' . $ext;

        // 3. Move vào thư mục public/uploads/product/original
        $destination = public_path('uploads/product/original');
        if (!\File::exists($destination)) {
            \File::makeDirectory($destination, 0755, true);
        }
        $file->move($destination, $imageName);

        // 4. Lưu vào database
        $productImage = ProductImage::create([
            'product_id' => $request->product_id,
            'image'      => $imageName,
        ]);

        // 5. Trả về JSON cho Dropzone
        return response()->json([
            'status'    => true,
            'image_id'  => $productImage->id,
            'ImagePath' => asset("uploads/product/original/{$imageName}"),
            'message'   => 'Image uploaded successfully (no resize)',
        ]);
    }
    public function storeTemp(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id', // Kiểm tra product_id
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);
    
        // Lấy ảnh từ request
        $imageFile = $request->file('image');
        $imageName = time() . '-' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
    
        // Di chuyển ảnh vào thư mục tạm
        $imageFile->move(public_path('uploads/temp'), $imageName);
    
        // Tạo bản ghi ProductImage
        $productImage = ProductImage::create([
            'product_id' => $request->product_id,  // Lưu product_id vào bảng product_images
            'image' => $imageName
        ]);
    
        return response()->json([
            'status' => true,
            'image_id' => $productImage->id,
            'ImagePath' => asset("uploads/temp/{$imageName}"), // Trả về đường dẫn ảnh
            'message' => 'Image uploaded successfully'
        ]);
}
public function destroy(Request $request)
{
    $pi = ProductImage::find($request->id);
    if (!$pi) {
        return response()->json(['status'=>false,'message'=>'Image not found']);
    }
    // Xóa file nếu tồn tại
    $path = public_path('uploads/product/original/' . $pi->image);
    if (\File::exists($path)) {
        \File::delete($path);
    }
    $pi->delete();
    return response()->json(['status'=>true,'message'=>'Image deleted']);
}
}
