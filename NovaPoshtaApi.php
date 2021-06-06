<?php declare(strict_types=1);

/**
 * Class NovaPoshtaApi
 */
class NovaPoshtaApi
{
    /**
     * API URL
     */
    const API_URI = 'http://api.novaposhta.ua/v2.0/json/';
    /**
     * API KEY
     */
    const API_KEY = 'a8c09a5ed046fba223343e6ec4211842';

    protected $api;

    /**
     * NovaPoshtaApi constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    public function __destruct()
    {
        curl_close($this->api);
    }

    /**
     * Init curl
     */
    protected function init()
    {
        $ch = curl_init(static::API_URI);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->api = $ch;
    }

    /**
     * Find city ID (REF) by name or part of name
     *
     * @param string $city
     *
     * @return array
     * @throws \Exception
     */
    public function findCityRefByName($city)
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
            $result[] = [
                "Description" => $item->Description,
                "CityRef" => $item->Ref
            ];
        }

        return $result;
    }

    /**
     * Get city warehouses using city REF
     *
     * @param string $cityRef
     * @param bool $skipPastomat
     *
     * @return array
     * @throws \Exception
     */
    public function getWarehousesByCityRef($cityRef, $skipPastomat = true)
    {
        $data = [
            "modelName" => "AddressGeneral",
            "calledMethod" => "getWarehouses",
            "methodProperties" => [
                "CityRef" => $cityRef
            ],
            "apiKey" => static::API_KEY
        ];
        $request = json_encode($data);
        curl_setopt($this->api, CURLOPT_POSTFIELDS, $request);
        $response = curl_exec($this->api);
        $result = [];
        foreach ($this->processResponse($response) as $item) {
            if ($skipPastomat && $item->CategoryOfWarehouse === 'Postomat') {
                continue;
            }
            $result[] = ['Description' => $item->Description];
        }

        return $result;
    }

    /**
     * Decode response and handle errors
     *
     * @param string $response
     *
     * @return \stdClass[]
     * @throws \Exception
     */
    protected function processResponse($response)
    {
        $response = (json_decode($response));
        if (isset($response->errors[0])) {
            throw new \Exception('API error:' . $response->errors[0]);
        }

        return $response->data;
    }
}

$api = new NovaPoshtaApi();
$cities = $api->findCityRefByName('Винн');
var_dump($cities);
$firstCityRef = reset($cities);
if (isset($firstCityRef['CityRef'])) {
    $result = $api->getWarehousesByCityRef($firstCityRef['CityRef']);
    var_dump($result);
}
