<?php
include('include/include.php');

// Default values for days and website ID
$days = isset($_POST['days']) ? intval($_POST['days']) : 30;
$websiteId = isset($_POST['websiteId']) ? intval($_POST['websiteId']) : null;
$businessType = isset($_POST['businessType']) ? $_POST['businessType'] : null;
$activeStatus = isset($_POST['activeStatus']) ? $_POST['activeStatus'] : null;
$start = isset($_POST['start']) ? $_POST['start'] : 0;
$length = isset($_POST['length']) ? $_POST['length'] : 100;

$secondQuery = "SELECT DISTINCT
dm.id,
dm.username,
dm.sku,
dm.company_name,
dm.email,
dm.mobile,
dw.website_name
FROM
digitizing_member dm
LEFT JOIN digitizing_order AS dmo
ON
dm.id =
dmo.user_id
LEFT JOIN digitizing_website dw ON
dm.website_id = dw.id
WHERE
dm.user_type = 0 AND(
dmo.order_datetime IS NULL OR
dmo.order_datetime =(
    SELECT
        MAX(order_datetime)
    FROM
        digitizing_order
    WHERE
        user_id = dm.id
)
) AND dw.website_name IS NOT NULL AND(
dmo.order_datetime IS NULL OR
dmo.order_datetime < DATE_SUB(NOW(), INTERVAL $days DAY))";

// Add website filter if a specific website is selected
if (!empty($websiteId)) {
    $secondQuery .= " AND dm.website_id = $websiteId";
}

if ($businessType != '') {
    // Quote the $businessType value
    $secondQuery .= " AND dm.customer_price_setting_id = '$businessType'";
}

if ($activeStatus != '') {
    $secondQuery .= " AND dm.is_active = '$activeStatus'";
} else {
    $secondQuery .= " AND dm.is_active = '1'";
}

$searchValue = $_POST['search']['value'];
if (!empty($searchValue)) {
    $secondQuery .= " AND ( dm.sku LIKE '%$searchValue%'
        OR dm.company_name LIKE '%$searchValue%'
        OR dm.email LIKE '%$searchValue%'
    )";
}
$totalRecordsQuery = "SELECT COUNT(*) AS total FROM ($secondQuery) AS subquery";
$totalRecordsResult = $db->query($totalRecordsQuery);
$totalRecords = $totalRecordsResult[0]['total'];
$secondQuery .= " LIMIT $start, $length";
$result = $db->query($secondQuery);

$filteredData = [];
foreach ($result as $row) {
    $filteredData[] = $row;
}
// Prepare the JSON response
$response = [
    'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
    'recordsTotal' => $totalRecords,  // Total records (not just the ones on the current page)
    'recordsFiltered' => $totalRecords,  // Total records after filtering
    'data' => $filteredData,  // Array of data rows
];

// Return data as JSON
echo json_encode($response);
?>
