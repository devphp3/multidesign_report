<?php
include('include/include.php');

// Default values for days and website ID
$days = isset($_POST['days']) ? intval($_POST['days']) : 30;
$websiteId = isset($_POST['websiteId']) ? intval($_POST['websiteId']) : null;
$businessType = isset($_POST['businessType']) ? $_POST['businessType'] : null;
$activeStatus = isset($_POST['activeStatus']) ? $_POST['activeStatus'] : null;

$orderQuery = "SELECT DISTINCT user_id
FROM digitizing_order
WHERE order_datetime >= DATE_SUB(NOW(), INTERVAL $days DAY)";
$result1 = $db->query($orderQuery);

// Fetch the user IDs from the result set and create a comma-separated list
$userIdsList = [];
foreach ($result1 as $row) {
    $userIdsList[] = $row['user_id'];
}

$userIdsList = implode(',', $userIdsList);

$secondQuery = "SELECT DISTINCT
    digitizing_member.id,
    digitizing_member.username,
    digitizing_member.sku,
    digitizing_member.company_name,
    digitizing_member.email,
    digitizing_member.mobile,
    digitizing_website.website_name
FROM digitizing_member
LEFT JOIN digitizing_customer_price_settings ON digitizing_member.customer_price_setting_id = digitizing_customer_price_settings.id
LEFT JOIN digitizing_website ON digitizing_member.website_id = digitizing_website.id
WHERE digitizing_member.user_type = 0
    AND digitizing_member.id NOT IN ('$userIdsList')";

// Add website filter if a specific website is selected
if (!empty($websiteId)) {
    $secondQuery .= " AND digitizing_member.website_id = $websiteId";
}

if ($businessType != '') {
    // Quote the $businessType value
    $secondQuery .= " AND digitizing_member.customer_price_setting_id = '$businessType'";
}

if ($activeStatus != '') {
    // Quote the $activeStatus value
    $secondQuery .= " AND digitizing_member.is_active = '$activeStatus'";
}

$result = $db->query($secondQuery);

$filteredData = [];
foreach ($result as $row) {
    $filteredData[] = $row;
}

// Prepare the JSON response
$response = [
    'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
    'recordsTotal' => count($filteredData),  // Total records (not just the ones on the current page)
    'recordsFiltered' => count($filteredData),  // Total records after filtering
    'data' => $filteredData,  // Array of data rows
];

// Return data as JSON
echo json_encode($response);
?>
