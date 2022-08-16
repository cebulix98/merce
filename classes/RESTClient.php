<?php

namespace Class;

class RESTClient
{

    private $requestType;
    private $bodyType;
    private $authorizationType;
    private $authorization;
    private $body;
    private $headers;
    private $url;
    private $request;

    //SETTERS
    public function SetRequestType(String $type)
    {
        $requestTypes = ['POST', 'GET', 'DELETE', 'PUT', 'PATCH', 'HEAD', 'OPTIONS'];

        if (!in_array($type, $requestTypes)) {
            throw new \InvalidArgumentException('Invalid request type. available types: ' . implode(', ', $requestTypes));
        } else {
            $this->requestType = $type;
        }
    }

    public function SetBodyType(String $type)
    {
        $bodyTypes = ['form', 'json', 'url'];

        if (!in_array($type, $bodyTypes)) {
            throw new \InvalidArgumentException('Invalid body type. Available types: ' . implode(', ', $bodyTypes));
        } else {
            $this->bodyType = $type;
        }
    }

    public function setAuthorizationType(String $type)
    {
        $authorizationTypes = ['basic', 'bearer'];

        if (!in_array($type, $authorizationTypes)) {
            throw new \InvalidArgumentException('Invalid authorization type. Available types : ' . implode(',', $authorizationTypes));
        } else {
            $this->authorizationType = $type;
        }
    }

    public function setHeaders(array $headers)
    {
        if (array_search('Content-Type:', $headers) === true) {
            throw new \InvalidArgumentException('Content-Type header cannot be changed');
        } else {
            $this->headers = $headers;
        }
    }

    public function SetAuthorization($authorization)
    {
        if (empty($this->authorizationType)) {
            throw new \Exception('You must set authorization type before setting authorization data');
        }

        if ($this->authorizationType === 'basic') {
            if (gettype($authorization) === 'array' && isset($authorization['user'], $authorization['password'])) {
                $this->authorization = "{$authorization['user']} : {$authorization['password']}";
            }
        }

        if (gettype($authorization) !== 'string') {
            throw new \InvalidArgumentException('Authorization code must be a string');
        }
    }

    public function setUrl(String $url)
    {

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('url is not valid');
        }

        $this->url = $url;
    }

    public function setBody($body)
    {
        if ($this->requestType === 'POST' && gettype($body) !== 'array') {
            throw new \InvalidArgumentException('Body is not an array');
        } else {
            $this->body = $body;
        }
    }

    //METHODS
    private function parseBody()
    {
        switch ($this->bodyType) {
            case 'JSON':
                return json_encode($this->body);
                break;
            default:
                return $this->body;
        }
    }

    private function makeHeaders()
    {
        $headers = [];
        switch ($this->bodyType) {
            case 'JSON':
                $headers[] = 'Content-Type: application/json';
                break;
            case 'form':
                $headers[] = 'Content-Type: application/x-www-form-urlencoded\r\n';
                break;
            default:
                break;
        }

        foreach ($this->headers as $key => $value) {
            $headers[] = "{$key}: {$value}";
        }
    }

    private function setMethod()
    {
        switch ($this->requestType) {
            case 'POST':
                curl_setopt($this->request, CURLOPT_POST, true);
                break;
            case 'PUT':
                curl_setopt($this->request, CURLOPT_PUT, true);
                break;
            default:
                break;
        }
    }

    private function makeGETQuery()
    {
        if (!empty($this->body)) {
            return gettype($this->body) === 'array' ? http_build_query($this->body) : $this->body;
        } else {
            return '';
        }
    }

    private function makeAuthorization()
    {
        if ($this->authorizationType === 'basic') {
            curl_setopt($this->request, CURLOPT_USERPWD, $this->authorization);
        } elseif ($this->authorizationType === 'bearer') {
            curl_setopt($this->request, CURLOPT_HTTPAUTH, CURLAUTH_BEARER);
            curl_setopt($this->request, CURLOPT_XOAUTH2_BEARER, $this->authorization);
        }
    }

    public function sendRequest()
    {
        if (empty($this->url)) {
            throw new \Exception('You must provide url before sending request');
        }

        $this->request = curl_init();

        $this->makeAuthorization();

        if ($this->requestType == 'GET') {
            curl_setopt($this->request, CURLOPT_URL, $this->url . $this->makeGETQuery());
        } else {
            curl_setopt($this->request, CURLOPT_URL, $this->url);
            curl_setopt($this->request, CURLOPT_POSTFIELDS, $this->parseBody());
            curl_setopt($this->request, CURLOPT_HEADER, $this->makeHeaders());
            $this->setMethod();
        }

        curl_exec($this->request);

        return $this->request;
        $this->destroy;
    }
}
