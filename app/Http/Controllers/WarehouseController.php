<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::withCount(['stocks', 'movements'])
            ->orderBy('name')->get();

        return view('warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        return view('warehouses.create');
    }

    public function store(Request $request)
    {
        Warehouse::create($this->validated($request));

        return redirect()->route('warehouses.index')
            ->with('success', __('flash.warehouse_created'));
    }

    public function edit(Warehouse $warehouse)
    {
        return view('warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $warehouse->update($this->validated($request, $warehouse));

        return redirect()->route('warehouses.index')
            ->with('success', __('flash.warehouse_updated'));
    }

    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->stocks()->sum('quantity') > 0) {
            return back()->with('error', __('flash.warehouse_not_empty'));
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')
            ->with('success', __('flash.warehouse_deleted'));
    }

    protected function validated(Request $request, ?Warehouse $warehouse = null): array
    {
        $unique = $warehouse ? ',' . $warehouse->id : '';

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:warehouses,code' . $unique,
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['is_default'] = $request->boolean('is_default');

        // Un seul entrepôt par défaut
        if ($data['is_default']) {
            Warehouse::where('id', '!=', $warehouse?->id)->update(['is_default' => false]);
        }

        return $data;
    }
}
