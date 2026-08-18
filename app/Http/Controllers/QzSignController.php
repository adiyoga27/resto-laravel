<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class QzSignController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $privateKeyPath = storage_path('app/qz/private-key.pem');

        if (! file_exists($privateKeyPath)) {
            abort(500, 'QZ private key not found.');
        }

        $message = $request->getContent();

        Log::info('QZ sign request received', ['bytes' => strlen($message)]);

        $signature = null;
        $signed = openssl_sign($message, $signature, file_get_contents($privateKeyPath), OPENSSL_ALGO_SHA512);

        if (! $signed) {
            abort(500, 'Failed to sign QZ message.');
        }

        return response(base64_encode($signature), 200)->header('Content-Type', 'text/plain');
    }
}
