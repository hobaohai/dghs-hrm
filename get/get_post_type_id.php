<?php
require_once '../configuration.php';

$sql = "SELECT
            staff_post_type.post_type_name,
            staff_post_type.post_type_id
            FROM
            staff_post_type
            ORDER BY
            staff_post_type.post_type_name";
$result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>checkPasswordIsCorrect:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");

$data = array();
while ($row = mysql_fetch_array($result)) {
    $data[] = array(
        'text' => $row['post_type_name'],
        'value' => $row['post_type_id']
    );
}
$json_data = json_encode($data);

print_r($json_data);
?>
