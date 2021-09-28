<?php
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Slim\Routing\RouteContext;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

include "funcionesCRM.php";
include "configCRM.php";

// function mw1($request, $response, $next){
//     // if(!isset($request->getHeaders()["Authorization"]))){
//     //     $payload = json_encode(array(array("errorCode" => -1, "errorDescription" =>"There is not Authorization Header")));
//     //     $response->getBody()->write($payload);          
//     //     return $response->withHeader('Content-Type', 'application/json');
//     // }
//     $response->getBody()->write('BEFORE');
//     $response = $next($request, $response);
//     $response->getBody()->write('AFTER');
//     return $response->withHeader('Content-Type', 'application/json');
// }



$app->group('/CRM', function (Group $group) {    
    //middleware para evitar que no se agrege el authorization
    $mw = function (Request $request, RequestHandler $handler) {
        $response = $handler->handle($request);
        
        if(isset($request->getHeaders()["Authorization"])){
            return $response;
        }else{
            $response1 = new Response();
            $response1->getBody()->write( json_encode(array("errorCode" => -1, "errorDescription" =>"There is not Authorization Header")));
            return $response1->withHeader('Content-Type', 'application/json');
        }   
        
    };
    $mw2 = function (Request $request, RequestHandler $handler) {
        $response = $handler->handle($request);        
        if(checkForDomain()){
            return $response;
        }else{
            $response1 = new Response();
            $response1->getBody()->write( json_encode(array("errorCode" => -1, "errorDescription" =>"There is not authorization for this domain")));
            return $response1->withHeader('Content-Type', 'application/json');
        }        
    };

    $group->get('', function ($request, $response){
        $makeQuery = makeQuery("miquerito");

        $payload = json_encode($makeQuery);
       $response->getBody()->write($payload);        
        return $response->withHeader('Content-Type', 'application/json');
    });


    // SELECT Id,RRC_Date__c,RRC_MaxAppointments__c,RRC_Remaining_appointments__c,RRC_SalesRoom__c,RRC_Time__c,RRC_TotalAppointments__c,RRC_WaveDescription__c,RRC_Wave__c FROM RRC_Wave__c WHERE RRC_MaxAppointments__c > 0 AND RRC_Remaining_appointments__c >= 1 AND RRC_Date__c > 2019-11-24 AND RRC_Date__c < 2019-11-27
    $group->post('/getWaves', function ($request, $response, $args){
        $contents = json_decode(file_get_contents('php://input'), true);
        $params = (array)$contents;        

        if(isset($params["checkIn"]) && isset($params["checkOut"]) && isset($params["resort"])){
            $query = $params;
            $checkIN = $query["checkIn"];
            $checkOut = $query["checkOut"];
            $resort = $query["resort"];            
            $query = "SELECT Id,RRC_Date__c,RRC_MaxAppointments__c,RRC_Remaining_appointments__c,RRC_SalesRoom__c,RRC_Time__c,RRC_TotalAppointments__c,RRC_WaveDescription__c,RRC_Wave__c FROM RRC_Wave__c WHERE RRC_MaxAppointments__c > 0 AND RRC_Remaining_appointments__c >= 1 AND RRC_Date__c > $checkIN AND RRC_Date__c < $checkOut  AND RRC_SalesRoom__c = '$resort'";

            $makeQuery = makeQuery(urlencode($query),$request->getHeaders()["Authorization"]);
            $payload = json_encode($makeQuery);

            if(isset($makeQuery["done"])){
                $payload = json_encode(array("code" => 0,"data" => $makeQuery));
            }else{
                $payload = json_encode(array("code" => -1,"data" => $makeQuery));
            }
        }else{
            $payload = json_encode(array("code" => -1, "errorDescription" =>"there are no parameters to try to get"));
        }
        $response->getBody()->write($payload);        
        return $response->withHeader('Content-Type', 'application/json');
    })->add($mw);


    $group->post('/setPreferences', function ($request, $response, $args){
        $contents = json_decode(file_get_contents('php://input'), true);
        $params = (array)$contents;        
        if($params === null || ( !isset($params["RRC_Account__c"]) && !isset($params["PersonContactId"])) ||  !isset($params["RRC_PreferenceType__c"]) || !isset($params["records"])){
            $payload = json_encode(array("code" => -1, "errorDescription" =>"there are no parameters"));
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json');
        }
        if(count($params) > 0){
            $prueba = makeMultipleDeletesForPreferences($params,$request->getHeaders()["Authorization"]);
            $payload = json_encode($prueba); 
        }else{
            $payload = json_encode(array("code" => -1, "errorDescription" =>"there are no parameters to try to get"));
        }
        $response->getBody()->write($payload);        
        return $response->withHeader('Content-Type', 'application/json');
    })->add($mw);






    $group->get('/pruebaToken', function ($request, $response, $args){
        $contents = json_decode(file_get_contents('php://input'), true);
        $params = (array)$contents;        
        $response->getBody()->write("en ruta");
        return $response;
    })->add($mw);


    $group->post('/getToken', function ($request, $response){        
        $contents = json_decode(file_get_contents('php://input'), true);
        $params = (array)$contents;
        if(isset($params["user"]) && isset($params["password"])){
            $token = getCustomTokenCRM($params["user"],$params["password"]);
            if($token["code"] === 0){
                $payload = json_encode(array("code" => 0,"token" => $token));
            }else{
                $payload = json_encode(array("code" => $token["code"],"token" => $token["codeDesc"]));
            }
        } else{
            $payload = json_encode(array("code" => -1, "errorDescription" =>"Missing params ['user' or 'password']"));
        }
        $response->getBody()->write($payload);        
        return $response->withHeader('Content-Type', 'application/json');
    });
    $group->post('/getTokenByServer', function ($request, $response){        
        $payload = json_encode(getTokenCRM());
        $response->getBody()->write($payload);        
        return $response->withHeader('Content-Type', 'application/json');
    })->add($mw2);

    $group->get('/query', function ($request, $response){
        if(isset($_GET['q'])){
            $query = $_GET['q'];
            $makeQuery = makeQuery(urlencode($query),$request->getHeaders()["Authorization"]);
            $payload = json_encode($makeQuery);
        }else{
            $payload = json_encode(array(array("errorCode" => -1, "errorDescription" =>"there are no parameters to try to get")));
        }
        $response->getBody()->write($payload);        
        return $response->withHeader('Content-Type', 'application/json');
    })->add($mw);
    // $group->get('/accounts/{id}', function ($request, $response){
    //     $routeContext = RouteContext::fromRequest($request);
    //     $route = $routeContext->getRoute();        
    //     $accId = $route->getArgument('id');
       
    //     $account = getAccount($accId);
    //     if(isset($account["attributes"])){
    //         $payload = json_encode(array("code" => 0,"data" => $account));
    //         $response->getBody()->write($payload);        
    //     }else{
    //         $payload = json_encode(array("code" => -1,"data" => $account));
    //         $response->getBody()->write($payload);        
    //     }
    //     return $response->withHeader('Content-Type', 'application/json');
    // });
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

        $postObject = postSObject($sObject, $params, $request->getHeaders()["Authorization"]);

        if(isset($postObject["success"])){
            $payload = json_encode(array("code" => 0,"data" => $postObject));
            $response->getBody()->write($payload);        
        }else{
            $payload = json_encode(array("code" => -1,"data" => $postObject));
            $response->getBody()->write($payload);        
        }
        return $response->withHeader('Content-Type', 'application/json');
    })->add($mw);
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
        $putObject = putSObject($sObject,$sObjectID,$params,$request->getHeaders()["Authorization"]);

        if(count($putObject) == 0){
            $payload = json_encode(array("code" => 0,"data" => $putObject));
            $response->getBody()->write($payload);        
        }else{
            $payload = json_encode(array("code" => -1,"data" => $putObject));
            $response->getBody()->write($payload);        
        }
        return $response->withHeader('Content-Type', 'application/json');
    })->add($mw);
    $group->get('/{sobject}/{id}', function ($request, $response){
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();        
        $sObject = $route->getArgument('sobject');
        $sObjectId = $route->getArgument('id');
       
        if(!isset($request->getHeaders()["Authorization"])){
            $payload = json_encode(array("code" => -1,"data" => "There is no Authorizationssss"));
            $response->getBody()->write($payload); 
            return $response->withHeader('Content-Type', 'application/json');
        }

        if (in_array(ucfirst($sObject), getOjsBlocked())) {
            $payload = json_encode(array("code" => -1,"data" => "The ".ucfirst($sObject)." table is not able to be modified"));
            $response->getBody()->write($payload); 
            return $response->withHeader('Content-Type', 'application/json');
        }
        $getCustomObject = getCustomSObject($sObject,$sObjectId,$request->getHeaders()["Authorization"]);
        
        if(isset($getCustomObject["attributes"])){
            $getCustomObject["attributes"]->url = null;
            $payload = json_encode(array("code" => 0,"data" => $getCustomObject));
            $response->getBody()->write($payload);        
        }else{
            $payload = json_encode(array("code" => -1,"data" => $getCustomObject));
            $response->getBody()->write($payload);        
        }
        return $response->withHeader('Content-Type', 'application/json');
    })->add($mw);
});


?>