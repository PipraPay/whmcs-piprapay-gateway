<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly.");
}

function piprapay_MetaData()
{
    return [
        'DisplayName' => 'PipraPay Payment Gateway',
        'APIVersion' => '1.1',
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
        'apikey' => [
            'FriendlyName' => 'API Key',
            'Type' => 'password',
            'Size' => '64',
        ],
        'baseUrl' => [
            'FriendlyName' => 'Base URL (no trailing slash)',
            'Type' => 'text',
            'Default' => 'https://sandbox.piprapay.com',
        ],
        'returnType' => [
            'FriendlyName' => 'Return Type',
            'Type' => 'dropdown',
            'Options' => 'GET,POST',
            'Default' => 'POST',
        ],
        'currency_pp' => [
            'FriendlyName' => 'Currency',
            'Type' => 'text',
            'Default' => 'BDT',
        ],
    ];
}

function piprapay_link($params)
{
    $apiKey = $params['apikey'];
    $baseUrl = rtrim($params['baseUrl'], '/');

    $invoiceId = $params['invoiceid'];
    $amount = $params['amount'];
    $currency = $params['currency_pp'];
    $returnUrl = $params['returnurl'];
    $callbackUrl = $params['systemurl'] . '/modules/gateways/callback/piprapay.php';

    $postData = [
        'full_name'    => $params['clientdetails']['fullname'],
        'email_mobile' => $params['clientdetails']['email'],
        'amount'       => $amount,
        'metadata'     => ['invoiceid' => $invoiceId],
        'redirect_url' => $returnUrl,
        'cancel_url'   => $params['systemurl'],
        'webhook_url'  => $callbackUrl,
        'return_type'  => $params['returnType'],
        'currency'     => $currency,
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
        return '<div class="alert alert-danger">Error connecting to PipraPay.</div>';
    }
}
