<?php

use Illuminate\Support\Facades\Http;

test('shipping endpoints return 503 when the api key is missing', function () {
    config([
        'services.jagoongkir.base_url' => 'https://api.jagoongkir.com/v1',
        'services.jagoongkir.key' => null,
        'services.jagoongkir.origin_city_id' => null,
    ]);

    $this->get('/ongkir/provinces')->assertStatus(503);
    $this->post('/ongkir/cost', [
        'city_id' => '501',
        'weight' => 1000,
        'courier' => 'jne',
    ])->assertStatus(503);
});

test('shipping cost uses config backed credentials and origin city', function () {
    config([
        'services.jagoongkir.base_url' => 'https://api.jagoongkir.com/v1',
        'services.jagoongkir.key' => 'test-api-key',
        'services.jagoongkir.origin_city_id' => '152',
    ]);

    Http::fake([
        'https://api.jagoongkir.com/v1/cost' => Http::response([
            'rajaongkir' => [
                'results' => [
                    [
                        'costs' => [
                            ['service' => 'REG', 'cost' => [['value' => 20000, 'etd' => '2-3']]],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $this->post('/ongkir/cost', [
        'city_id' => '501',
        'weight' => 1000,
        'courier' => 'jne',
    ])->assertOk()->assertJsonPath('rajaongkir.results.0.costs.0.service', 'REG');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.jagoongkir.com/v1/cost'
            && $request->hasHeader('key', 'test-api-key')
            && $request['origin'] === '152'
            && $request['destination'] === '501'
            && $request['weight'] === 1000
            && $request['courier'] === 'jne';
    });
});
