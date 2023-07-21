<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddMaterialRequest;
use App\Http\Requests\EditMaterialRequest;
use App\Models\Material;
use App\Models\ProductMaterial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaterialController extends Controller
{
    public function GetMaterials(Request $request): JsonResponse
    {
        $materials = Material::where('user_id', Auth::id());

        if ($request->has('term')) {
            $materials->where('name', 'LIKE', '%' . $request->term . '%');
        }

        return response()->json($materials->paginate(20));
    }

    public function GetMaterialProducts(Request $request, $id): JsonResponse
    {
        $products = ProductMaterial::where('material_id', $id)->whereHas('product', function($query) use ($request){
            $query->where('user_id', Auth::id());
            if ($request->has('term')) {
                $query->where('name', 'LIKE', '%' . $request->term . '%');
            }
        })->with('product')->paginate(20);

        return response()->json($products);
    }

    public function AddMaterial(AddMaterialRequest $request): JsonResponse
    {
        $material = Material::create([
            'name' => $request->name,
            'price_per_uom' => number_format($request->price_per_uom, 2, '.', ''),
            'uom' => $request->uom,
            'user_id' => Auth::id(),
            'category_id' => $request->category_id
        ]);

        return response()->json(['material' => $material, 'message' =>'Material added successfully']);
    }

    public function EditMaterial(EditMaterialRequest $request): JsonResponse
    {
        $material = Material::where([['id', $request->id], ['user_id', Auth::id()]])->first();
        if ($request->has('price_per_uom') && $request->price_per_uom !== $material->price_per_uom) {
            $price_per_uom_diff = $request->price_per_uom - $material->price_per_uom;
            $material->price_per_uom = number_format($request->price_per_uom, 2, '.', '');
            $material->products()->update(['price_cost' => DB::raw("price_cost + (" . $price_per_uom_diff . " * material_quantity)"), 'price_diff' => DB::raw("price_sell - (price_cost + (" . $price_per_uom_diff . " * material_quantity))")]);
        }
        $material->fill($request->all())->save();

        return response()->json(['material' => $material, 'message' =>'Material edited successfully']);
    }

    public function DeleteMaterial($id): JsonResponse
    {
        $material = Material::find($id);
        $material->products()->update(['price_cost' => DB::raw("price_cost - (material_quantity * " . $material->price_per_uom . ")"), 'price_diff' => DB::raw("price_sell - (price_cost - (material_quantity * " . $material->price_per_uom . "))")]);
        $response = Material::destroy($id);

        if ($response) {
            return response()->json(['message' =>'Material deleted successfully']);
        }
        return response()->json(['message' =>'Material does not exist'], 404);
    }
}
