<?php

// ARG #1: base url
// ARG #2: tests (comma separated list) to execute
// ARG #2: identifier
// ARG #3: secret
// ARG #4: debug? (optional, no debug by default)

$test = new Test($_SERVER['argv'][1], $_SERVER['argv'][3], $_SERVER['argv'][4], isset($_SERVER['argv'][5]));
$test->executeTests($_SERVER['argv'][2]);
//$test->testPictures();
//$test->testEvents();

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
        $this->printResult($endpoint, 'token', $res);
        
        $json = json_decode($res->getData(), true);
        $this->token = $json['access_token'];
        
        return $this;
    }
    
    public function testCustomers()
    {
        // create
        $customer = [
            'email' => 'baptiste.simon@test.tld',
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
        $data = $res->getData(true)['_embedded']['items'];
        $res = $this->request($endpoint = '/api/v2/pictures/'.$this->getOneFromList($data, rand(0, count($data)-1))['id']);
        $this->printResult($endpoint, 'display', $res);
        
        return $this;
    }
    
    protected function getOneFromList($data, $i = 0)
    {
        return $data['_embedded']['items'][$i];
    }
    
    protected function printResult($endpoint, $action, HTTPResult $result)
    {
        echo "$endpoint | $action | ";
        echo !$result->isSuccess() ? 'ERROR' : 'SUCCESS';
        echo ': HTTP '.$result->getStatus();
        
        if ( $this->show_results ) {
            echo "\n".$result->getData();
            echo "\n".$this->lastCurlEquivalent;
            $this->lastCurlEquivalent = '';
        }
        
        echo "\n\n";
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
        $this->lastCurlEquivalent = sprintf('$ curl -k "%s" -H "%s" %s',
            $this->base.$uri,
            $h,
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
}
