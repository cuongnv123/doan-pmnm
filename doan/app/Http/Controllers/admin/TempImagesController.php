<?php

namespace App\Http\Controllers\admin;


use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;


class TempImagesController extends Controller
{
    public function create(Request $request)
    {
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $name = uniqid() . '.' . $extension;
    
            $file->move(public_path('uploads/temp'), $name);
    
            // Nếu bạn có bảng temp_images
            $tempImage = new TempImage();
            $tempImage->name = $name;
            $tempImage->save();
    
            return response()->json([
                'status' => true,
                'image_id' => $tempImage->id,
                'file_name' => $name
            ]);
        }
    
        return response()->json([
            'status' => false,
            'message' => 'No image found'
        ], 400);
    }
}
