<?php
include('include/include.php');

// Default values for days and website ID
$days = isset($_POST['days']) ? intval($_POST['days']) : 30;
$websiteId = isset($_POST['websiteId']) ? intval($_POST['websiteId']) : null;

// Build the SQL query
$query = "SELECT DISTINCT
    digitizing_member.id,
    digitizing_member.username,
    digitizing_member.sku,
    digitizing_member.company_name,
    digitizing_member.email,
    digitizing_member.mobile,
    digitizing_website.website_name
FROM
    digitizing_member
LEFT JOIN digitizing_customer_price_settings ON digitizing_member.customer_price_setting_id = digitizing_customer_price_settings.id
LEFT JOIN digitizing_order ON digitizing_member.id = digitizing_order.user_id
LEFT JOIN digitizing_website ON digitizing_member.website_id = digitizing_website.id
WHERE
    digitizing_member.is_active = 1
    AND (
        digitizing_order.order_datetime IS NULL OR digitizing_order.order_datetime < DATE_SUB(NOW(), INTERVAL $days DAY)
    )
    AND (digitizing_website.website_name IS NOT NULL";

// Add website filter if a specific website is selected
if (!empty($websiteId)) {
    $query .= " AND digitizing_member.website_id = $websiteId";
}

$query .= ")";

// Apply search filter
$searchValue = $_POST['search']['value'];
if (!empty($searchValue)) {
    $query .= " AND (digitizing_member.id LIKE '%$searchValue%' OR digitizing_member.username LIKE '%$searchValue%' OR digitizing_website.website_name LIKE '%$searchValue%')";
}

// Execute the query and fetch results
$result = $db->query($query);

$filteredData = [];
if (is_array($result)) {
    foreach ($result as $row) {
        $filteredData[] = $row;
    }
}

// Prepare the JSON response
$response = [
    'draw' => intval($_POST['draw']),
    'recordsTotal' => count($filteredData),  // Total records (not just the ones on the current page)
    'recordsFiltered' => count($filteredData),  // Total records after filtering
    'data' => $filteredData,  // Array of data rows
];

// Return data as JSON
echo json_encode($response);
?>
