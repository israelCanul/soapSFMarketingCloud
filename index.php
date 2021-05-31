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
        array("endpoint"=>"/publicationLists/{id}","description"=>"cambiar el status de un subscriptor en la lista especifica","request" => array("method"=>"POST","Content-Type"=>"application/json","params" => array("subscriberKey"=>"subscriberKey [Type: string]","status"=>"Status [Type: string]")))
    );
    $payload = json_encode($methods);
    $response->getBody()->write($payload);        
    return $response->withHeader('Content-Type', 'application/json');
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