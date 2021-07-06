<?php

function rand_string( $length ) {  
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";  
    $size = strlen( $chars );  
    // echo "Random string =";  
    $strigRandom = 'HT';
    for( $i = 0; $i < $length; $i++ ) {  
        $str= $chars[ rand( 0, $size - 1 ) ];  
        $strigRandom = $strigRandom.$str;  
    }
    return $strigRandom;
} 

function SendMessage($messageKey, $keyDefinition, $contactKey,$to, $vars){    
    include "credencialesSF.php";    
    $objToken = getToken();
    if($objToken['code'] == 0){
        $data =array(
                "definitionKey"=> $keyDefinition,
                "recipient" => array(
                    "contactKey" =>  $contactKey,
                    "to"=>$to,
                    "attributes" => $vars
                ),
            );
        $data_string = json_encode($data);
        $ch = curl_init($objToken['token']["rest_instance_url"].'/messaging/v1/email/messages/'.$messageKey);  
        // var_dump($data_string);                                                           
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_POST, true);
        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization:Bearer '.$objToken['token']["access_token"],'Content-Length: ' . strlen($data_string)));
        $resultAdd = curl_exec($ch);
        $err = curl_error($ch);
        $resultAdd = (Array) json_decode($resultAdd);
        return $resultAdd;
    }else{
        return array("error"=> 0,"desc" => $objToken["codeDesc"]);
    }
}
function getEmailDefinitions($page){
    //obtiene las definiciones para envios de correo transaccionales   
    include "credencialesSF.php";    
    $objToken = getToken();
    if($objToken['code'] == 0){
        $ch = curl_init($objToken['token']["rest_instance_url"].'/messaging/v1/email/definitions/?$page='.$page.'');  
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt ($ch, CURLOPT_POST, false);
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization:Bearer '.$objToken['token']["access_token"]));
        $resultAdd = curl_exec($ch);
        $err = curl_error($ch);
        // $resultAdd = (Array) json_decode($resultAdd);
        return $resultAdd;
    }else{
        return array("error"=> 0,"desc" => $objToken["codeDesc"]);
    }
}



function EmailDefinitions($keyDefinition, $name, $customerKey, $list, $dt){
    include "credencialesSF.php";    
    $objToken = getToken();
    if($objToken['code'] == 0){
        $data = array(
            "definitionKey"=> $keyDefinition,
            "status"=> "Active",
            "name"=> $name,
            "description"=> "Created via REST",
            "classification"=> "Default Transactional",
            "content"=> array(
              "customerKey"=> $customerKey
            ),
            "subscriptions"=> array(
              "list"=> $list,
              "dataExtension"=> $dt,
              "autoAddSubscriber"=> true,
              "updateSubscriber"=> true
            ),
            "options"=> array(
              "trackLinks"=> true,
            //   "cc"=> ["icanul@example.com"],
            //   "bcc"=> ["bcc_address@example.com"]
              "createJourney"=> true
            )
        );
        $data_string = json_encode($data);  
        $ch = curl_init($objToken['token']["rest_instance_url"].'/messaging/v1/email/definitions');  
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_POST, true);
        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization:Bearer '.$objToken['token']["access_token"],'Content-Length: ' . strlen($data_string)));
        $resultAdd = curl_exec($ch);
        $err = curl_error($ch);
        $resultAdd = (Array) json_decode($resultAdd);

        return array("data"=>$resultAdd, "errorSF"=>$err);
    }else{
        return array("error"=> 0,"desc" => $objToken["codeDesc"]);
    }
}


  
  function addNew($email, $fName, $lName, $countryCode, $iAccID, $phone, $areaCode, $certificateTypeCpde, $language, $peopleID, $registerDate, $error, $dates){
    include "credencialesSF.php";   
    $objToken = getToken();
    if($objToken['code'] == 0){
        $data = array(
            "items" =>array(
                array(
                "email"=>$email,
                "fName"=>$fName, 
                "lName" => $lName, 
                "countryCode" => $countryCode,
                "iAccID" => $iAccID,
                "phone" => $phone,
                "areaCode" => $areaCode,
                "certificateTypeCode"=> $certificateTypeCpde,
                "language" => $language, 
                "peopleID" => $peopleID, 
                "registerDate" => $registerDate, 
                "error" => $error, 
                "dates" => $dates
                )
            )
        );
        $data_string = json_encode($data);   
        $ch = curl_init($objToken['token']["rest_instance_url"].'/data/v1/async/dataextensions/key:'.$dtKey.'/rows');  
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_POST, true);
        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization:Bearer '.$objToken['token']["access_token"],'Content-Length: ' . strlen($data_string)));
        $resultAdd = curl_exec($ch);
        $err = curl_error($ch);
        $resultAdd = (Array) json_decode($resultAdd);
        if(count($resultAdd['resultMessages']) == 0){

        }
        return $resultAdd;
    }
  }
  function updateData($email,$peopleID,$iAccID,$amount, $purchaseDate, $certificateID){
    include "credencialesSF.php";   
    $objToken = getToken();
    if($objToken['code'] == 0){
        $data = array(
            "items" =>array(
                array(
                "email"=>$email,
                "iAccID" => $iAccID,
                "certificateID"=> $certificateID,
                "peopleID" => $peopleID, 
                "purchaseDate" => $purchaseDate, 
                "amount" => $amount
                )
            )
        );
        $data_string = json_encode($data);   
        $ch = curl_init($objToken['token']["rest_instance_url"].'/data/v1/async/dataextensions/key:'.$dtKey.'/rows');  
                                                                              
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt ($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization:Bearer '.$objToken['token']["access_token"],'Content-Length: ' . strlen($data_string)));
        $resultAdd = curl_exec($ch);
        $err = curl_error($ch);
        $resultAdd = (Array) json_decode($resultAdd);
        if(count($resultAdd['resultMessages']) == 0){

        }
        return $objToken;
    }
  }
  function getToken(){
    include "credencialesSF.php";   
    
    $data = array(
        "grant_type" => "client_credentials", 
        "client_id" =>$usserSalesforce, 
        "client_secret" =>$passSalesforce,
        "scope" => "data_extensions_write data_extensions_read email_write email_read list_and_subscribers_read list_and_subscribers_write email_send",
        "account_id" => $accountIDSalesforce
    );  
    $ch = curl_init($urlSalesforce);

    $data_string = json_encode($data);

    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
    curl_setopt ($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_POST, true);
    curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data_string)));
    $resultGetToken = curl_exec($ch);
    $resultGetToken = (Array) json_decode($resultGetToken);
    $resultLogin = array("code"=>0);
    if(!isset($resultGetToken["error"])){
        $resultLogin = array("code"=>0, "token"=> $resultGetToken);
    } else{
        $resultLogin = array("code"=>-1,"codeDesc"=>"Error en token");
    }
    
    // echo "<pre>";
    // var_dump($resultLogin);
    // // var_dump($contact);
    // echo "</pre>";
    // exit(0);
    return $resultLogin;
  }

?>

