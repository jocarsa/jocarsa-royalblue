<?php
/**
 * integration.php
 * Contains helper functions to send invoice data to Software B.
 */

/**
 * Sends invoice data to Software B via its API.
 *
 * @param array $invoiceData The invoice data to send.
 * @return array The API response.
 */
function sendInvoiceToInvoicing($invoiceData) {
    $apiUrl = "https://your-software-b-domain.com/api/invoice.php"; // Update with Software B API URL
    $apiToken = "YOUR_API_TOKEN"; // Replace with your actual API token

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($invoiceData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $apiToken
    ]);
    $response = curl_exec($ch);
    if(curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return ['error' => $error_msg];
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $result = json_decode($response, true);
    if($httpCode !== 201) {
        return ['error' => $result['error'] ?? 'Unknown error'];
    }
    return $result;
}

/**
 * Returns the Software B user id for this admin.
 * In production, this mapping might be stored in a configuration or in the admin’s record.
 *
 * @return int
 */
function getSoftwareBUserId() {
    return 123; // Replace with the proper Software B user id
}

