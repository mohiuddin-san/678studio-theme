<?php
include_once("config/db_conection.php");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


$data = json_decode(file_get_contents("php://input"), true);

$shop_id = $data['shop_id'] ?? null;
$name = $data['name'] ?? '';
$address = $data['address'] ?? '';
$phone = $data['phone'] ?? '';
$nearest_station = $data['nearest_station'] ?? '';
$business_hours = $data['business_hours'] ?? '';
$holidays = $data['holidays'] ?? '';
$map_url = $data['map_url'] ?? '';
$company_email = $data['company_email'] ?? '';

if (!$shop_id) {
    echo json_encode(["error" => "Missing shop_id"]);
    exit;
}

$stmt = $conn->prepare("
    UPDATE studio_shops SET 
        name=?, address=?, phone=?, nearest_station=?, 
        business_hours=?, holidays=?, map_url=?, company_email=?
    WHERE id=?
");

$stmt->bind_param("ssssssssi", $name, $address, $phone, $nearest_station, $business_hours, $holidays, $map_url, $company_email, $shop_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Shop updated successfully"]);
} else {
    echo json_encode(["error" => $stmt->error]);
}
$conn->close();