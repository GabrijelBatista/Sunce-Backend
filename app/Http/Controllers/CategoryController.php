<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCategoryRequest;
use App\Http\Requests\EditCategoryRequest;
use App\Models\Category;
use App\Models\Material;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function GetProductCategories(Request $request): JsonResponse
    {
        $categories = Category::where([['user_id', Auth::id()], ['type', 1]])
            ->whereHas('products', function ($query) use ($request) {
            if ($request->has('term')) {
                $query->where('name', 'LIKE', '%' . $request->term . '%');
            }
        })->paginate(10);


        return response()->json($categories);
    }

    public function GetMaterialCategories(Request $request): JsonResponse
    {
        $categories = Category::where([['user_id', Auth::id()], ['type', 2]])
            ->whereHas('materials', function ($query) use ($request) {
                if ($request->has('term')) {
                    $query->where('name', 'LIKE', '%' . $request->term . '%');
                }
            })->paginate(10);

        return response()->json($categories);
    }

    public function GetCategoryMaterials(Request $request, $id): JsonResponse
    {
        $materials = Material::where([['user_id', Auth::id()], ['category_id', $id]]);
        if ($request->has('term')) {
            $materials->where('name', 'LIKE', '%' . $request->term . '%');
        }

        return response()->json($materials->paginate(6));
    }

    public function GetCategoryProducts(Request $request, $id): JsonResponse
    {
        $products = Product::where([['user_id', Auth::id()], ['category_id', $id]]);
        if ($request->has('term')) {
            $products->where('name', 'LIKE', '%' . $request->term . '%');
        }

        return response()->json($products->paginate(20));
    }

    public function AutocompleteCategories(Request $request): JsonResponse
    {
        $categories = Category::select('id', 'name', 'type')
            ->where([['user_id', Auth::id()], ['type', $request->type], ['name', 'LIKE', '%' . $request->term . '%']])
            ->limit(10)
            ->get();

        return response()->json($categories);
    }

    public function AddCategory(AddCategoryRequest $request): JsonResponse
    {
        $category = Category::create([
            'name' => $request->name,
            'type' => $request->type,
            'user_id' => Auth::id()
        ]);

        return response()->json(['category' => $category, 'message' => 'Category added successfully']);
    }

    public function EditCategory(EditCategoryRequest $request): JsonResponse
    {
        $category = Category::where([['id', $request->id], ['user_id', Auth::id()]])->first();
        $category->fill($request->all())->save();

        return response()->json(['category' => $category, 'message' => 'Category edited successfully']);
    }

    public function DeleteCategory($id): JsonResponse
    {
        $response = Category::destroy($id);

        if ($response) {
            return response()->json(['message' => 'Category deleted successfully']);
        }
        return response()->json(['message' => 'Category cannot be deleted because it is not empty'], 403);
    }
}
