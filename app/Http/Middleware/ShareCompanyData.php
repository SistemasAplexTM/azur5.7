<?php

namespace App\Http\Middleware;

use App\Company as AppCompany;
use Closure;
use App\Models\Company;

class ShareCompanyData
{
    public function handle($request, Closure $next)
    {
        // Obtener la primera empresa (ajusta según tu lógica)
        $company = AppCompany::first(); 

        // Compartir la variable con todas las vistas
        view()->share('company', $company);

        return $next($request);
    }
}