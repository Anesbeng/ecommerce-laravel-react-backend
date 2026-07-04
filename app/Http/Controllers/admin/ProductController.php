<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductController extends Controller
{
    // ── Save one file to disk, return filename ────────────────────────────
    private function saveImageFile($file): string
    {
        $imageName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        $largeDir = public_path('uploads/product/large/');
        $smallDir = public_path('uploads/product/small/');

        if (!file_exists($largeDir)) mkdir($largeDir, 0755, true);
        if (!file_exists($smallDir)) mkdir($smallDir, 0755, true);

        $file->move($largeDir, $imageName);

        $manager = new ImageManager(Driver::class);
        $img     = $manager->read($largeDir . $imageName);
        $img->coverDown(400, 600);
        $img->save($smallDir . $imageName);

        return $imageName;
    }

    // ── Delete large + small files from disk ─────────────────────────────
    private function deleteImageFiles(string $filename): void
    {
        $large = public_path('uploads/product/large/' . $filename);
        $small = public_path('uploads/product/small/' . $filename);
        if (file_exists($large)) @unlink($large);
        if (file_exists($small)) @unlink($small);
    }

    // ─────────────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────────────
    function index()
    {
        $products = Product::with(['images', 'sizes'])->orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => 200,
            'data'   => $products,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────────────────────────────
    function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'          => 'required|string|max:255',
            'description'    => 'required|string',
            'price'          => 'required|numeric',
            'category_id'    => 'required|integer',
            'sku'            => 'required|unique:products,sku',
            'status'         => 'required|integer',
            'is_featured'    => 'required|in:0,1',
            'image'          => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'images.*'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'sizes'          => 'required|array|min:1',
            'sizes.*.size_id'=> 'required|integer|exists:sizes,id',
            'sizes.*.qty'    => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors(),
            ], 400);
        }

        $product                    = new Product();
        $product->title             = $request->title;
        $product->description       = $request->description;
        $product->price             = $request->price;
        $product->compare_price     = $request->compare_price;
        $product->short_description = $request->short_description;
        $product->category_id       = $request->category_id;
        $product->brand_id          = $request->brand_id;
        $product->sku               = $request->sku;
        $product->barcode           = $request->barcode;
        $product->status            = $request->status;
        $product->is_featured       = $request->is_featured;
        $product->save();

        // ── Images ───────────────────────────────────────────────────────
        if ($request->hasFile('image')) {
            $imageName      = $this->saveImageFile($request->file('image'));
            $product->image = $imageName;
            $product->save();

            ProductImage::create([
                'product_id' => $product->id,
                'image'      => $imageName,
                'is_primary' => 1,
            ]);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                if ($index === 0) continue; // already saved as main image above
                $imageName = $this->saveImageFile($file);
                ProductImage::create([
                    'product_id' => $product->id,
                    'image'      => $imageName,
                    'is_primary' => 0,
                ]);
            }
        }

        // ── Sizes — sync each size with its own stock quantity ───────────
        $sizesData = [];
        foreach ($request->sizes as $sizeRow) {
            $sizesData[$sizeRow['size_id']] = ['qty' => (int) $sizeRow['qty']];
        }
        $product->sizes()->sync($sizesData);

        return response()->json([
            'status'  => 200,
            'message' => 'Product created successfully',
        ], 200);
    }

    // ─────────────────────────────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────────────────────────────
    function show($id)
    {
        // Eager-load sizes so the Edit page can pre-check the right boxes
        $product = Product::with(['images', 'sizes'])->find($id);

        if (!$product) {
            return response()->json([
                'status'  => 404,
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'status'  => 200,
            'product' => $product,
        ], 200);
    }

    // ─────────────────────────────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────────────────────────────
    function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'status'  => 404,
                'message' => 'Product not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title'            => 'required|string|max:255',
            'description'      => 'required|string',
            'price'            => 'required|numeric',
            'category_id'      => 'required|integer',
            'sku'              => 'required|unique:products,sku,' . $id,
            'status'           => 'required|integer',
            'is_featured'      => 'required|in:0,1',
            'images.*'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'removed_images'   => 'nullable|array',
            'removed_images.*' => 'nullable|string',
            'kept_images'      => 'nullable|array',
            'kept_images.*'    => 'nullable|string',
            'sizes'            => 'required|array|min:1',
            'sizes.*.size_id'  => 'required|integer|exists:sizes,id',
            'sizes.*.qty'      => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors(),
            ], 400);
        }

        // ── Basic fields ──────────────────────────────────────────────────
        $product->title             = $request->title;
        $product->description       = $request->description;
        $product->price             = $request->price;
        $product->compare_price     = $request->compare_price;
        $product->short_description = $request->short_description;
        $product->category_id       = $request->category_id;
        $product->brand_id          = $request->brand_id;
        $product->sku               = $request->sku;
        $product->barcode           = $request->barcode;
        $product->status            = $request->status;
        $product->is_featured       = $request->is_featured;

        // ── STEP 1: Delete removed images from disk + DB ──────────────────
        $removedImages = $request->removed_images ?? [];

        foreach ($removedImages as $filename) {
            $this->deleteImageFiles($filename);

            ProductImage::where('product_id', $product->id)
                        ->where('image', $filename)
                        ->delete();

            if ($product->image === $filename) {
                $product->image = null;
            }
        }

        // ── STEP 2: Save new uploaded images ─────────────────────────────
        $newImages = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $imageName   = $this->saveImageFile($file);
                $newImages[] = $imageName;

                ProductImage::create([
                    'product_id' => $product->id,
                    'image'      => $imageName,
                    'is_primary' => 0, // primary assigned in step 3
                ]);
            }
        }

        // ── STEP 3: Decide the main image ────────────────────────────────
        $keptImages = $request->kept_images ?? [];

        if (!empty($keptImages)) {
            // Some old images remain — first kept image stays main
            $product->image = $keptImages[0];

            ProductImage::where('product_id', $product->id)->update(['is_primary' => 0]);
            ProductImage::where('product_id', $product->id)
                        ->where('image', $keptImages[0])
                        ->update(['is_primary' => 1]);

        } elseif (!empty($newImages)) {
            // All old images were removed — promote first new image as main
            $product->image = $newImages[0];

            ProductImage::where('product_id', $product->id)->update(['is_primary' => 0]);
            ProductImage::where('product_id', $product->id)
                        ->where('image', $newImages[0])
                        ->update(['is_primary' => 1]);

        } else {
            // No images left at all
            $product->image = null;
        }

        $product->save();

        // ── STEP 4: Sync sizes with per-size stock quantities ─────────────
        // sync() replaces all existing rows in product_sizes for this product
        // with the newly submitted array — no manual deletes needed.
        $sizesData = [];
        foreach ($request->sizes as $sizeRow) {
            $sizesData[$sizeRow['size_id']] = ['qty' => (int) $sizeRow['qty']];
        }
        $product->sizes()->sync($sizesData);

        return response()->json([
            'status'  => 200,
            'message' => 'Product updated successfully',
        ], 200);
    }

    // ─────────────────────────────────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────────────────────────────────
    function destroy($id)
    {
        $product = Product::with('images')->find($id);
        if (!$product) {
            return response()->json([
                'status'  => 404,
                'message' => 'Product not found',
            ], 404);
        }

        foreach ($product->images as $img) {
            $this->deleteImageFiles($img->image);
        }

        if ($product->image) {
            $this->deleteImageFiles($product->image);
        }

        // Detach sizes from pivot before deleting (or set onDelete cascade in migration)
        $product->sizes()->detach();

        $product->delete();

        return response()->json([
            'status'  => 200,
            'message' => 'Product deleted successfully',
        ], 200);
    }
}