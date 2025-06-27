<?php

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

$gatewayModuleName = 'piprapay';
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Make sure the module is active
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

// Read raw JSON data from the webhook
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// Optional: Log webhook payload for debugging
// file_put_contents(__DIR__ . '/piprapay_webhook.log', print_r($data, true), FILE_APPEND);

// Get API key from headers (case-insensitive)
$headers = getallheaders();
$receivedKey = $headers['mh-piprapay-api-key'] ?? $headers['Mh-Piprapay-Api-Key'] ?? $headers['MH-PIPRAPAY-API-KEY'] ?? '';

if (empty($receivedKey) || $receivedKey !== $gatewayParams['apikey']) {
    header("HTTP/1.1 401 Unauthorized");
    die("Invalid API Key");
}

// Extract required fields
$invoiceId = $data['metadata']['invoiceid'] ?? null;
$ppId      = $data['pp_id'] ?? null;
$status    = strtolower($data['status'] ?? '');

if (!$invoiceId || !$ppId || !$status) {
    die("Missing parameters");
}

$invoiceId      = (int)$invoiceId;
$transactionId  = $ppId;
$paymentAmount  = $data['amount'] ?? 0;
$paymentFee     = 0.00;

// Step 1: Verify the payment with PipraPay
$verifyPayload = json_encode(['pp_id' => $ppId]);

$ch = curl_init(rtrim($gatewayParams['baseUrl'], '/') . '/api/verify-payments');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'mh-piprapay-api-key: ' . $gatewayParams['apikey'],
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $verifyPayload);
$verifyResponse = curl_exec($ch);
$verifyResult   = json_decode($verifyResponse, true);

// Step 2: Confirm status from verification
if (isset($verifyResult['status']) && strtolower($verifyResult['status']) === 'completed') {
    addInvoicePayment(
        $invoiceId,
        $transactionId,
        $paymentAmount,
        $paymentFee,
        $gatewayModuleName
    );
    logTransaction($gatewayModuleName, $data, 'Success');
    echo "Success";
} else {
    logTransaction($gatewayModuleName, $verifyResult, 'Verification Failed');
    echo "Payment verification failed.";
}