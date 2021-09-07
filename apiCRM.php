<?php
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Slim\Routing\RouteContext;

include "funcionesCRM.php";
include "configCRM.php";

$app->group('/CRM', function (Group $group) {    
    $group->get('', function ($request, $response){

        $makeQuery = makeQuery("miquerito");

        $payload = json_encode($makeQuery);
       $response->getBody()->write($payload);        
        return $response->withHeader('Content-Type', 'application/json');
    });
    $group->get('/query', function ($request, $response){
        if(isset($_GET['q'])){
            $query = $_GET['q'];
            $makeQuery = makeQuery(urlencode($query) );
            $payload = json_encode($makeQuery);
        }else{
            $payload = json_encode(array(array("errorCode" => -1, "errorDescription" =>"there are no parameters to try to get")));
        }
        $response->getBody()->write($payload);        
        return $response->withHeader('Content-Type', 'application/json');
    });
    $group->get('/accounts/{id}', function ($request, $response){
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();        
        $accId = $route->getArgument('id');
       
        $account = getAccount($accId);
        if(isset($account["attributes"])){
            $payload = json_encode(array("code" => 0,"data" => $account));
            $response->getBody()->write($payload);        
        }else{
            $payload = json_encode(array("code" => -1,"data" => $account));
            $response->getBody()->write($payload);        
        }
        return $response->withHeader('Content-Type', 'application/json');
    });
    $group->post('/{sobject}', function ($request, $response){
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();        
        $sObject = $route->getArgument('sobject');
       
        $contents = json_decode(file_get_contents('php://input'), true);
        $params = (array)$contents;

        if (in_array(ucfirst($sObject), getOjsBlocked())) {
            $payload = json_encode(array("code" => -1,"data" => "The ".ucfirst($sObject)." table is not able to be modified"));
            $response->getBody()->write($payload); 
            return $response->withHeader('Content-Type', 'application/json');
        }

        $postObject = postSObject($sObject, $params);

        if(isset($postObject["success"])){
            $payload = json_encode(array("code" => 0,"data" => $postObject));
            $response->getBody()->write($payload);        
        }else{
            $payload = json_encode(array("code" => -1,"data" => $postObject));
            $response->getBody()->write($payload);        
        }
        return $response->withHeader('Content-Type', 'application/json');
    });
    // putSObject
    $group->put('/{sobject}/{id}', function ($request, $response){
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();        
        $sObject = $route->getArgument('sobject');
        $sObjectID = $route->getArgument('id');
       
        $contents = json_decode(file_get_contents('php://input'), true);
        $params = (array)$contents;

        if (in_array(ucfirst($sObject), getOjsBlocked())) {
            $payload = json_encode(array("code" => -1,"data" => "The ".ucfirst($sObject)." table is not able to be modified"));
            $response->getBody()->write($payload); 
            return $response->withHeader('Content-Type', 'application/json');
        }
        $putObject = putSObject($sObject,$sObjectID,$params);

        if(count($putObject) == 0){
            $payload = json_encode(array("code" => 0,"data" => $putObject));
            $response->getBody()->write($payload);        
        }else{
            $payload = json_encode(array("code" => -1,"data" => $putObject));
            $response->getBody()->write($payload);        
        }
        return $response->withHeader('Content-Type', 'application/json');
    });
    $group->get('/{sobject}/{id}', function ($request, $response){
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();        
        $sObject = $route->getArgument('sobject');
        $sObjectId = $route->getArgument('id');
       
        if (in_array(ucfirst($sObject), getOjsBlocked())) {
            $payload = json_encode(array("code" => -1,"data" => "The ".ucfirst($sObject)." table is not able to be modified"));
            $response->getBody()->write($payload); 
            return $response->withHeader('Content-Type', 'application/json');
        }
        $getCustomObject = getCustomSObject($sObject,$sObjectId);
        
        if(isset($getCustomObject["attributes"])){
            $getCustomObject["attributes"]->url = null;
            $payload = json_encode(array("code" => 0,"data" => $getCustomObject));
            $response->getBody()->write($payload);        
        }else{
            $payload = json_encode(array("code" => -1,"data" => $getCustomObject));
            $response->getBody()->write($payload);        
        }
        return $response->withHeader('Content-Type', 'application/json');
    });
});


?>