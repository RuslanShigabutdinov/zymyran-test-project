<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKaspiProductRequest;
use App\Http\Requests\UpdateKaspiProductRequest;
use App\Models\KaspiProduct;

class KaspiProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = KaspiProduct::paginate(10);
        return $items;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKaspiProductRequest $request)
    {
        $item = KaspiProduct::create($request->validated());

        return response()->json($item, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(KaspiProduct $kaspiProduct)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KaspiProduct $kaspiProduct)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKaspiProductRequest $request, KaspiProduct $kaspiProduct)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KaspiProduct $kaspiProduct)
    {
        $kaspiProduct->delete();
        return response(null, 204);
    }
}
