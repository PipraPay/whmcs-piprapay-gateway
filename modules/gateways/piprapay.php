<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly.");
}

function piprapay_MetaData()
{
    return [
        'DisplayName' => 'PipraPay Payment Gateway',
        'APIVersion' => '1.0.2',
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
    ];
}

function piprapay_config()
{
    return [
        'FriendlyName' => [
            'Type' => 'System',
            'Value' => 'PipraPay',
        ],
        'Description' => [
            'Type' => 'System',
            'Value' => 'This is the PipraPay payment gateway module for WHMCS, which allows users to process payments securely.',
        ],
        'DeveloperName' => [
            'Type' => 'System',
            'Value' => 'Mouzh Hossain Shad',
        ],
        'Version' => [
            'Type' => 'System',
            'Value' => '1.0.2',
        ],
        'apikey' => [
            'FriendlyName' => 'API Key',
            'Type' => 'password',
            'Size' => '64',
        ],
        'baseUrl' => [
            'FriendlyName' => 'Base URL',
            'Type' => 'text',
            'Default' => '',
        ],
        'piprapay_version' => [
            'FriendlyName' => 'PipraPay Version',
            'Type' => 'dropdown',
            'Options' => 'V3+,V2',
            'Default' => 'V3+',
        ],
    ];
}

function piprapay_link($params)
{
    if($params['piprapay_version'] == "V2"){
        $apiKey = $params['apikey'];
        $baseUrl = rtrim($params['baseUrl'], '/');
    
        $invoiceId = $params['invoiceid'];
        $amount = $params['amount'];
        $returnUrl = $params['returnurl'];
    
        $parsedUrl = parse_url($params['systemurl']);
        $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] : 'http';
        $host = $parsedUrl['host'] ?? '';
        $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        
        $systemUrl = $scheme . '://' . $host . $port;
        $callbackUrl = $systemUrl . '/modules/gateways/callback/piprapay.php';
    
        $postData = [
            'full_name'    => $params['clientdetails']['fullname'],
            'email_mobile' => $params['clientdetails']['email'],
            'amount'       => $amount,
            'metadata'     => ['invoiceid' => $invoiceId],
            'redirect_url' => $returnUrl,
            'cancel_url'   => $params['systemurl'],
            'webhook_url'  => $callbackUrl,
            'return_type'  => 'GET',
            'currency'     => $params['currency'],
        ];
    
        $ch = curl_init($baseUrl . '/api/create-charge');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'accept: application/json',
            'mh-piprapay-api-key: ' . $apiKey,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        $response = curl_exec($ch);
        $result = json_decode($response, true);
    
        if (isset($result['pp_url'])) {
            return '<a href="' . $result['pp_url'] . '" target="_blank" class="btn btn-primary">Pay Now</a>';
        } else {
            return '<div class="alert alert-danger">'.$response.'</div>';
        }
    }else{
        $baseUrl = $params['baseUrl'];
    
        $invoiceId = $params['invoiceid'];
        $amount = $params['amount'];
        
        $parsedUrl = parse_url($params['systemurl']);
        $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] : 'http';
        $host = $parsedUrl['host'] ?? '';
        $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        
        $systemUrl = $scheme . '://' . $host . $port;
        $callbackUrl = preg_replace('/(https?:\/\/)www\./i', '$1', $systemUrl . '/modules/gateways/callback/piprapay.php');
        $returnUrl = preg_replace('/(https?:\/\/)www\./i', '$1', $params['returnurl']);

        $postData = [
            'full_name'    => $params['clientdetails']['firstname'].' '.$params['clientdetails']['firstname'],
            'email_address' => ($params['clientdetails']['email'] == "") ? 'jhon@gmail.com' : $params['clientdetails']['email'],
            'mobile_number' => ($params['clientdetails']['phonenumber'] == "") ? '01700000000' : $params['clientdetails']['phonenumber'],
            'amount'       => $amount,
            'metadata'     => ['invoiceid' => $invoiceId],
            'return_url' => $returnUrl,
            'webhook_url'  => $callbackUrl,
            'currency'     => $params['currency'],
        ];
    
        $ch = curl_init($baseUrl . '/checkout/redirect');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'accept: application/json',
            'MHS-PIPRAPAY-API-KEY: ' . $params['apikey'],
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        $response = curl_exec($ch);
        $result = json_decode($response, true);
    
        if (isset($result['pp_url'])) {
            return '<a href="' . $result['pp_url'] . '" target="_blank" class="btn btn-primary">Pay Now</a>';
        } else {
            if (isset($result['error'])) {
                return '<div class="alert alert-danger">'.$result['error']['message'].'</div>';
            }else{
                return '<div class="alert alert-danger">Error connecting to PipraPay.</div>';
            }
        }
    }
}
