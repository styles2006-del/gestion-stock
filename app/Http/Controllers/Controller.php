<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Informations de l'organisation (paramètres de l'application).
     */
    protected function orgInfo(): array
    {
        return [
            'name' => Setting::getValue('org_name', config('app.name')),
            'address' => Setting::getValue('org_address', ''),
            'phone' => Setting::getValue('org_phone', ''),
            'email' => Setting::getValue('org_email', ''),
            'logo' => Setting::getValue('org_logo', ''),
            'currency' => Setting::getValue('currency', 'EUR'),
            'tax_rate' => Setting::getValue('tax_rate', '20'),
        ];
    }
}
