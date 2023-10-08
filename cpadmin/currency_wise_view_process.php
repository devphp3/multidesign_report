<?php
include('include/include.php');
$fromDate = $_POST['fromDate'];
$toDate = $_POST['toDate'];
$websiteId = $_POST['websiteId'];

// Convert the date format to 'YYYY-MM-DD'
if (!empty($fromDate)) {
    $fromDate = date('Y-m-d', strtotime($fromDate));
}

if (!empty($toDate)) {
    $toDate = date('Y-m-d', strtotime($toDate));
}

$query = "SELECT
    DW.website_name AS website_name,
    DM.currency_id,
    DOB.name,
    COUNT(*) AS record_count,
    FORMAT(SUM(DO.new_price), 2) AS total_price
FROM digitizing_order AS DO
INNER JOIN digitizing_member DM ON DO.user_id = DM.id
INNER JOIN digitizing_website DW ON DM.website_id = DW.id
INNER JOIN digitizing_objectmeta DOB ON DM.currency_id = DOB.id
WHERE DO.is_status IN (3, 7)";

if (!empty($fromDate)) {
    $query .= " AND DO.order_completetion_date >= '$fromDate'";
}
if (!empty($toDate)) {
    $query .= " AND DO.order_completetion_date <= '$toDate'";
}


if (!empty($websiteId)) {
    $query .= " AND DW.id = " . $websiteId;
}
$query .= " GROUP BY DW.website_name, DM.currency_id HAVING COUNT(*) > 0 ORDER BY DW.website_name, DM.currency_id";

$result = $db->query($query);

$data = array();
if (is_array($result)) {
    foreach ($result as $row) {
        $data[] = $row;
    }
}

echo json_encode(array('data' => $data));
?>
