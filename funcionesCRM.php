<?php
session_start();
$_SESSION["token"] = array();

function makeQuery($query){    
    include "credencialesSF.php";
    $objToken = getTokenCRM();

    if($objToken['code'] == 0){
        //adding the logic to get data from SF        
        $ch = curl_init($objToken["token"]["instance_url"]."/services/data/v51.0/query?q=".$query);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt ($ch, CURLOPT_POST, false);
        // curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization:Bearer '.$objToken['token']["access_token"]));
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization:Bearer '.$objToken['token']["access_token"]));

        $resultQuery = curl_exec($ch);
        $resultQuery = (Array) json_decode($resultQuery);
        // $resultQuery = array("echo" => $query);
        $responseQuery = array_merge($resultQuery, $objToken);        
        return $resultQuery;
    }else{
        return $objToken;
    }
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

function  getCustomSObject($sObject, $idSObject){
    include "credencialesSF.php";
    $objToken = getTokenCRM();

    if($objToken['code'] == 0){
        //adding the logic to get data from SF        
        $ch = curl_init($objToken["token"]["instance_url"]."/services/data/v52.0/sobjects/".$sObject."/".$idSObject);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt ($ch, CURLOPT_POST, false);
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization:Bearer '.$objToken['token']["access_token"]));
        $resultQuery = curl_exec($ch);
        $resultQuery = (Array) json_decode($resultQuery);
        return $resultQuery;
    }else{
        return $objToken;
    }
}
function postSObject($sObject, $params = array()){
    include "credencialesSF.php";
    $data = $params;
    $data_string = json_encode($data);  

    $objToken = getTokenCRM();
    if($objToken['code'] == 0){
        $ch = curl_init($objToken["token"]["instance_url"]."/services/data/v52.0/sobjects/".$sObject);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_POST, true);
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization:Bearer '.$objToken['token']["access_token"]));
        $resultPostSObject = curl_exec($ch);
        $resultPostSObject = (Array) json_decode($resultPostSObject);
        
        return $resultPostSObject;        
    }else{
        return $objToken;
    }
}
function putSObject($sObject,$idSObject, $params = array()){
    include "credencialesSF.php";
    $data = $params;
    $data_string = json_encode($data);  

    $objToken = getTokenCRM();
    if($objToken['code'] == 0){
        $ch = curl_init($objToken["token"]["instance_url"]."/services/data/v52.0/sobjects/".$sObject."/".$idSObject);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization:Bearer '.$objToken['token']["access_token"]));
        $resultPostSObject = curl_exec($ch);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $resultPostSObject = (Array) json_decode($resultPostSObject);        
        return $resultPostSObject;        
    }else{
        return $objToken;
    }
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

?>