<?php
include "CredencialesSF.php";
return array(
    'appsignature' => 'none',
	'clientid' => $usserSalesforce,
	'clientsecret' => $passSalesforce,
	'defaultwsdl' => 'https://webservice.exacttarget.com/etframework.wsdl',
    'xmlloc' => 'ExactTargetWSDL.xml',
    'baseUrl' => 'https://mc61kcjlrzmvw8f5xmfcydp5r5x4.rest.marketingcloudapis.com/',
    'baseAuthUrl' => 'https://mc61kcjlrzmvw8f5xmfcydp5r5x4.auth.marketingcloudapis.com/',
    'baseSoapUrl' => 'https://mc61kcjlrzmvw8f5xmfcydp5r5x4.soap.marketingcloudapis.com/',
    'useOAuth2Authentication' => true,
    'accountId' => $accountIDSalesforce,
    'scope' => 'data_extensions_write data_extensions_read email_write list_and_subscribers_read list_and_subscribers_write email_send'
);