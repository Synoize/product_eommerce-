<?php
/**
 * Indian pincode validation through the free Postal Pincode API.
 * API: https://api.postalpincode.in/pincode/{pincode}
 */

function lookupIndianPincode(string $pincode): array
{
    $pincode = trim($pincode);

    if (!preg_match('/^[1-9][0-9]{5}$/', $pincode)) {
        return [
            'valid' => false,
            'message' => 'Enter a valid 6-digit Indian pincode.',
        ];
    }

    $url = 'https://api.postalpincode.in/pincode/' . rawurlencode($pincode);
    $response = null;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        $curlOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_USERAGENT => 'Earthence/1.0',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ];
        if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
            $curlOptions[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V4;
        }
        curl_setopt_array($ch, $curlOptions);
        $response = curl_exec($ch);
        curl_close($ch);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 8,
                'header' => "User-Agent: Earthence/1.0\r\n",
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        $response = @file_get_contents($url, false, $context);
    }

    if (!$response) {
        return [
            'valid' => false,
            'message' => 'Could not verify pincode right now. Please try again.',
        ];
    }

    $data = json_decode($response, true);
    $result = $data[0] ?? null;
    $postOffices = $result['PostOffice'] ?? [];

    if (($result['Status'] ?? '') !== 'Success' || empty($postOffices)) {
        return [
            'valid' => false,
            'message' => 'This pincode was not found. Please enter a valid Indian pincode.',
        ];
    }

    $firstPostOffice = $postOffices[0];

    return [
        'valid' => true,
        'message' => 'Pincode verified.',
        'pincode' => $pincode,
        'city' => $firstPostOffice['District'] ?? '',
        'state' => $firstPostOffice['State'] ?? '',
        'post_office' => $firstPostOffice['Name'] ?? '',
    ];
}
