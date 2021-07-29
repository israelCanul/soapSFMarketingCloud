<?php
require __DIR__ . '/vendor/autoload.php';
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteContext;
use FuelSdk\ET_Client;
use FuelSdk\ET_List_Subscriber;
use FuelSdk\ET_List;
use FuelSdk\ET_Subscriber;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use FuelSdk\ET_Email;
use FuelSdk\ET_Asset;

use FuelSdk\ET_DataExtension;
use FuelSdk\ET_DataExtension_Row;

include "funcionesSF.php";

$app = AppFactory::create();
/**
 * Se agrega el middleware para el control de errores
 * [INICIO]
 */
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setErrorHandler(
    HttpNotFoundException::class,
    function (ServerRequestInterface $request, Throwable $exception, bool $displayErrorDetails) {
        $response = new Response();
        $response->getBody()->write('404 NOT FOUND');

        return $response->withStatus(404);
    });
$errorMiddleware->setErrorHandler(
    HttpMethodNotAllowedException::class,
    function (ServerRequestInterface $request, Throwable $exception, bool $displayErrorDetails) {
    
        $response = new Response();
        $response->getBody()->write('405 NOT ALLOWED');

        return $response->withStatus(405);
    });
/**
 * Se agrega el middleware para el control de errores
 * [FINAL]
 */


$app->get('/', function (Request $request, Response $response, $args) {
    $methods = array(
        array("endpoint"=>"/subscribers","description"=>"Obtener las listas dentro de las que esta agregado un usuario","request" => array("method"=>"POST","Content-Type"=>"application/json","params" => array("subscriberKey"=>"subscriberKey [Type: string]","lists"=>"list of ListID [Type: array<string>]"))),
        array("endpoint"=>"/publicationLists","description"=>"obtener la lista de publication lists","request" => array("method"=>"GET","Content-Type"=>"application/json")),
        array("endpoint"=>"/publicationLists","description"=>"cambiar el status de un subscriptor (Unsubscribe From All)","request" => array("method"=>"POST","Content-Type"=>"application/json","params" => array("subscriberKey"=>"subscriberKey [Type: string]","status"=>"Status [Type: string]"))),
        array("endpoint"=>"/publicationLists/{id}","description"=>"obtener una publication list en especifica","request" => array("method"=>"GET","Content-Type"=>"application/json")),
        array("endpoint"=>"/publicationLists/{id}","description"=>"cambiar el status de un subscriptor en la lista especifica","request" => array("method"=>"POST","Content-Type"=>"application/json","params" => array("subscriberKey"=>"subscriberKey [Type: string]","status"=>"Status [Type: string]"))),
        array("endpoint"=>"/emails/sendMessage","description"=>"Send a Transactional Message","request" => array("method"=>"POST","Content-Type"=>"application/json","params" => array("messageKey [Type: string]"=>"JHGAJDA","keyDefinition [Type: string]"=>"APIHooktourEliteMessage","contactKey [Type: string]"=>"Unique identifier for a subscriber in Marketing Cloud [Example] icanul@royalresorts.com","to [Type: string]"=> "icanul@royalresorts.com","vars [Type: Object]"=>array("fname [example]"=> "iran","lname [example]"=> "canul","certificateID [example]"=> "CERTIFICATE","purchasedate [example]" => "5/12/2021 12:00:00 AM")))),
        array("endpoint"=>"/dtExtensions","description"=>"Obtener la lista de data extensions segun la cuenta","request" => array("method"=>"GET","Content-Type"=>"application/json")),
        array("endpoint"=>"/dtExtensions/{customerKey}","operaciones"=>"operaciones permitidas  'equals, IN' ","description"=>"obtener la lista de rows de una DT segun los campos pasados","request" => array("method"=>"GET","Content-Type"=>"application/json"))
    );
    $payload = json_encode($methods);
    $response->getBody()->write($payload);        
    return $response->withHeader('Content-Type', 'application/json');
});

$app->group('/keyDefinitions', function (Group $group) {
    $group->get('', function ($request, $response){
        $page = "1";
        if(isset($_GET['page'])){
            $page = $_GET['page'];
        }
        $respuestaGet = getEmailDefinitions($page);
        if(!isset($respuestaGet["error"])){
            $response->getBody()->write($respuestaGet);        
            return $response->withHeader('Content-Type', 'application/json');
        }else{
            $response->getBody()->write("[]");        
            return $response->withHeader('Content-Type', 'application/json');
        }
    });
    $group->post('', function ($request, $response){
        $contents = json_decode(file_get_contents('php://input'), true);
        $params = (array)$contents;

        $respuestaGet = EmailDefinitions($params["definitionKey"], $params["name"], $params["customerKey"], $params["list"], $params["dataExtension"]);

        if(!isset($respuestaGet["error"])){
            $response->getBody()->write(var_dump($respuestaGet));        
            return $response->withHeader('Content-Type', 'application/json');
        }else{
            $response->getBody()->write("[]");        
            return $response->withHeader('Content-Type', 'application/json');
        }

    });
});
$app->group('/emails', function (Group $group) {
    $group->post('/sendMessage', function ($request, $response){
        $contents = json_decode(file_get_contents('php://input'), true);
        $params = (array)$contents;
        // $response->getBody()->write(var_dump($contents));        
        // return $response->withHeader('Content-Type', 'application/json');
        // exit(0);
        if(!isset($params["messageKey"]) && !isset($params["keyDefinition"]) && !isset($params["to"])){
            $payload = json_encode(array("errorCode" => 0, "errorDescription" => "Parameters Missing"));
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json');
        }

        $vars = array();
        if(isset($params["vars"])){
            foreach ($params["vars"] as $key => $value) {
                $vars[$key] = $value;
            }
        }
        $sendResponse = SendMessage(rand_string(30).$params["messageKey"],$params["keyDefinition"], $params["contactKey"],$params["to"], $vars);

        if(!isset($sendResponse["error"])){
            $response->getBody()->write(json_encode($sendResponse));        
            return $response->withHeader('Content-Type', 'application/json');
        }else{
            $response->getBody()->write(json_encode($sendResponse));        
            return $response->withHeader('Content-Type', 'application/json');
        }        
        // $response->getBody()->write(json_encode($vars));
        // return $response->withHeader('Content-Type', 'application/json');
        // //SendMessage(rand_string(30)."HJKL01", 'APIHooktourEliteMessage', $result[0]->Email, array("fname" => $result[0]->FName,"lname" => $result[0]->LName, "certificateID" => $result[0]->CertificateID, "purchasedate"=> date("m/d/y")));

        
    });

});



$app->group('/dtExtensions', function (Group $group) {
    $group->get('', function ($request, $response){
        $name = $_GET["name"];       
        $myclient = new ET_Client(true);
        $DT = new ET_DataExtension();
        $DT->authStub = $myclient;
        if($name != null){
            $DT->filter = array("Property"=>"Name", "SimpleOperator"=>"equals","Value"=>$name);
        }        
        $respuesta = $DT->get();


        if($respuesta->status == "true" || $respuesta->status == true){
            $payload = json_encode($respuesta);
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json');          
        }else{
            $payload = json_encode(array("errorCode" => -1, "errorDescription" => $respuesta->message));
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json');
        }

    });
    $group->get('/{customerkey}', function ($request, $response){
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $cKey = $route->getArgument('customerkey');
        $fields = $_GET["fields"];
        $query = $_GET["q"];
        if($cKey == null){
            $payload = json_encode(array("errorCode" => -1, "data" => $cKey, "errorDescription" => "This Endpoint only works with a customer key valid"));        
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json');      
        }
        if($fields == null){
            $payload = json_encode(array("errorCode" => -1, "data" => $cKey, "errorDescription" => "The endpoint needs a list of fields to retrieve", "type"=>"fields=<field1>,<field2>,...<field(n)>"));        
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json');      
        }
        $myclient = new ET_Client(true);
        $DT = new ET_DataExtension_Row();
        $DT->authStub = $myclient;
        $DT->props =  explode(",", $fields);
        $DT->CustomerKey = $cKey;
        if($query){
            $query = explode(",", $query);
            if(count($query) == 3){
                if($query[1] != "equals" && $query[1] != "IN"){
                    $payload = json_encode(array("errorCode" => -1, "CustomerKey" => $cKey, "errorDescription" => "Operation not allowed ".$query[1]));        
                    $response->getBody()->write($payload);        
                    return $response->withHeader('Content-Type', 'application/json');
                }
                $DT->filter = array("Property"=>$query[0], "SimpleOperator"=>$query[1],"Value"=>$query[2]);
            }
        }        
        $respuesta = $DT->get();

        if($respuesta->status == "true" || $respuesta->status == true){
            $results = array();
            foreach ($respuesta->results as $key => $value) {
                $props = array();
                foreach ($value->Properties->Property as $key1 => $value1) {
                    $props[$value1->Name] =$value1->Value;
                }
                array_push($results, $props);                
            }
            $respuesta->fields = explode(",", $fields);
            $respuesta->results = $results;                         
            $payload = json_encode($respuesta);
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json');             
        }else{
            $payload = json_encode(array("errorCode" => -1, "errorDescription" => $respuesta->message));
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json'); 
        }               
    });
});

$app->group('/subscribers', function (Group $group) {
    $group->get('', function ($request, $response){
        $payload = json_encode(array("errorCode" => 0, "errorDescription" => "This Endpoint only works in POST Merthod","Help"=>array("request" => array("method"=>"POST","Content-Type"=>"application/json","params" => array("subscriberKey"=>"subscriberKey [Type: string]","lists"=>"list of ListID [Type: array<string>]")))));
        $response->getBody()->write($payload);        
        return $response->withHeader('Content-Type', 'application/json');
    });
    $group->post('', function ($request, $response){
        //con este metodo traemos la lista de publication list a las que esta subscrito un subscriber 
        $contents = json_decode(file_get_contents('php://input'), true);
        $params = (array)$contents;
        if(isset($params["Help"]) || isset($params["help"])){
            $payload = json_encode(array("request" => array("method"=>"POST","Content-Type"=>"application/json","params" => array("subscriberKey"=>"subscriberKey [Type: string]","lists"=>"list of ListID [Type: array<string>]"))));
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json');
        }       
        
        if(!isset($params[$stringForSubscriber]) && !isset($params["lists"])){
            $payload = json_encode(array("errorCode" => 0, "errorDescription" => "Parameters Missing"));
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json');
        }

        $stringForSubscriber = "subscriberKey";
        $myclient = new ET_Client(true);
        $request = new ET_List_Subscriber();
        $request->authStub = $myclient;
           
        
        $left = array("Property"=>"ListID", "SimpleOperator"=>"IN","Value"=>$params["lists"]);
        $right = array("Property"=>"SubscriberKey", "SimpleOperator"=>"equals","Value"=>$params[$stringForSubscriber]);
        $request->filter = array("LeftOperand"=>$left, "LogicalOperator"=>"AND","RightOperand"=>$right);

        $respuesta = $request->get();
        //Sobreescribimos el resultado para solo regresar los necesario [INICIO]
        $listResult = array();
        if(count($respuesta->results) > 0){
            foreach ($respuesta->results as $key => $value) {
                array_push($listResult, $value);
            }
        }
        $respuesta->results = $listResult;
        //Sobreescribimos el resultado para solo regresar los necesario [FINAL]

        if($respuesta->status == true && $respuesta->code == 200){
            $payload = json_encode($respuesta->results);
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json');
        }else{
            $payload = json_encode(array("errorCode" => 0, "errorDescription" => $respuesta->message));
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json');
        }
    });
});




$app->group('/publicationLists', function (Group $group) {
    $group->post('', function ($request, $response){
        $contents = json_decode(file_get_contents('php://input'), true);
        $params = (array)$contents;

        if(isset($params["Help"]) || isset($params["help"])){
            $payload = json_encode(array("request" => array("method"=>"POST","Content-Type"=>"application/json","params" => array("subscriberKey"=>"subscriberKey [Type: string]","status"=>"Status [Type: string]"))));
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json');
        }
        if(!isset($params["subscriberKey"]) || !isset($params["status"])){
            $payload = json_encode(array("errorCode" => 0, "errorDescription" => "Parameters Missing"));
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json');
        } 

        $stringForSubscriber = "subscriberKey";
        $myclient = new ET_Client(true);
        $subPatch = new ET_Subscriber();
        $subPatch->authStub = $myclient;
        $subPatch->props = array("SubscriberKey"=>$params["subscriberKey"],"Status" => $params["status"]);
        if(isset($params["email"])){
            $subPatch->props = array("EmailAddress" =>$params["email"],"SubscriberKey"=>$params["subscriberKey"],"Status" => $params["status"]);
        }        
        $patchResult = $subPatch->patch();
        if (!$patchResult->status) {            
            $payload = json_encode(array("errorCode" => 0, "errorDescription" => $patchResult->message));
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json');
        }	
        $payload = json_encode($patchResult);
        $response->getBody()->write($payload);        
        return $response->withHeader('Content-Type', 'application/json');
    });
    $group->get('', function ($request, $response){
        // throw new Exception('División por cero.');
        $myclient = new ET_Client(true);
        $requestSF = new ET_List();
        $requestSF->authStub = $myclient;
        $requestSF->filter = array("Property"=>"ListClassification", "SimpleOperator"=>"equals","Value"=>"PublicationList");
        $respuesta = $requestSF->get();
        //Sobreescribimos el resultado para solo regresar los necesario [INICIO]
        $listResult = array();
        if(count($respuesta->results) > 0){
            foreach ($respuesta->results as $key => $value) {
                array_push($listResult,array(
                    "ID"=> $value->ID,
                    "CreatedDate"=> $value->CreatedDate,
                    "ModifiedDate"=> $value->ModifiedDate,
                    "ObjectID"=>$value->ObjectID,
                    "ListName"=>$value->ListName,
                    "Category"=>$value->Category,
                    "Type"=>$value->Type,
                    "Description"=>$value->Description,
                    "ListClassification"=>$value->ListClassification
                ));
            }
        }
        $respuesta->results = $listResult;
        //Sobreescribimos el resultado para solo regresar los necesario [FINAL]

        if($respuesta->status == true && $respuesta->code == 200){
            $payload = json_encode($respuesta->results);
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json');
        }else{
            $payload = json_encode(array("errorCode" => 0, "errorDescription" => $respuesta->message));
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json');
        }
    });
    $group->get('/{id}',function ($request, $response){

        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        
        $listId = $route->getArgument('id');

        $myclient = new ET_Client(true);
        $requestSF = new ET_List();
        $requestSF->authStub = $myclient;

        $left = array("Property"=>"ListClassification", "SimpleOperator"=>"equals","Value"=>"PublicationList");
        $right = array("Property"=>"ID", "SimpleOperator"=>"equals","Value"=>$listId);
        $requestSF->filter = array("LeftOperand"=>$left, "LogicalOperator"=>"AND","RightOperand"=>$right);

        $respuesta = $requestSF->get();

        if($respuesta->status == true && $respuesta->code == 200){
            //Sobreescribimos el resultado para solo regresar los necesario [INICIO]
            $listResult = array();
            if(count($respuesta->results) > 0){
                foreach ($respuesta->results as $key => $value) {
                    array_push($listResult,array(
                        "ID"=> $value->ID,
                        "CreatedDate"=> $value->CreatedDate,
                        "ModifiedDate"=> $value->ModifiedDate,
                        "ObjectID"=>$value->ObjectID,
                        "ListName"=>$value->ListName,
                        "Category"=>$value->Category,
                        "Type"=>$value->Type,
                        "Description"=>$value->Description,
                        "ListClassification"=>$value->ListClassification
                    ));
                }
            }
            $respuesta->results = $listResult;
            //Sobreescribimos el resultado para solo regresar los necesario [FINAL]
            if(count($respuesta->results) > 0 && count($respuesta->results) < 2 ){
                $respuesta->results = $respuesta->results[0];
            }            
            $payload = json_encode($respuesta->results);
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json');
        }else{
            $payload = json_encode(array("errorCode" => 0, "errorDescription" => $respuesta->message));
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json');
        }
    });
    $group->post('/{id}',function ($request, $response){
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();        
        $listId = $route->getArgument('id');

        $contents = json_decode(file_get_contents('php://input'), true);
        $params = (array)$contents;

        if(isset($params["Help"]) || isset($params["help"])){
            $payload = json_encode(array("request" => array("method"=>"POST","Content-Type"=>"application/json","params" => array("subscriberKey"=>"subscriberKey [Type: string]","status"=>"Status [Type: string]"))));
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json');
        }
        if(!isset($params["subscriberKey"]) || !isset($params["status"])){
            $payload = json_encode(array("errorCode" => 0, "errorDescription" => "Parameters Missing"));
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json');
        } 

        $stringForSubscriber = "subscriberKey";
        $myclient = new ET_Client(true);
        $subPatch = new ET_Subscriber();
        $subPatch->authStub = $myclient;
        $subPatch->props = array("SubscriberKey"=>$params["subscriberKey"],"Status" => "Active", "Lists" => array("ID" => $listId,"Status" => $params["status"]));
        if(isset($params["email"])){
            $subPatch->props = array("EmailAddress" =>$params["email"],"SubscriberKey"=>$params["subscriberKey"],"Status" => "Active", "Lists" => array("ID" => $listId,"Status" => $params["status"]));
        }        
        $patchResult = $subPatch->patch();
        if (!$patchResult->status) {            
            $payload = json_encode(array("errorCode" => 0, "errorDescription" => $patchResult->message));
            $response->getBody()->write($payload);        
            return $response->withHeader('Content-Type', 'application/json');
        }	
        $payload = json_encode($patchResult);
        $response->getBody()->write($payload);        
        return $response->withHeader('Content-Type', 'application/json');
    });
});



$app->run();