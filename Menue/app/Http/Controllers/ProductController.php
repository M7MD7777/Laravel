<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\File;
use Spatie\Translatable\HasTranslations;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $locale = $request->header('Accept-Language', 'en');
        $categories = Product::select("id", "name->$locale as name", "description->$locale as description", 'image_path')->get();

        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'Name' => 'required',
            'Price' => 'required',
            'Description' => 'required',
            'image_path' => 'required|mimes:jpg,png,jpeg|max:5048',
            'category_id' => 'required',
        ]);

        $newImageName = time() . '_' . $request->Name . '.' . $request->image_path->extension();
        $request->image_path->move(public_path('images'), $newImageName);

        $product = new Product();
        $product->setTranslation('Name', 'en', $request->input('Name'));
        $product->setTranslation('Name', 'ar', $request->input('Name_ar')); // Adjust the field name as needed
        $product->Price = $request->input('Price');
        $product->setTranslation('Description', 'en', $request->input('Description'));
        $product->setTranslation('Description', 'ar', $request->input('Description_ar')); // Adjust the field name as needed
        $product->image_path = $newImageName;
        $product->category_id = $request->input('category_id');
        $product->save();

        return response()->json([
            'message' => Lang::get('products.created_successfully'),
            'product' => $product,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        $product = Product::findOrFail($id);

        $locale = $request->header('Accept-Language', 'en');
        $response = [
            'Name' => $product->getTranslation('Name', $locale),
            'Price' => $product->Price,
            'Description' => $product->getTranslation('Description', $locale),
            'image_path' => $product->image_path,
            'category_id' => $product->category_id,
        ];

        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'Name' => 'required',
            'Price' => 'required',
            'Description' => 'required',
            'image_path' => 'nullable|mimes:jpg,png,jpeg|max:5048',
            'category_id' => 'required',
        ]);

        $product = Product::findOrFail($id);

        // Check if a file is present in the request
        if ($request->hasFile('image_path')) {
            $newImageName = time() . '_' . $request->input('Name') . '.' . $request->file('image_path')->extension();
            $request->file('image_path')->move(public_path('images'), $newImageName);

            // Delete the old image
            $oldImagePath = public_path('images') . '/' . $product->image_path;
            File::delete($oldImagePath);

            $product->image_path = $newImageName;
        }

        $product->setTranslation('Name', 'en', $request->input('Name'));
        $product->setTranslation('Name', 'ar', $request->input('Name_ar')); // Adjust the field name as needed
        $product->Price = $request->input('Price');
        $product->setTranslation('Description', 'en', $request->input('Description'));
        $product->setTranslation('Description', 'ar', $request->input('Description_ar')); // Adjust the field name as needed
        $product->category_id = $request->input('category_id');
        $product->save();

        return response()->json([
            'message' => Lang::get('products.updated_successfully'),
            'product' => $product,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => Lang::get('products.deleted_successfully'),
        ]);
    }

    /**
     * Search for a product by name.
     */
    public function search($name, Request $request)
    {
        $locale = $request->header('Accept-Language', 'en');

        $query = Product::query();

        if ($locale == 'ar') {
            $results = $query->where("Name->ar", 'like', '%' . $name . '%')
                ->orWhere("Description->ar", 'like', '%' . $name . '%')
                ->get();

            // Transform the response to the desired structure for Arabic
            $transformedResults = $results->map(function ($result) use ($locale) {
                return [
                    'id' => $result->id,
                    'Name' => $result->getTranslation('Name', $locale),
                    'Description' => $result->getTranslation('Description', $locale),
                    'image_path' => $result->image_path,
                    'category_id' => $result->category_id,
                ];
            });
        }
        if ($locale == 'en') {
            $results = $query->where("Name->en", 'like', '%' . $name . '%')
                ->orWhere("Description->en", 'like', '%' . $name . '%')
                ->get();

            // Transform the response to the desired structure for English
            $transformedResults = $results->map(function ($result) use ($locale) {
                return [
                    'id' => $result->id,
                    'Name' => $result->getTranslation('Name', $locale),
                    'Description' => $result->getTranslation('Description', $locale),
                    'image_path' => $result->image_path,
                    'category_id' => $result->category_id,
                ];
            });
        }

        return response()->json($transformedResults);
    }
}
