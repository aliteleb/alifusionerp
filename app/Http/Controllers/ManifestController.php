<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManifestController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $manifest = [
            'name' => settings('app_name') ?: config('app.name', 'Ali Fusion ERP'),
            'short_name' => settings('app_name') ?: 'Ali Fusion ERP',
            'description' => settings('app_description_en') ?: 'Professional Ali Fusion ERP platform for multi-tenant operations',
            'icons' => [
                [
                    'src' => settings('favicon') ?: asset('favicon.ico'),
                    // 'sizes' => '16x16',
                    'type' => 'image/png',
                ],
                [
                    'src' => settings('favicon') ?: asset('favicon.ico'),
                    // 'sizes' => '32x32',
                    'type' => 'image/png',
                ],
                [
                    'src' => settings('logo') ?: settings('favicon') ?: asset('favicon.ico'),
                    // 'sizes' => '180x180',
                    'type' => 'image/png',
                ],
            ],
            'theme_color' => '#ffffff',
            'background_color' => '#ffffff',
            'display' => 'standalone',
            'start_url' => '/admin',
            'scope' => '/',
        ];

        return response()->json($manifest, 200, [
            'Content-Type' => 'application/manifest+json',
        ]);
    }
}
