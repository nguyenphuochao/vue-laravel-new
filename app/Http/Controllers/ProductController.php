<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // sleep(5);
        $conds = [];

        if(request()->has("keyword")) {
            $keyword = request()->input('keyword');
            $conds[] = ["name", "like", "%$keyword%"];
        }

        return Product::where($conds)->orderBy('created_at', 'desc')->paginate(5)->withQueryString();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // authorization
        if (!Gate::allows('create-products')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // handle validated
        $validated = $request->validate([
            'name' => 'required|min:5',
            'price' => 'required|numeric|gt:0',
        ]);

        // create product in DB
        $product = Product::create([
            'name'     => $request->input('name'),
            'price'    => $request->input('price'),
        ]);

        // response
        return response([
            'product' => $product
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // authorization
        if (!Gate::allows('update-products')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // validate
        $validated = $request->validate([
            'name' => 'required|min:5',
            'price' => 'required|numeric|gt:0',
        ]);

        // find one produuct by id
        $product = Product::find($id);

        // update product
        $product->name = $request->input('name');
        $product->price = $request->input('price');

        $product->save();

        // response
        return response([
            'product' => $product
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // authorization
        if (!Gate::allows('delete-products')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $product = Product::find($id);
        $product->delete();
        return response([
            'result' => 'success'
        ], 200);
    }
}
