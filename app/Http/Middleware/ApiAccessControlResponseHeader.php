<?php

namespace App\Http\Middleware;

use Closure;

class ApiAccessControlResponseHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // ALLOW OPTIONS METHOD
        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods'=> 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers'=> 'Authorization, X-Requested-With, Content-Type, Accept, Origin'
        ];
        if($request->getMethod() == "OPTIONS") {
            // The client-side application can set only headers allowed in Access-Control-Allow-Headers
            return \Response::make('OK', 200, $headers);
        }

        /** @var \Illuminate\Http\Response $response */
        $response = $next($request);
        foreach($headers as $key => $value) {
            $response->header($key, $value);
        }
        return $response;
    }
}
