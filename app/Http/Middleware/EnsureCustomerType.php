<?php

namespace App\Http\Middleware;

use App\Enums\CustomerStatus;
use App\Models\Customer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $expectedType): Response
    {
        $customerId = $request->route('id');

        if (!$customerId) {
            return $next($request);
        }

        $customer = Customer::find($customerId);

        if (!$customer instanceof Customer) {
            return $next($request);
        }

        // Se accediamo a /leads/123 ma il customer è diventato CUSTOMER
        if ($expectedType === 'lead' && !$customer->isLead()) {
            return redirect()->route('customer.show', ['id' => $customer->id])
                ->with('info', 'Il lead è stato convertito in cliente.');
        }

        // Se accediamo a /customers/123 ma il customer è ancora un LEAD
        if ($expectedType === 'customer' && !$customer->isCustomer()) {
            return redirect()->route('lead.show', ['id' => $customer->id])
                ->with('info', 'Questo è ancora un lead.');
        }

        return $next($request);
    }
}
