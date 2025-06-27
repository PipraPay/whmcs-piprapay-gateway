# PipraPay Payment Gateway for WHMCS

A secure and fully compatible WHMCS payment gateway module to accept payments through PipraPay.

## 🚀 Features

- Compatible with WHMCS v8.9+
- Supports redirect-based checkout
- Webhook (IPN) support with verification
- Invoice metadata support for tracking
- API Key security validation

---

## 📁 Installation

1. Download the module files or clone this repo.
2. Copy the contents into your WHMCS directory like this:

/modules
└── /gateways
├── piprapay.php
└── /callback
└── piprapay.php


3. Log in to your WHMCS Admin panel.
4. Go to **Setup > Payments > Payment Gateways**.
5. Select **PipraPay** from the available gateways.
6. Configure the settings:

- API Key (from your PipraPay dashboard)
- Base URL (e.g., `https://sandbox.piprapay.com`)
- Return Type (GET or POST)
- Currency (e.g., BDT)

---

## 🧪 Testing

1. Set `Base URL` to the PipraPay sandbox URL.
2. Use a test API key provided by PipraPay.
3. Try a test checkout and ensure you are redirected to PipraPay.
4. Complete the payment and verify that the invoice is automatically marked as **Paid**.

---

## 📬 Webhook Callback

The webhook URL is automatically set and handled at:

[your-whmcs-domain]/modules/gateways/callback/piprapay.php

Ensure this is publicly accessible and matches what you set in the PipraPay API dashboard.

---

## 🔒 Security

- API Key is validated on every webhook request.
- Every payment is re-verified via PipraPay's `/api/verify-payments` endpoint.

---

## 📄 License

MIT License
