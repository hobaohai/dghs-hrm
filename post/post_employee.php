<?php

require_once '../configuration.php';

if ($_SESSION['logged'] != true) {
    header("location:login.php");
}
$org_code = $_SESSION['org_code'];
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['username'];


$pk = mysql_real_escape_string($_POST['pk']);
$name = mysql_real_escape_string($_POST['name']);
$value = mysql_real_escape_string($_POST['value']);
$p_org_code = mysql_real_escape_string($_POST['org_code']);

$post_type = mysql_real_escape_string($_POST['post_type']);
if ($post_type == "checklist"){
//    $value = "[";
    for ($i = 0; $i < count($_POST['value'])-1; $i++) {
        $value .= $_POST['value'][$i] . ",";
    }
//    $value .= $_POST['value'][$i] . "]";
    $value .= $_POST['value'][$i];
}

if ($p_org_code == $org_code){
    $sql = "UPDATE `dghs_hrm_main_live`.`old_tbl_staff_organization` 
            SET 
                `$name` = \"$value\",
                `last_update` = \"". date("Y-m-d H:i:s") . "\",
                `updated_by` = \"$user_name\"   
            WHERE 
                `old_tbl_staff_organization`.`staff_id` = $pk;";
    $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>post_employee:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");

    echo "Successfully Updated.";
} else {
    /*
      In case of incorrect value or error you should return HTTP status != 200.
      Response body will be shown as error message in editable form.
     */

    header('HTTP 400 Bad Request', true, 400);
    echo "This field is required!";
}
?>