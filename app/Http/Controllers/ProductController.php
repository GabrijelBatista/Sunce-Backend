<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddProductRequest;
use App\Http\Requests\EditProductRequest;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductMaterial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function GetProducts(Request $request): JsonResponse
    {
        $products = Product::where('user_id', Auth::id());

        if ($request->has('term')) {
            $products->where('name', 'LIKE', '%' . $request->term . '%');
        }

        return response()->json($products->with('category')->paginate(20)->groupBy('category_id'));
    }

    public function GetProductMaterials(Request $request, $id): JsonResponse
    {
        $product_materials = ProductMaterial::where('product_id', $id)->whereHas('material', function($query) use ($request){
            $query->where('user_id', Auth::id());

            if ($request->has('term')) {
                $query->where('name', 'LIKE', '%' . $request->term . '%');
            }
        })->with('material')->paginate(6);

        return response()->json($product_materials);
    }

    public function AutocompleteProducts(Request $request): JsonResponse
    {
        $products = Product::where([['user_id', Auth::id()], ['name', 'LIKE', '%' . $request->term . '%']])
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    public function AddProduct(AddProductRequest $request): JsonResponse
    {
        $materials_to_array = [];
        foreach ( $request->materials as $material) {
            $materials_to_array[$material['id']] = ['material_quantity' => $material['material_quantity']];
        }
        $materials = Material::whereIn('id', array_keys($materials_to_array))->get();

        $price_cost = 0;
        foreach ( $materials as $material) {
            $price_cost += $material->price_per_uom * $materials_to_array[$material['id']]['material_quantity'];
        }
        $price_diff = $request->price_sell - $price_cost;
        $product = Product::create([
            'name' => $request->name,
            'price_sell' => number_format($request->price_sell, 2, '.', ''),
            'price_cost' => number_format($price_cost, 2, '.', ''),
            'price_diff' => number_format($price_diff, 2, '.', ''),
            'user_id' => Auth::id(),
            'category_id' => $request->category_id
        ]);
        $product->materials()->sync($materials_to_array);

        return response()->json(['product' => $product->load(['materials', 'category']), 'message' =>'Product added successfully']);
    }

    public function EditProduct(EditProductRequest $request): JsonResponse
    {
        $product = Product::where([['id', $request->id], ['user_id', Auth::id()]])->first();
        if ($request->has('price_sell')) {
            $request->price_sell = number_format($request->price_sell, 2, '.', '');
        }
        $product->fill($request->except('materials'));

        if ($request->has('materials')) {
            $materials_to_array = [];
            foreach ( $request->materials as $material) {
                $materials_to_array[$material['id']] = ['material_quantity' => $material['material_quantity']];
            }
            $materials = Material::whereIn('id', array_keys($materials_to_array))->get();

            $price_cost = 0;
            foreach ( $materials as $material) {
                $price_cost += $material->price_per_uom * $materials_to_array[$material['id']]['material_quantity'];
            }

            $price_diff = $request->price_sell - $price_cost;
            $product->materials()->sync($materials_to_array);
            $product->price_cost = number_format($price_cost, 2, '.', '');
            $product->price_diff = number_format($price_diff, 2, '.', '');
        } else if ($request->has('price_sell')) {
            $product->price_diff = $product->price_sell - $product->price_cost;
        }

        $product->save();

        return response()->json(['product' => $product->load(['category']), 'message' =>'Product edited successfully']);
    }

    public function DeleteProduct($id): JsonResponse
    {
        $response = Product::destroy($id);

        if ($response) {
            return response()->json(['message' =>'Product deleted successfully']);
        }
        return response()->json(['message' =>'Product does not exist'], 404);
    }
}
