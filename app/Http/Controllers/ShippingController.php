<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ShippingController extends Controller
{
    public function provinces(): JsonResponse
    {
        $response = Http::withHeaders(['key' => $this->apiKey()])
            ->get($this->endpoint('province'));

        return response()->json($response->json(), $response->status());
    }

    public function cities(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'province_id' => ['required', 'string'],
        ]);

        $response = Http::withHeaders(['key' => $this->apiKey()])
            ->get($this->endpoint('city'), [
                'province' => $validated['province_id'],
            ]);

        return response()->json($response->json(), $response->status());
    }

    public function cost(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'city_id' => ['required', 'string'],
            'weight' => ['required', 'integer', 'min:1'],
            'courier' => ['required', 'string', 'max:50'],
        ]);

        $originCityId = (string) config('services.jagoongkir.origin_city_id');

        abort_if(blank($originCityId), 503, 'Shipping service is not configured.');

        $response = Http::withHeaders(['key' => $this->apiKey()])
            ->post($this->endpoint('cost'), [
                'origin' => $originCityId,
                'destination' => $validated['city_id'],
                'weight' => $validated['weight'],
                'courier' => $validated['courier'],
            ]);

        return response()->json($response->json(), $response->status());
    }

    private function apiKey(): string
    {
        $apiKey = (string) config('services.jagoongkir.key');

        abort_if(blank($apiKey), 503, 'Shipping service is not configured.');

        return $apiKey;
    }

    private function endpoint(string $path): string
    {
        return rtrim((string) config('services.jagoongkir.base_url', 'https://api.jagoongkir.com/v1'), '/').'/'.ltrim($path, '/');
    }
}
