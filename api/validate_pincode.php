<?php
require_once __DIR__ . '/../includes/pincode_validation.php';

header('Content-Type: application/json');

$pincode = isset($_GET['pincode']) ? trim($_GET['pincode']) : '';
echo json_encode(lookupIndianPincode($pincode));
