<?php

// ARG #1: base url
// ARG #2: tests (comma separated list) to execute
// ARG #2: identifier
// ARG #3: secret
// ARG #4: debug? (optional, no debug by default)

$test = new Test($_SERVER['argv'][1], $_SERVER['argv'][3], $_SERVER['argv'][4], isset($_SERVER['argv'][5]));
$test->executeTests($_SERVER['argv'][2]);

class Test
{
    private $base = '';
    private $identifier = '';
    private $secret = '';
    private $token = '';
    private $show_results = true;
    private $lastCurlEquivalent = '';
    
    public function __construct($url, $identifier, $secret, $show_results = true)
    {
        $this->base = $url;
        $this->identifier = $identifier;
        $this->secret = $secret;
        $this->initToken();
        $this->show_results = $show_results;
    }
    
    public function executeTests($tests)
    {
        if ( !is_array($tests) ) {
            $tests = explode(',', $tests);
        }
        
        foreach ( $tests as $test ) {
            if ( method_exists($this, $method = 'test'.ucfirst($test)) ) {
                $this->$method();
            }
            else {
                echo "Test $test is unavailable.\n\n";
            }
        }
    }
    
    protected function initToken()
    {
        $res = $this->request(($endpoint = "/api/oauth/v2/token")."?client_id=".$this->identifier."&client_secret=".$this->secret."&grant_type=password");
        if ( $this->show_results ) {
            $this->printResult($endpoint, 'token', $res);
        }
        
        $json = json_decode($res->getData(), true);
        $this->token = $json['access_token'];
        
        return $this;
    }
    
    public function testOrders()
    {
        // TODO
        
        return $this;
    }
    public function testCustomers()
    {
        // create
        $customer = [
            'email'     => 'baptiste.simon@test.tld',
            'firstName' => 'Baptiste',
            'lastName'  => 'SIMON',
            'address'   => 'TEST',
            'zip'       => '29000',
            'city'      => 'Quimper',
            'country'   => 'France',
            'password'  => 'TesT',
        ];
        $res = $this->request($endpoint = '/api/v2/customers', 'POST', $customer);
        $this->printResult($endpoint, 'create', $res);
        $data = $res->getData(true);
        
        if ( !$res->isSuccess() ) {
            echo "Aborting...\n";
            return $this;
        }
        
        // login
        $res = $this->request($endpoint = '/api/v2/login', 'POST', ['email' => $customer['email'], 'password' => $customer['password']]);
        $this->printResult($endpoint, 'login', $res);
        
        // list
        $res = $this->request($endpoint = '/api/v2/customers', 'GET');
        $this->printResult($endpoint, 'list', $res);
        
        // logout
        $res = $this->request($endpoint = '/api/v2/logout', 'GET');
        $this->printResult($endpoint, 'logout', $res);
        
        // list
        $res = $this->request($endpoint = '/api/v2/customers', 'GET');
        $this->printResult($endpoint, 'list', $res);
        
        // login
        $res = $this->request($endpoint = '/api/v2/login', 'POST', ['email' => $customer['email'], 'password' => $customer['password']]);
        $this->printResult($endpoint, 'login', $res);
        
        // one
        $res = $this->request($endpoint = '/api/v2/customers/'.$data['id'], 'GET');
        $this->printResult($endpoint, 'resource', $res);
        
        // delete
        $res = $this->request($endpoint = '/api/v2/customers/'.$data['id'], 'DELETE');
        $this->printResult($endpoint, 'delete', $res);
        
        // list
        $res = $this->request($endpoint = '/api/v2/customers', 'GET');
        $this->printResult($endpoint, 'list', $res);
        
        return $this;
    }
    
    public function testPictures()
    {
        // list
        $res = $this->request($endpoint = '/api/v2/pictures');
        $this->printResult($endpoint, 'list', $res);
        
        // one
        $res = $this->request($route = $endpoint.'/'.$res->getOneFromList()['id']);
        $this->printResult($route, 'resource', $res);
        
        return $this;
    }
    
    public function testLocations()
    {
        // list
        $res = $this->request($endpoint = '/api/v2/locations');
        $this->printResult($route, 'list', $res);
        
        // one
        $res = $this->request($route = $endpoint.'/'.$res->getOneFromList()['id']);
        $this->printResult($route, 'resource', $res);
        
        return $this;
    }

    public function testVats()
    {
        // list
        $res = $this->request($endpoint = '/api/v2/vats');
        $this->printResult($route, 'list', $res);
        
        // one
        $res = $this->request($route = $endpoint.'/'.$res->getOneFromList()['id']);
        $this->printResult($route, 'resource', $res);
        
        return $this;
    }

    public function testPrices()
    {
        // list
        $res = $this->request($endpoint = '/api/v2/prices');
        $this->printResult($route, 'list', $res);
        
        // one
        $res = $this->request($route = $endpoint.'/'.$res->getOneFromList()['id']);
        $this->printResult($route, 'resource', $res);
        
        return $this;
    }
    
    public function testMetaEvents()
    {
        // list
        $res = $this->request($endpoint = '/api/v2/metaevents');
        $this->printResult($route, 'list', $res);
        
        // one
        $res = $this->request($route = $endpoint.'/'.$res->getOneFromList()['id']);
        $this->printResult($route, 'resource', $res);
        
        return $this;
    }
    
    public function testMetaGauges()
    {
        // list
        $res = $this->request($route = $endpoint = '/api/v2/metagauges');
        $this->printResult($route, 'list', $res);
        
        // one
        $res = $this->request($route = $endpoint.'/'.$res->getOneFromList()['id']);
        $this->printResult($route, 'resource', $res);
        
        return $this;
    }
    
    public function testGauges()
    {
        // base manif
        $res = $this->request($endpoint = '/api/v2/manifestations');
        $this->printResult($endpoint, 'list', $res);
        
        // list
        $found = false;
        for ( $i = 0 ; !$found && $i < 10 ; $i++ ) {
            $res = $this->request($route = $endpoint = '/api/v2/manifestations/'.$res->getOneFromList()['id'].'/gauges');
            $this->printResult($route, 'list', $res);
            $found = count($res->getData(true)['_embedded']['items']) > 0;
        }
        // failure by lack of data
        if ( !$found ) {
            echo "The test failed due to no foundable gauge.\n\n";
            return $this;
        }
        
        $gauge = $res->getOneFromList();
        
        // base workspace
        $ws = $this->request($tmp = '/api/v2/metagauges?criteria[id][type]=not equal&criteria[id][value]='.$gauge['metaGaugeId']);
        $this->printResult($tmp, 'search gauge', $res);
        
        // one
        $res = $this->request($route = $endpoint.'/'.$gauge['id']);
        $this->printResult($route, 'resource', $res);
        
        // update
        $res = $this->request($route = $endpoint.'/'.$gauge['id'], 'POST', ['total' => $gauge['total'] + rand(1,10)*(rand(0,1)*2-1)]);
        $this->printResult($route, 'update', $res);
        
        // one
        $res = $this->request($route = $endpoint.'/'.$gauge['id']);
        $this->printResult($route, 'resource', $res);
        
        // create
        $res = $this->request($route = $endpoint, 'POST', [
            'metaGaugeId'       => 3,
            'total'             => 42,
            'manifestationId'   => 20,
        ]);
        $this->printResult($route, 'create', $res);
        
        $gauge = $res->getData(true);
        
        // add price to gauge
        $prices = $this->request('/api/v2/prices');
        $res = $this->request($route = $endpoint.'/'.$gauge['id'].'/price', 'POST', [
            'priceId' => $price_id = $prices->getOneFromList()['id'],
            'value'   => NULL,
        ]);
        $this->printResult($route, 'add price', $res);
        
        // one
        $res = $this->request($route = $endpoint.'/'.$gauge['id']);
        $this->printResult($route, 'resource', $res);
        
        // remove price from gauge
        $res = $this->request($route = $endpoint.'/'.$gauge['id'].'/price/'.$price_id, 'DELETE');
        $this->printResult($route, 'remove price', $res);
        
        // one
        $res = $this->request($route = $endpoint.'/'.$gauge['id']);
        $this->printResult($route, 'resource', $res);
        
        // delete
        $res = $this->request($route = $endpoint.'/'.$gauge['id'], 'DELETE');
        $this->printResult($route, 'delete', $res);
        
        // one
        $res = $this->request($route = $endpoint.'/'.$gauge['id']);
        $this->printResult($route, 'resource', $res);
        
        return $this;
    }

    public function testManifestations()
    {
        // list
        $res = $this->request($route = $endpoint = '/api/v2/manifestations');
        $this->printResult($route, 'list', $res);
        
        // one
        $res = $this->request($route = $endpoint.'/'.$res->getOneFromList()['id']);
        $this->printResult($route, 'resource', $res);
        
        // get available events
        $events = $this->request($route = '/api/v2/metaevents');
        // get available events
        $locations = $this->request($route = '/api/v2/locations');
        // get available events
        $vats = $this->request($route = '/api/v2/vats');
        
        // create
        $manif = [
            'startsAt'      => str_replace('-', 'T', date('Ymd-HisP', strtotime('+3 weeks'))),
            'endsAt'        => str_replace('-', 'T', date('Ymd-HisP', strtotime('+3 weeks + 1 hour'))),
            'eventId'       => $events->getOneFromList()['id'],
            'locationId'    => $locations->getOneFromList()['id'],
            'vatId'         => $vats->getOneFromList()['id'],
        ];
        $res = $this->request($route = $endpoint, 'POST', $manif);
        $this->printResult($route, 'create', $res);
        $manifid = $res->getData(true)['id'];
        
        // add a gauge
        $res = $this->request($route = '/api/v2/manifestations/'.$manifid.'/gauges', 'POST', [
            'metaGaugeId'       => 3,
            'total'             => 42,
            'manifestationId'   => $manifid,
        ]);
        $this->printResult($route, 'create gauge', $res);
        
        // get one
        $res = $this->request($route = $endpoint.'/'.$manifid);
        $this->printResult($route, 'resource', $res);
        
        // update
        $res = $this->request($route = $endpoint.'/'.$manifid, 'POST', [
            'endsAt'        => str_replace('-', 'T', date('Ymd-HisP', strtotime('+3 weeks + 1 hour 30 minutes'))),
            'locationId'    => $locations->getOneFromList()['id'],
        ]);
        $this->printResult($route, 'update', $res);
        
        // remove price from manifestation
        $res = $this->request($route = $endpoint.'/'.$manifid.'/price/'.$price_id, 'DELETE');
        $this->printResult($route, 'remove price', $res);
        
        // get one
        $res = $this->request($route = $endpoint.'/'.$manifid);
        $this->printResult($route, 'resource', $res);
        
        // add price to manifestation
        $prices = $this->request('/api/v2/prices');
        $res = $this->request($route = $endpoint.'/'.$manifid.'/price', 'POST', [
            'priceId' => $price_id = $prices->getOneFromList()['id'],
            'value'   => NULL,
        ]);
        $this->printResult($route, 'add price', $res);

        // get one
        $res = $this->request($route = $endpoint.'/'.$manifid);
        $this->printResult($route, 'resource', $res);

        // delete
        $res = $this->request($route = $endpoint.'/'.$manifid, 'DELETE');
        $this->printResult($route, 'delete', $res);
        
        // get one
        $res = $this->request($route = $endpoint.'/'.$manifid);
        $this->printResult($route, 'resource', $res);
        
        return $this;
    }
    
    public function testEvents()
    {
        // list
        $res = $this->request($endpoint = '/api/v2/events');
        $this->printResult($endpoint, 'list', $res);
        
        // one
        $res = $this->request($route = $endpoint.'/'.$res->getOneFromList()['id']);
        $this->printResult($route, 'resource', $res);
        
        // get one meta-event
        $metaevents = $this->request($route = '/api/v2/metaevents');
        
        // get one picture
        $pics = $this->request($route = '/api/v2/pictures');
        
        // create
        $event = [
            'metaEvent' => [ 'id' => $metaevents->getOneFromList()['id'] ],
            'translations' => [
                'fr' => [ 'name' => 'MonTest' ],
                'en' => [ 'name' => 'MyTest' ],
            ],
            'imageId' => $pics->getOneFromList()['id'],
        ];
        $res = $this->request($route = $endpoint, 'POST', $event);
        $this->printResult($route, 'create', $res);
        
        // get one
        $res = $this->request($route = $endpoint.'/'.$res->getData(true)['id']);
        $this->printResult($route, 'resource', $res);
        
        // update
        $res = $this->request($route = $endpoint.'/'.$res->getData(true)['id'], 'POST', ['imageId' => $pics->getOneFromList()['id']]);
        $this->printResult($route, 'update', $res);
        
        // get one
        $res = $this->request($route = $endpoint.'/'.$res->getData(true)['id']);
        $this->printResult($route, 'resource', $res);
        
        // delete
        $res = $this->request($route = $endpoint.'/'.$res->getData(true)['id'], 'DELETE');
        $this->printResult($route, 'delete', $res);
        
        return $this;
    }
    
    protected function printResult($endpoint, $action, HTTPResult $result)
    {
        if ( $this->show_results ) {
            echo $result->getData()."\n";
        }
        echo $this->lastCurlEquivalent."\n\n";
        $this->lastCurlEquivalent = '';
        
        echo "$endpoint | $action | ";
        echo !$result->isSuccess() ? 'ERROR' : 'SUCCESS';
        echo ': HTTP '.$result->getStatus();
        
        echo "\n\n\n";
        return $this;
    }
    
    protected function request($uri, $method = 'GET', array $data = [])
    {
        $ch = curl_init($this->base.$uri);
        
        if ( $data ) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $headers = ['Content-Type: application/json'];
        if ( $this->token ) {
            $headers[] = 'Authorization: Bearer '.$this->token;
        }
        
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_CUSTOMREQUEST  => $method,
        ]);
        
        $h = implode('" -H "', $headers);
        $this->lastCurlEquivalent = sprintf('$ curl -k "%s" -H "%s" -X %s %s',
            str_replace(['[', ']', ' '], ['\\[', '\\]', '%20'], $this->base.$uri),
            $h,
            $method,
            $data ? "--data '".json_encode($data)."'" : ''
        );
        
        $res = new HTTPResult($ch);
        curl_close($ch);
        return $res;
    }
}

class HTTPResult
{
    protected $data;
    protected $status;
    protected $resource;
    
    public function __construct($curl_resource)
    {
        $this->resource = $curl_resource;
        $this->data = curl_exec($curl_resource);
        $this->status = curl_getinfo($curl_resource, CURLINFO_HTTP_CODE);
    }
    
    public function getCurlResource()
    {
        return $this->resource;
    }
    
    public function getData($json = false)
    {
            return $json ? json_decode($this->data, true) : $this->data;
    }
    
    public function getStatus()
    {
        return $this->status;
    }
    
    public function isSuccess()
    {
        return $this->status < 400;
    }
    
    public function __toString()
    {
        return is_array($this->data) ? json_encode($this->data, JSON_PRETTY_PRINT) : $this->data;
    }

    /**
     * Returns one entity from a list of entities
     *
     * $i   integer if < 0, it means that we are expecting a random result
     */
    public function getOneFromList($i = -1)
    {
        $data = $this->getData(true)['_embedded']['items'];
        return $i < 0 ? $data[rand(0, count($data)-1)] : $data[$i];
    }
}
