<?php
require_once '../configuration.php';
?>
<script type="text/javascript">

    function confirmDelete(org_code) {
	    var org_code;
        if (confirm('Are you sure you want to delete this?')) {
            //Make ajax call
            $.ajax({
                url: "script_delete.php",
                type: "POST",
                data: {org_code : org_code},
                dataType: "html", 
                success: function() {
                    alert("It was succesfully deleted!");
                }
            });

        }
    }
</script>
<?php 
$div_id = (int) mysql_real_escape_string($_POST['div_id']);
$dis_id = (int) mysql_real_escape_string($_POST['dis_id']);
$upa_id = (int) mysql_real_escape_string($_POST['upa_id']);
$agency_code = (int) mysql_real_escape_string($_POST['agency_code']);
$type_code = (int) mysql_real_escape_string($_POST['type_code']);

if (!$agency_code > 0) {
    $agency_code = 11;
}

$query_string = "";
//echo "$div_id|$dis_id|$upa_id";

if ($upa_id > 0) {
    $query_string .= " AND organization.upazila_id = $upa_id";
} else if ($dis_id > 0) {
    $query_string .= " AND organization.district_id = $dis_id";
}
if ($div_id > 0) {
    $query_string .= " AND organization.division_id = $div_id";
}
if ($type_code > 0) {
    $query_string .= " AND organization.org_type_code = $type_code";
}

$query_string .= " ORDER BY org_name";

$sql = "SELECT
            organization.org_name,
            organization.org_code,
            organization.email_address1
        FROM
            organization
        WHERE
            organization.agency_code = $agency_code
            $query_string";
$result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>get_org_list:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
//echo "$sql";

$row_count = mysql_num_rows($result);
if ($row_count > 0) {
    echo "\" Total $row_count Organization(s) found. \"";
    echo "<ul class=\"nav nav-pills nav-stacked\">";
    while ($data_list = mysql_fetch_assoc($result)) {
        echo "<li>";
        //echo "<a href=\"org_profile.php?org_code=" . $data_list['org_code'] . "\" target=\"_blank\">";
		   
        echo $data_list['org_name'];
        echo " (Org Code:" . $data_list['org_code'] . ")";
        echo " -- Email: "  . $data_list['email_address1'];
        echo "<a href=\"#\" onclick='return confirmDelete(".$data_list['org_code'].")' target=\"_blank\" style='display:inline;color:red;'>Delete</a>";
       
        echo "</li>";
    }
    echo "</ul>";
} 
else {
    echo "\" 0 (Zero) Organization found. \"";
}
?>
