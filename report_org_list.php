<?php
require_once 'configuration.php';

if ($_SESSION['logged'] != true) {
    header("location:login.php");
}

// assign values from session array
$org_code = $_SESSION['org_code'];
$org_name = $_SESSION['org_name'];
$org_type_name = $_SESSION['org_type_name'];

$echoAdminInfo = "";

// assign values admin users
if ($_SESSION['user_type'] == "admin" && $_GET['org_code'] != "") {
    $org_code = (int) mysql_real_escape_string($_GET['org_code']);
    $org_name = getOrgNameFormOrgCode($org_code);
    $org_type_name = getOrgTypeNameFormOrgCode($org_code);
    $echoAdminInfo = " | Administrator";
    $isAdmin = TRUE;
}

/***
 * 
 * POST
 */
//print_r($_POST);
$div_id = (int) mysql_real_escape_string(trim($_POST['admin_division']));
$dis_id = (int) mysql_real_escape_string(trim($_POST['admin_district']));
$upa_id = (int) mysql_real_escape_string(trim($_POST['admin_upazila']));
$agency_code = (int) mysql_real_escape_string(trim($_POST['org_agency']));
$type_code = (int) mysql_real_escape_string(trim($_POST['org_type']));
$form_submit = (int) mysql_real_escape_string(trim($_POST['form_submit']));

if ($form_submit == 1 && isset($_POST['form_submit'])) {

    /*
     * 
     * query builder to get the organizatino list
     */
    $query_string = "";
    if ($div_id > 0 || $dis_id > 0 || $upa_id > 0 || $agency_code > 0 || $type_code > 0) {
        $query_string .= " WHERE ";

        if ($agency_code > 0) {
            $query_string .= "organization.agency_code = $agency_code";
        }
        if ($upa_id > 0) {
            if ($agency_code > 0) {
                $query_string .= " AND ";
            }
            $query_string .= "organization.upazila_id = $upa_id";
        }
        if ($dis_id > 0) {
            if ($upa_id > 0 || $agency_code > 0) {
                $query_string .= " AND ";
            }
            $query_string .= "organization.district_id = $dis_id";
        }
        if ($div_id > 0) {
            if ($dis_id > 0 || $upa_id > 0 || $agency_code > 0) {
                $query_string .= " AND ";
            }
            $query_string .= "organization.division_id = $div_id";
        }
        if ($type_code > 0) {
            if ($div_id > 0 || $dis_id > 0 || $upa_id > 0 || $agency_code > 0) {
                $query_string .= " AND ";
            }
            $query_string .= "organization.org_type_code = $type_code";
        }
    }

    $query_string .= " ORDER BY org_name";

    $sql = "SELECT organization.org_name, organization.org_code FROM organization $query_string";
    $org_list_result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>get_org_list:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
//echo "$sql";

    
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?php echo $org_name . " | " . $app_name; ?></title>
        <?php
        include_once 'include/header/header_css_js.inc.php';
        include_once 'include/header/header_ga.inc.php';
        ?>
    </head>

    <body>

        <!-- Top navigation bar
        ================================================== -->
        <?php include_once 'include/header/header_top_menu.inc.php'; ?>

        <!-- Subhead
        ================================================== -->
        <header class="jumbotron subhead" id="overview">
            <div class="container">
                <h1><?php echo $org_name . $echoAdminInfo; ?></h1>
                <p class="lead"><?php echo "$org_type_name"; ?></p>
            </div>
        </header>


        <div class="container">

            <!-- Docs nav
            ================================================== -->
            <div class="row">
                <div class="span3 bs-docs-sidebar">
                    <ul class="nav nav-list bs-docs-sidenav">
                            <?php
                        $active_menu = "";
                        include_once 'include/left_menu.php';
                        ?>
                    </ul>
                </div>
                <div class="span9">
                    <!-- info area
                    ================================================== -->
                    <section id="report">

                        <div class="row">
                            <div class="">
                                <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                                    <p class="lead">Organization List</p>
                                    <div class="control-group">
                                        <select id="admin_division" name="admin_division">
                                            <option value="0">Select Division</option>
                                            <?php
                                            /**
                                             * @todo change old_visision_id to division_bbs_code
                                             */
                                            $sql = "SELECT admin_division.division_name, admin_division.old_division_id FROM admin_division";
                                            $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>loadDivision:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");

                                            while ($rows = mysql_fetch_assoc($result)) {
												if ($rows['old_division_id'] == $_POST['admin_division'])
       												 echo "<option value=\"" . $rows['old_division_id'] . "\" selected='selected'>" . $rows['division_name'] . "</option>";
											    else
                                                     echo "<option value=\"" . $rows['old_division_id'] . "\">" . $rows['division_name'] . "</option>";
                                            }
                                            ?>
                                        </select>
										
                                         <select id="admin_district" name="admin_district">
                                         <option value="0">Select District</option>
										<?php 
										    
											$sql = "SELECT 
												  admin_district.district_bbs_code,
												  admin_district.old_district_id,
												  admin_district.district_name
											  FROM
												  admin_district
											  WHERE
												  admin_district.division_id =$div_id
											  ORDER BY
												  admin_district.district_name";
									  $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>get_district_list:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
										  while ($rows = mysql_fetch_assoc($result)) {
												if ($rows['old_district_id'] == $_REQUEST['admin_district'])
       												 echo "<option value=\"" . $rows['old_district_id'] . "\" selected='selected'>" . $rows['district_name'] . "</option>";
											    else
                                                     echo "<option value=\"" . $rows['old_district_id'] . "\">" . $rows['district_name'] . "</option>";
                                            }
											
										?>
                                        </select>
                                        
<!--                                        <select id="admin_district" name="admin_district">
                                            <option value="0">Select District</option>                             
                                        </select>-->
                                        
                                        
                                       <select id="admin_upazila" name="admin_upazila">
                                         <option value="0">Select Upazila</option>
										<?php 
										    
											$sql = "SELECT
													admin_upazila.upazila_name,
													admin_upazila.old_upazila_id
												FROM
													admin_upazila
												WHERE
													admin_upazila.old_district_id = $dis_id
												ORDER BY
													admin_upazila.upazila_name";
									  $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>get_dupazila_list:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
										  while ($rows = mysql_fetch_assoc($result)) {
												if ($rows['old_upazila_id'] == $_REQUEST['admin_upazila'])
       												 echo "<option value=\"" . $rows['old_upazila_id'] . "\" selected='selected'>" . $rows['upazila_name'] . "</option>";
											    else
                                                     echo "<option value=\"" . $rows['old_upazila_id'] . "\">" . $rows['upazila_name'] . "</option>";
                                            }
											
										?>
                                        </select>
                                        
                                        <!--<select id="admin_upazila" name="admin_upazila">
                                            <option value="0">Select Upazila</option>                                        
                                        </select>-->
                                    </div>

                                    <div class="control-group">
                                        <select id="org_agency" name="org_agency">
                                            <option value="0">Select Agency</option>
                                            <?php
                                            $sql = "SELECT
                                                    org_agency_code.org_agency_code,
                                                    org_agency_code.org_agency_name
                                                FROM
                                                    org_agency_code
                                                ORDER BY
                                                    org_agency_code.org_agency_code";
                                            $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>loadorg_agency:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");

                                            while ($rows = mysql_fetch_assoc($result)) {
												if ($rows['org_agency_code'] == $_POST['org_agency'])
       												 echo "<option value=\"" . $rows['org_agency_code'] . "\" selected='selected'>" . $rows['org_agency_name'] . "</option>";
											    else
                                                     echo "<option value=\"" . $rows['org_agency_code'] . "\">" . $rows['org_agency_name'] . "</option>";
                                            }
                                            ?>
                                        </select>

                                        <select id="org_type" name="org_type">
                                            <option value="0">Select Org Type</option>
                                            <?php
                                            $sql = "SELECT
                                                            org_type.org_type_code,
                                                            org_type.org_type_name
                                                        FROM
                                                            org_type
                                                        ORDER BY
                                                            org_type.org_type_name ASC";
                                            $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>loadorg_type:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");

                                            while ($rows = mysql_fetch_assoc($result)) {
												if($rows['org_type_code'] == $_POST['org_type'])
												echo "<option value=\"" . $rows['org_type_code'] . "\" selected='selected'>" . $rows['org_type_name'] . "</option>";
												else
                                                echo "<option value=\"" . $rows['org_type_code'] . "\">" . $rows['org_type_name'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <input name="form_submit" value="1" type="hidden" />
                                    <div class="control-group">
                                        <button id="btn_show_org_list" type="submit" class="btn btn-info">Show Report</button>

                                        <a id="loading_content" href="#" class="btn btn-info disabled" style="display:none;"><i class="icon-spinner icon-spin icon-large"></i> Loading content...</a>
                                    </div>  
                                </form>
                            </div>
                            <?php if ($form_submit == 1 && isset($_POST['form_submit'])) : ?>
                            <div class="alert alert-success"> 
                                    Report displaying form:<br>
                                    <?php
                                    $echo_string="";
                                    if ($div_id > 0){
                                        $echo_string .= " Division: <strong>" . getDivisionNamefromCode(getDivisionCodeFormId($div_id)) . "</strong><br>";
                                    }
                                    if ($dis_id > 0){
                                        $echo_string .= " District: <strong>" . getDistrictNamefromCode(getDistrictCodeFormId($dis_id)) . "</strong><br>";
                                    }
                                    if ($upa_id > 0){
                                        $echo_string .= " Upazila: <strong>" . getUpazilaNamefromBBSCode(getUpazilaCodeFormId($upa_id), getDistrictCodeFormId($dis_id)) . "</strong><br>";
                                    }
                                    if ($agency_code > 0){
                                        $echo_string .= " Agency: <strong>" . getAgencyNameFromAgencyCode($agency_code) . "</strong><br>";
                                    }
                                    if ($type_code > 0){
                                        $echo_string .= " Org Type: <strong>" . getOrgTypeNameFormOrgTypeCode($type_code) . "</strong><br>";
                                    }
                                    echo "$echo_string";
                                    ?>
                                <br />
                                <blockquote>
                                Total <strong><em><?php echo mysql_num_rows($org_list_result); ?></em></strong> organization found.<br />
                                </blockquote>
                            </div>
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <td><strong>Organization Name</strong></td>
                                        <td><strong>Organization Code</strong></td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($data = mysql_fetch_assoc($org_list_result)): ?>
                                    <tr>
                                        <td><?php echo $data['org_name']; ?></td>
                                        <td><?php echo $data['org_code']; ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>

                    </section>

                </div>
            </div>

        </div>



        <!-- Footer
        ================================================== -->
        <?php include_once 'include/footer/footer.inc.php'; ?>

        <script type="text/javascript">
            // load division
            $('#admin_division').change(function() {
                $("#loading_content").show();
                var div_id = $('#admin_division').val();
                $.ajax({
                    type: "POST",
                    url: 'get/get_district_list.php',
                    data: {div_id: div_id},
                    dataType: 'json',
                    success: function(data)
                    {
                        $("#loading_content").hide();
                        var admin_district = document.getElementById('admin_district');
                        admin_district.options.length = 0;
                        for (var i = 0; i < data.length; i++) {
                            var d = data[i];
                            admin_district.options.add(new Option(d.text, d.value));
                        }
                    }
                });
            });

            // load district 
            $('#admin_district').change(function() {
                var dis_id = $('#admin_district').val();
                $("#loading_content").show();
                $.ajax({
                    type: "POST",
                    url: 'get/get_upazila_list.php',
                    data: {dis_id: dis_id},
                    dataType: 'json',
                    success: function(data)
                    {
                        $("#loading_content").hide();
                        var admin_upazila = document.getElementById('admin_upazila');
                        admin_upazila.options.length = 0;
                        for (var i = 0; i < data.length; i++) {
                            var d = data[i];
                            admin_upazila.options.add(new Option(d.text, d.value));
                        }
                    }
                });
            });
        </script>
    </body>
</html>
