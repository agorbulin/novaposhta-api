<?php

class NovaPoshtaApi
{
    const API_URI = 'http://api.novaposhta.ua/v2.0/json/';
    const API_KEY = 'API_key';

    protected $api;

    public function __construct()
    {
        $this->init();
    }

    public function __destruct()
    {
        curl_close($this->api);
    }

    protected function init()
    {
        $ch = curl_init(static::API_URI);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->api = $ch;
    }

    public function findCities($city)
    {
        $data = [
            "modelName" => "Address",
            "calledMethod" => "getCities",
            "methodProperties" => [
                "FindByString" => $city,
                "Limit" => 5
            ],
            "apiKey" => static::API_KEY
        ];
        $request = json_encode($data);
        curl_setopt($this->api, CURLOPT_POSTFIELDS, $request);
        $response = curl_exec($this->api);
        $result = [];
        foreach ($this->processResponse($response) as $item) {
            $result[] = $item->Description;
        }

        return $result;
    }

    public function getWarehousesByCity($city)
    {
        $data = [
            "modelName" => "AddressGeneral",
            "calledMethod" => "getWarehouses",
            "methodProperties" => [
                "CityName" => $city
            ],
            "apiKey" => static::API_KEY
        ];
        $request = json_encode($data);
        curl_setopt($this->api, CURLOPT_POSTFIELDS, $request);
        $response = curl_exec($this->api);
        $result = [];
        foreach ($this->processResponse($response) as $item) {
            if ($item->CategoryOfWarehouse === 'Postomat') {
                continue;
            }
            $result[] = $item->Description;
        }

        return $result;
    }

    protected function processResponse($response)
    {
        $response = (json_decode($response));
        if (isset($response->success) && $response->success !== true) {
            return [];
        }

        return $response->data;
    }
}

$api = new NovaPoshtaApi();
$result = $api->findCities('Винн');
var_dump($result);
$result = $api->getWarehousesByCity($result[0]);
var_dump($result);
