<?php
session_start();
$_SESSION["token"] = array();


function checkForDomain(){
    $http_origin = $_SERVER["HTTP_ORIGIN"]; 
    $whiteListDomains = array("http://127.0.0.1:5500", "http://localhost:3000");
   
    
    if(array_search($http_origin, $whiteListDomains) === false)
    {
        return false;
    }  else{
        return true;
    }
    
}


function makeQuery($query, $token){    
    include "credencialesSF.php";
    // $objToken = getTokenCRM();

    // if($objToken['code'] == 0){
        //adding the logic to get data from SF        
        $ch = curl_init($instance_url."/services/data/v51.0/query?q=".$query);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt ($ch, CURLOPT_POST, false);
        // curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization:Bearer '.$objToken['token']["access_token"]));
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization:'.$token[0]));

        $resultQuery = curl_exec($ch);
        $resultQuery = (Array) json_decode($resultQuery);
        // $resultQuery = array("echo" => $query);
        $responseQuery = array_merge($resultQuery);        
        return $resultQuery;
    // }else{
    //     return $objToken;
    // }
}
// function getAccount($accId){    
//     include "credencialesSF.php";
//     $objToken = getTokenCRM();

//     if($objToken['code'] == 0){
//         //adding the logic to get data from SF        
//         $ch = curl_init($objToken["token"]["instance_url"]."/services/data/v52.0/sobjects/Account/".$accId);
//         curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false); 
//         curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
//         curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
//         //curl_setopt ($ch, CURLOPT_POST, false);
//         curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization:Bearer '.$objToken['token']["access_token"]));
//         $resultQuery = curl_exec($ch);
//         $resultQuery = (Array) json_decode($resultQuery);
//         return $resultQuery;
//     }else{
//         return $objToken;
//     }
// }

function  getCustomSObject($sObject, $idSObject,$token){
    include "credencialesSF.php";
    // $objToken = getTokenCRM();

    // if($objToken['code'] == 0){
        //adding the logic to get data from SF        
        $ch = curl_init($instance_url."/services/data/v52.0/sobjects/".$sObject."/".$idSObject);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt ($ch, CURLOPT_POST, false);
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization:'.$token[0]));
        $resultQuery = curl_exec($ch);
        $resultQuery = (Array) json_decode($resultQuery);
        return $resultQuery;
    // }else{
    //     return $objToken;
    // }
}
function postSObject($sObject, $params = array(), $token){
    include "credencialesSF.php";
    $data = $params;
    $data_string = json_encode($data);  

    // $objToken = getTokenCRM();
    // if($objToken['code'] == 0){
        $ch = curl_init($instance_url."/services/data/v52.0/sobjects/".$sObject);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_POST, true);
        // curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization:Bearer '.$objToken['token']["access_token"]));
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization:'.$token[0]));
        $resultPostSObject = curl_exec($ch);
        $resultPostSObject = (Array) json_decode($resultPostSObject);
        
        return $resultPostSObject;   
        // return array("data"=> $token[0]);
    // }else{
    //     return $objToken;
    // }
}
function putSObject($sObject,$idSObject, $params = array(), $token){
    include "credencialesSF.php";
    $data = $params;
    $data_string = json_encode($data);  

    // $objToken = getTokenCRM();
    // if($objToken['code'] == 0){
        $ch = curl_init($instance_url."/services/data/v52.0/sobjects/".$sObject."/".$idSObject);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization:'.$token[0]));
        $resultPostSObject = curl_exec($ch);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $resultPostSObject = (Array) json_decode($resultPostSObject);        
        return $resultPostSObject;        
    // }else{
    //     return $objToken;
    // }
}

function getTokenCRM(){
    include "credencialesSF.php";
    $data = array(
        "grant_type" => $grant_type, 
        "client_id" =>$client_id, 
        "client_secret" =>$client_secret,
        "username " => $username,
        "password" => $password,
        "redirect_uri" => $redirect_uri
    );
    $data= "grant_type=".$grant_type."&grant_type=".$grant_type."&client_id=".$client_id."&client_secret=".$client_secret."&username=".$username."&password=".$password."&redirect_uri=".$redirect_uri;
    $ch = curl_init($urAuth);
    $data_string =$data;

    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
    curl_setopt ($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_POST, true);
    curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded','Content-Length: ' . strlen($data_string)));
    $resultGetToken = curl_exec($ch);
    $resultGetToken = (Array) json_decode($resultGetToken);
    $resultLogin = array("code"=>0);
    if(!isset($resultGetToken["error"])){
        $_SESSION["tokenCRM"] = $resultGetToken;
        $resultLogin = array("code"=>0, "token"=> $resultGetToken);
    } else{
        $resultLogin = array("code"=>-1,"codeDesc"=>"Error en token");
    }
    return $resultLogin; 
}
function getCustomTokenCRM($userCustom,$passwordCustom){
    include "credencialesSF.php";
    $data= "grant_type=".$grant_type."&grant_type=".$grant_type."&client_id=".$client_id."&client_secret=".$client_secret."&username=".$userCustom."&password=".$passwordCustom."&redirect_uri=".$redirect_uri;
    $ch = curl_init($urAuth);
    $data_string =$data;

    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
    curl_setopt ($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_POST, true);
    curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded','Content-Length: ' . strlen($data_string)));
    $resultGetToken = curl_exec($ch);
    $resultGetToken = (Array) json_decode($resultGetToken);
    $resultLogin = array("code"=>0);
    if(!isset($resultGetToken["error"])){
        $_SESSION["tokenCRM"] = $resultGetToken;
        $resultLogin = array("code"=>0, "token"=> $resultGetToken);
    } else{
        $resultLogin = array("code"=>-1,"codeDesc"=>"Error en token");
    }
    return $resultLogin; 
}


  /**
 * @desc    Do a DELETE of lists of preferences and insert new ones
 *
 * @param   string $params  array of data to be used en this function
 * @param   array  $token  this param is used to get authorization to use the Salesforce API 
 * 
 */
function makeMultipleDeletesForPreferences($params, $token){
    include "credencialesSF.php";
    //array of ids to be deleted
    $idsToDelete = array(); 
    $account = ""; 
    if(isset($params["PersonContactId"])){
        $ch = curl_init($instance_url."/services/data/v51.0/query?q=".urlencode("SELECT Id FROM Account WHERE PersonContactId= '".$params["PersonContactId"]."' "));
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization:'.$token[0]));
        $resultGetAccount = curl_exec($ch);
        $resultGetAccount = (Array) json_decode($resultGetAccount);        
        if(!isset($resultGetAccount["done"]) ){
            $responseGetAccount = array("code"=>-1, "Error"=> $resultGetAccount[0]->message);
            return $responseGetAccount;
        }
        $account = $resultGetAccount["records"][0]->Id;
    }

    //get the array of preferences by account id [INICIO]
    if(isset($params["RRC_Account__c"])){
        $account = $params["RRC_Account__c"];       
    }
    $ch = curl_init($instance_url."/services/data/v51.0/query?q=".urlencode("SELECT Id,RRC_Account__c,RRC_PreferenceType__c,RRC_Preference__c FROM RRC_Preference__c WHERE RRC_Account__c= '".$account."' AND RRC_PreferenceType__c= '".$params["RRC_PreferenceType__c"]."'"));
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization:'.$token[0]));

    $resultQuery = curl_exec($ch);
    $resultQuery = (Array) json_decode($resultQuery);
    $responseQuery = array_merge($resultQuery);
    foreach ((array) $responseQuery["records"] as $key => $value) {
       
        array_push($idsToDelete,$value->Id);
    }
    //delete objects [INICIO]
    $resultDelete = "There aren´t records to this Account";
    if(count($idsToDelete) > 0){
        $stringOfIds = implode(",",$idsToDelete);
        curl_init();
        curl_setopt($ch, CURLOPT_URL, $instance_url."/services/data/v51.0/composite/sobjects?ids=".$stringOfIds);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization:'.$token[0]));
        $resultDelete = curl_exec($ch);
        $resultDelete = (Array) json_decode($resultDelete);
    }
    //delete objects [FINAL]

    //adding the new ones [INICIO]
    $recordsToAdd = array();
    foreach ($params["records"]  as $key => $value) {
        // $params["records"]
        array_push(
                $recordsToAdd, 
                array(
                "attributes" => array("type" => "RRC_Preference__c", "referenceId" => "ref".$key),    
                "RRC_Preference__c" => $value["RRC_Preference__c"],
                "RRC_Account__c" => $account,
                "RRC_PreferenceType__c" => $params["RRC_PreferenceType__c"]
                )
            );
    }

    $data_string = json_encode(array("records"=>$recordsToAdd)); 

    $ch = curl_init($instance_url."/services/data/v52.0/composite/tree/RRC_Preference__c");
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
    curl_setopt ($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_POST, true);
    curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization:'.$token[0]));
    $resultPostNewObjects = curl_exec($ch);
    $resultPostNewObjects = (Array) json_decode($resultPostNewObjects); 
    //adding the new ones [FINAL]

    if(isset($resultPostNewObjects["hasErrors"])){
        if($resultPostNewObjects["hasErrors"] == false){
            $resultLogin = array("code"=>0, "IDsDeleted"=> $idsToDelete,"idsDeleted" => $resultDelete,"added" => $resultPostNewObjects);
        }else{
            $resultLogin = array("code"=>-1,"idsDeleted" => $resultDelete, "Error"=> $resultPostNewObjects["results"][0]->errors[0]->message);
        }        
    }else{
        $resultLogin = array("code"=>-2, "Error"=> $resultPostNewObjects);
    }
    
    // $resultLogin = array("code"=>0,  "response" => $resultQuery);
    return  $resultLogin;
} 



?>