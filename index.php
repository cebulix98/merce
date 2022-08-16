<?php 

    require(__DIR__."/vendor/autoload.php");

    use Class\RESTClient;

    $client = new RESTClient;

    $client->setUrl('https://api.zippopotam.us/');
    $client->setBody('us/33162');
    $client->setRequestType('GET');
    $res = $client->sendRequest('GET');
    var_dump($res);