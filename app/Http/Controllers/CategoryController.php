<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCategoryRequest;
use App\Http\Requests\EditCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function GetProductCategories(Request $request): JsonResponse
    {
        if($request->has('term')){
            $categories = Category::where([['user_id', Auth::id()], ['type', 1]])
                ->whereHas('products', function ($query) use ($request) {
                    if ($request->has('term')) {
                        $query->where('name', 'LIKE', '%' . $request->term . '%');
                    }
                })->with('products', function ($query) use ($request) {
                    if ($request->has('term')) {
                        $query->where('name', 'LIKE', '%' . $request->term . '%');
                    }
            })->paginate(10);
        } else {
            $categories = Category::where([['user_id', Auth::id()], ['type', 1]])->with('products')->paginate(10);
        }


        return response()->json($categories);
    }

    public function GetMaterialCategories(Request $request): JsonResponse
    {
        if($request->has('term')){
            $categories = Category::where([['user_id', Auth::id()], ['type', 2]])
                ->whereHas('materials', function ($query) use ($request) {
                    if ($request->has('term')) {
                        $query->where('name', 'LIKE', '%' . $request->term . '%');
                    }
                })->with('materials', function ($query) use ($request) {
                    if ($request->has('term')) {
                        $query->where('name', 'LIKE', '%' . $request->term . '%');
                    }
                })->paginate(10);
        } else {
            $categories = Category::where([['user_id', Auth::id()], ['type', 2]])->with('materials')->paginate(10);
        }
        return response()->json($categories);
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
        $name_check = Category::where([['name', $request->name], ['type', $request->type], ['user_id', Auth::id()]])->first();
        if($name_check){
            return response()->json(['message' => 'Kategorija s ovim imenom već postoji.'], 400);
        }

        $category = Category::create([
            'name' => $request->name,
            'type' => $request->type,
            'user_id' => Auth::id()
        ]);

        return response()->json(['category' => $category, 'message' => 'Kategorija uspješno dodana.']);
    }

    public function EditCategory(EditCategoryRequest $request): JsonResponse
    {
        $category = Category::where([['id', $request->id], ['user_id', Auth::id()]])->first();
        $name_check = Category::where([['name', $request->name], ['type', $request->type], ['user_id', Auth::id()]])->get();
        if($name_check[1] || ($name_check[0] && $name_check->name !== $category->name)){
            return response()->json(['message' => 'Kategorija s ovim imenom već postoji.'], 400);
        }
        $category->fill($request->all())->save();

        return response()->json(['category' => $category, 'message' => 'Kategorija uspješno uređena.']);
    }

    public function DeleteCategory($id): JsonResponse
    {
        $response = Category::destroy($id);

        if ($response) {
            return response()->json(['message' => 'Kategorija uspješno obrisana.']);
        }
        return response()->json(['message' => 'Kategorija ne može biti obrisana jer nije prazna.'], 403);
    }
}
