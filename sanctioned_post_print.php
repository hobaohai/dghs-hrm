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
if($_SESSION['user_type']=="admin" && $_GET['org_code'] != ""){
    $org_code = (int) mysql_real_escape_string($_GET['org_code']);
    $org_name = getOrgNameFormOrgCode($org_code);
    $org_type_name = getOrgTypeNameFormOrgCode($org_code);
    $echoAdminInfo = " | Administrator";
    $isAdmin = TRUE;
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo $org_name . " | " . $app_name; ?></title>
        <?php
        include_once 'include/header/header_css_js.inc.php';
        include_once 'include/header/header_ga.inc.php';
        ?>
        <link href="assets/css/login.css" rel="stylesheet"/>
    </head>
    <body data-spy="scroll">
        <!-- Top navigation bar
        ================================================== -->
        <!-- Subhead
        ================================================== -->
            <div class="container">
                <h2><?php echo "$org_name $echoAdminInfo"; ?></h2>
                <p class="lead"><?php echo "$org_type_name"; ?></p>
            </div>


        <div class="container">

            <!-- nav
            ================================================== -->
            <div class="row">

                <div class="span9">
                    <!-- Sanctioned Post
                    ================================================== -->
                    <section id="sanctioned-post">

                        <div class="row">
                            <div class="span9">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th colspan="7">&nbsp;</th>
											<th><a href="" onclick="javascript:window.print()" >Print</a></th>
                                        </tr>
										  <tr>
                                            <th>Sanctioned Post</th>
											<th> Type  of Post</th>
											<th> Pay Scale</th>
											<th> Job Class</th>
											<th> Existing Male</th>
											<th> Existing Female</th>
											<th> Existing total</th>
											<th> Vacant Post</th>
											<th> Sanctioned Post</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
										 /* $sql = "SELECT id, designation, discipline,type_of_post,pay_scale,class, COUNT(*) AS sp_count
                                            FROM total_manpower_imported_sanctioned_post_copy
                                            WHERE org_code = $org_code
                                            GROUP BY designation";*/

                                        $sql = "SELECT id, designation, discipline,type_of_post,pay_scale,class,existing_male,existing_female,existing_total,vacant_post, COUNT(*) AS sp_count
                                            FROM total_manpower_imported_sanctioned_post_copy
                                            WHERE org_code = $org_code
                                            AND total_manpower_imported_sanctioned_post_copy.active LIKE 1
                                            GROUP BY designation";
                                        $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>sql:2</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                       $cnt =0;
                                        while ($sp_data = mysql_fetch_assoc($result)) {
                                            echo "<tr>";
                                            echo "<td>";
											echo $sp_data['designation'];
											echo "</td>";
											 echo "<td>";
											echo $sp_data['type_of_post'];
											echo "</td>";
											echo "<td>";
											echo $sp_data['pay_scale'];
											echo "</td>";
										     echo "<td>";
											echo $sp_data['class'];
											echo "</td>";
											 echo "<td>";
											echo $sp_data['existing_male'];
											echo "</td>";
											echo "<td>";
											echo $sp_data['existing_female'];
											echo "</td>";
											echo "<td>";
											echo $sp_data['existing_total'];
											echo "</td>";
											echo "<td>";
											echo $sp_data['vacant_post'];
											echo "</td>";
											 echo "<td>";
											 echo  $sp_data['sp_count'];
                                           echo "</td>";
                                            echo "</div>";
                                            echo "</div>";
                                            echo "</div>";

                                            // sanctioned post list display
                                            echo "<div class=\"row\">";
                                            echo "<div class=\"span9\">";
                                            echo "<div id=\"$designation_div_id\" class=\"collapse\">";
//                                            echo "<strong>First Level Division:</strong> ABCD, <strong>Second Level Division:</strong> EFGH<br />";
                                            echo "<div class=\"clearfix alert alert-info\" id=\"list-$designation_div_id\">";

											?>
                                        <div id="loading-<?php echo $designation_div_id; ?>"><i class="icon-spinner icon-spin icon-large"></i> Loading content...</div>
                                        <script type="text/javascript" language="javascript">
                                            $(document).ready(function() {
                                                $("#btn-<?php echo $designation_div_id; ?>").click(function(event) {
                                                    $.ajax({
                                                        type: "POST",
                                                        url: "result.php",
                                                        data: {designation: "<?php echo $sp_data['designation']; ?>", org_code:<?php echo $org_code; ?>},
                                                        dataType: 'json',
                                                        success: function(data) {
                                                            $('#loading-<?php echo $designation_div_id; ?>').hide();
                                                            $('#list-<?php echo $designation_div_id; ?>').html("");
                                                            $.each(data, function(k, v) {
                                                                var data_list = "<div class=\"row\">";
                                                                data_list += "<div class=\"span6\">Sanctioned PostId: " + v.sanctioned_post_id + " (Staff Name: " + v.staff_name + ", Id:" + v.staff_id + ") </div>";
//                                                                data_list += "<div class=\"span1\">Id:" + v.staff_id + "</div>";
                                                                if (v.staff_id > 0) {
                                                                    data_list += "<div class=\"span2\"> <a href=\"employee.php?staff_id=" + v.staff_id + "&sanctioned_post_id=" + v.sanctioned_post_id + "&org_code=<?php echo $org_code; ?>\" target=\"_blank\"  class=\"btn btn-warning btn-mini\" ><i class=\"icon-user\"></i> View Profile</a>";
                                                                    data_list += "<a href=\"#moveOut_" + v.sanctioned_post_id + "\" role=\"button\" data-toggle=\"modal\"  class=\"btn btn-primary btn-mini\" ><i class=\"icon-external-link\"></i> Move Out</a></div>";
                                                                    data_list += "'<div id=\"moveOut_" + v.sanctioned_post_id + "\" class=\"modal hide fade\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"myModalLabel\" aria-hidden=\"true\">;";
                                                                    data_list += "<div class=\"modal-header\">";
                                                                    data_list += "<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">×</button>";
                                                                    data_list += "<h3 id=\"myModalLabel\">Move Out Type</h3>";
                                                                    data_list += "</div>";
                                                                    data_list += "<div class=\"modal-body\">";
                                                                    data_list += "<ul>";
                                                                    data_list += "<li><a href=\"#\">Promotion</a></li>";
                                                                    data_list += "<li><a href=\"move_staff.php?action=move_out&staff_id=" + v.staff_id + "&sanctioned_post_id=" + v.sanctioned_post_id + "&org_code=<?php echo $org_code; ?>\" target=\"_blank\" >Transfer</a></li>";
                                                                    data_list += "<li><a href=\"#\">Retirement</a></li>";
                                                                    data_list += "<li><a href=\"#\">Suspension</a></li>";
                                                                    data_list += "<li><a href=\"#\">Termination</a></li>";
                                                                    data_list += "<li><a href=\"#\">Death</a></li>";
                                                                    data_list += "<li><a href=\"#\">Leaving Job</a></li>";
                                                                    data_list += "<li><a href=\"#\">Unauthorised absent</a></li>";
                                                                    data_list += "<li><a href=\"#\">Leave</a></li>";
                                                                    data_list += "</ul>";
                                                                    data_list += "</div>";
                                                                    data_list += "<div class=\"modal-footer\">";
                                                                    data_list += "<button class=\"btn\" data-dismiss=\"modal\" aria-hidden=\"true\">Close</button>";
                                                                    data_list += "</div>";
                                                                    data_list += "</div>";


                                                                }
                                                                else {
                                                                    data_list += "<div class=\"span2\"> <a href=\"employee.php?sanctioned_post_id=" + v.sanctioned_post_id + "&action=new\" target=\"_blank\"  class=\"btn btn-success btn-mini\" >Add Profile</a></div>";
                                                                }

                                                                data_list += "</div>";
                                                                $("#list-<?php echo $designation_div_id; ?>").append(data_list);
                                                            });
                                                        }
                                                    });
                                                });
                                            });
                                        </script>
                                        <?php
                                        echo "</div>";
                                        echo "</div>";
                                        echo "</div>";
                                        echo "</div>";
                                        ?>

                                        <div id="sp-<?php echo "$designation_div_id"; ?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                <h3><?php echo $sp_data['designation']; ?></h3>
                                            </div>
                                            <div class="modal-body">
                                                <div id="sp-loading-<?php echo $designation_div_id; ?>"><i class="icon-spinner icon-spin icon-large"></i> Loading Content...</div>
                                                <div id="sp-content-<?php echo $designation_div_id; ?>"></div>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                                            </div>
                                        </div>
                                        <script type="text/javascript" language="javascript">
                                            $(document).ready(function() {
                                                $("#sp-btn-<?php echo $designation_div_id; ?>").click(function(event) {
                                                    $.ajax({
                                                        type: "POST",
                                                        url: "result-sp.php",
                                                        data: {id: "<?php echo $sp_data['id']; ?>"},
                                                        dataType: 'json',
                                                        success: function(data) {
                                                            $('#sp-loading-<?php echo $designation_div_id; ?>').hide();
                                                            $('#sp-content-<?php echo $designation_div_id; ?>').html("");
                                                            $.each(data, function(k, v) {
                                                                var data_all = "<table class=\"table\">";
//                                                                data_all += "<tr><td><b>Sanctioned Post Id</b></td><td>" + v.sanctioned_post_id + "</td></tr>";
                                                                data_all += "<tr><td><b>Organizaion Code</b></td><td><?php echo "$org_code"; ?></td></tr>";
                                                                data_all += "<tr><td><b>Organizaion Name</b></td><td><?php echo "$org_name"; ?></td></tr>";
                                                                data_all += "<tr><td><b>First Level Name</b></td><td>" + v.first_level_name + "</td></tr>";
                                                                data_all += "<tr><td><b>Second Level Name</b></td><td>" + v.second_level_name + "</td></tr>";
                                                                data_all += "<tr><td><b>Class</b></td><td>" + v.class + "</td></tr>";
                                                                data_all += "<tr><td><b>Pay Scale</b></td><td>" + v.pay_scale + "</td></tr>";
                                                                data_all += "<tr><td><b>Type of Post</b></td><td>" + v.type_of_post + "</td></tr>";
                                                                data_all += "<tr><td><b>Discipline</b></td><td>" + v.discipline + "</td></tr>";
																data_all += "<tr><td><b>Rank of the Post</b></td><td></td></tr>";
                                                                data_all += "<tr><td><b>Bangladesh Professional Category </b></td><td></td></tr>";
                                                                data_all += "<tr><td><b>WHO Major Health Occupation Group</b></td><td></td></tr>";
                                                                data_all += "<tr><td><b>WHO-ISCO Occupation Name </b></td><td></td></tr>";
                                                                data_all += "<tr><td><b>Year when the post created </b></td><td></td></tr>";
                                                                data_all += "<tr><td><b>Recruitment rule for the post</b></td><td></td></tr>";
                                                                data_all += "</table>";
                                                                $('#sp-content-<?php echo $designation_div_id; ?>').append(data_all);
                                                            });
                                                        }
                                                    });
                                                });
                                            });
                                        </script>
                                        <?php
                                        echo "</td>";
                                        echo "</tr>";


										 $cnt+=$sp_data['sp_count'];


                                    }

									echo "<tr>";
									echo "<td colspan='8'>";
									echo '<b>Total no of sanctioned post</b>';
									echo "</td>";
									echo "<td>";
									echo $cnt;
									echo "</td>";
									echo "</tr>";

									//print_r($sp_data);
									//echo $sp_data[0]['sp_count'];
								    // $sp_count=mysql_fetch_array($result);
									 //print_r($sp_count)

									 //echo $sp_count[0]['sp_count'];
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div> <!-- /container -->
    </body>
</html>
