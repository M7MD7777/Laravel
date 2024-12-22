<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\File;
use Spatie\Translatable\HasTranslations;
use Illuminate\Support\Facades\Config;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $locale = $request->header('Accept-Language', 'en');
        $categories = Category::select("id", "name->$locale as name", 'image_path')->get();

        return response()->json($categories);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'Name' => 'required',
            'image_path' => 'required|mimes:jpg,png,jpeg|max:5048',
        ]);

        $newImageName = time() . '_' . $request->input('Name') . '.' . $request->file('image_path')->extension();
        $request->file('image_path')->move(public_path('images'), $newImageName);

        $category = new Category();
        $category->setTranslation('Name', 'en', $request->input('Name'));
        $category->setTranslation('Name', 'ar', $request->input('Name_ar')); // Adjust the field name as needed
        $category->image_path = $newImageName;
        $category->save();

        return response()->json([
            'message' => Lang::get('categories.created_successfully'),
            'category' => $category,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        $category = Category::findOrFail($id);


        $locale = $request->header('Accept-Language', 'en');
        $response = [
            'id' => $category->id,
            'Name' => $category->getTranslation('Name', $locale),
            'image_path' => $category->image_path,
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
            'image_path' => 'nullable|mimes:jpg,png,jpeg|max:5048',
        ]);

        $category = Category::findOrFail($id);

        // Check if a file is present in the request
        if ($request->hasFile('image_path')) {
            $newImageName = time() . '_' . $request->input('Name') . '.' . $request->file('image_path')->extension();
            $request->file('image_path')->move(public_path('images'), $newImageName);

            // Delete the old image
            $oldImagePath = public_path('images') . '/' . $category->image_path;
            File::delete($oldImagePath);

            $category->image_path = $newImageName;
        }

        $category->setTranslation('Name', 'en', $request->input('Name'));
        $category->setTranslation('Name', 'ar', $request->input('Name_ar')); // Adjust the field name as needed
        $category->save();

        return response()->json([
            'message' => Lang::get('categories.updated_successfully'),
            'category' => $category,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => Lang::get('categories.deleted_successfully'),
        ]);
    }

    /**
     * Search for a category by name.
     */

    public function search($name, Request $request)
    {
        $locale = $request->header('Accept-Language', 'en');

        $query = Category::query();

        if ($locale == 'ar') {
            $results = $query->where("Name->ar", 'like', '%' . $name . '%')->get();

            // Transform the response to the desired structure for Arabic
            $transformedResults = $results->map(function ($result) use ($locale) {
                return [
                    'id' => $result->id,
                    'Name' => $result->getTranslation('Name', $locale),
                    'image_path' => $result->image_path,
                    // Add other fields as needed
                ];
            });
        } else {
            $results = $query->where("Name->en", 'like', '%' . $name . '%')->get();

            // Transform the response to the desired structure for English
            $transformedResults = $results->map(function ($result) use ($locale) {
                return [
                    'id' => $result->id,
                    'Name' => $result->getTranslation('Name', $locale),
                    'image_path' => $result->image_path,
                    // Add other fields as needed
                ];
            });
        }

        return response()->json($transformedResults);
    }
}
