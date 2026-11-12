<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'org_name' => Setting::getValue('org_name', config('app.name')),
            'org_address' => Setting::getValue('org_address', ''),
            'org_phone' => Setting::getValue('org_phone', ''),
            'org_email' => Setting::getValue('org_email', ''),
            'org_logo' => Setting::getValue('org_logo', ''),
            'currency' => Setting::getValue('currency', 'EUR'),
            'tax_rate' => Setting::getValue('tax_rate', '20'),
            'low_stock_alert' => Setting::getValue('low_stock_alert', true),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'org_name' => 'required|string|max:255',
            'org_address' => 'nullable|string|max:255',
            'org_phone' => 'nullable|string|max:50',
            'org_email' => 'nullable|email|max:255',
            'org_logo' => 'nullable|string|max:500',
            'currency' => 'required|string|max:10',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'low_stock_alert' => 'boolean',
        ]);

        foreach ($data as $key => $value) {
            $type = $key === 'low_stock_alert' ? 'boolean' : 'string';
            Setting::setValue($key, $key === 'low_stock_alert' ? $request->boolean($key) : $value, $type);
        }

        return redirect()->route('settings.index')
            ->with('success', __('flash.settings_updated'));
    }
}
