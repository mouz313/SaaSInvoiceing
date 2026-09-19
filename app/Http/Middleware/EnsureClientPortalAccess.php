<?php

namespace App\Http\Middleware;

use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientPortalAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clientId = $request->session()->get('portal_client_id');

        if (! $clientId) {
            return redirect()->route('portal.login')
                ->with('error', 'Please log in or use your private access link to view your portal.');
        }

        $client = Client::with('user')->find($clientId);

        if (! $client) {
            $request->session()->forget('portal_client_id');

            return redirect()->route('portal.login')
                ->with('error', 'Client account not found.');
        }

        view()->share('portalClient', $client);
        $request->attributes->set('portalClient', $client);

        return $next($request);
    }
}
