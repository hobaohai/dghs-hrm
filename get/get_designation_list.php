<?php

require_once '../configuration.php';

$organization_id = (int) mysql_real_escape_string($_POST['organization_id']);

$sql = "SELECT
            total_manpower_imported_sanctioned_post_copy.designation,
            total_manpower_imported_sanctioned_post_copy.designation_id
        FROM
            total_manpower_imported_sanctioned_post_copy
        WHERE
            org_code = $organization_id
        GROUP BY
            designation_id
        ORDER BY
            designation ";
$result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>get_designation_list:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
//echo "$sql";
$data = array();
$data[] = array(
    'text' => "Select Designation",
    'value' => 0
);
while ($row = mysql_fetch_array($result)) {
    $data[] = array(
        'text' => $row['designation'],
        'value' => $row['designation_id']
    );
}
$json_data = json_encode($data);

print_r($json_data);
?>