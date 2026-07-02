<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TempImage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


class TempImageController extends Controller
{
    function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors(),
            ], 400);
        }
        $temp = new TempImage();
        $temp->name = "dump";
        $temp->save();
        $imageName = time() . '.' . $request->image->extension();
        $request->image->move(public_path('uploads/temp'), $imageName);
        $temp->name = $imageName;
        $temp->save();
        $manager = new ImageManager(Driver::class);
        $img = $manager->read(public_path('uploads/temp/' . $imageName));

        
        $thumbDir = public_path('uploads/temp/thumb/');
        if (!file_exists($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }
        $img->coverDown(400, 450);
        $img->save($thumbDir . $imageName);

        return response()->json(['data' => $temp,'message' => 'Image uploaded successfully', 'status' => 200], 200);
    }
}