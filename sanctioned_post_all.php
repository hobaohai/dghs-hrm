<?php
require_once 'configuration.php';

/*
if ($_SESSION['logged'] != true) {
    header("location:login.php");
}
*/
/*
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
}*/
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php //echo $org_name . " | " . $app_name; ?></title>
        <?php
        include_once 'include/header/header_css_js.inc.php';
        include_once 'include/header/header_ga.inc.php';
        ?>
    </head>

    <body data-spy="scroll">

        <!-- Top navigation bar
        ================================================== -->

        <!-- Subhead
        ================================================== -->


        <div class="container">

            <!-- nav
            ================================================== -->
            <div class="row">

                <div class="span9">
                    <!-- Sanctioned Post
                    ================================================== -->
                    <section id="sanctioned-post">

                        <div class="row">
                            <div class="span9" style="width:1180px;">
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
											<th> Existing total</th>
											<th> Vacant Post</th>
											<th> Sanctioned Post</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php

                                        $sql = "SELECT id, designation, discipline,type_of_post,pay_scale,class,SUM(CASE WHEN staff_id != '0' THEN 1 ELSE 0 END) AS existing, COUNT(*) AS sp_count
                                            FROM total_manpower_imported_sanctioned_post_copy
                                            AND total_manpower_imported_sanctioned_post_copy.active LIKE 1
                                            GROUP BY designation order by pay_scale asc";

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
										echo $sp_data['existing'];
											echo "</td>";
											echo "<td>";
								         echo $vacant=($sp_data['sp_count']-$sp_data['existing']);
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

                                        <?php
                                        echo "</td>";
                                        echo "</tr>";

										 $total_existing+=$sp_data['existing'];
										 $total_vacant+=$vacant;
										 $total_sanction+=$sp_data['sp_count'];


                                    }

									echo "<tr>";
									echo "<td colspan='4'>";
									echo '<b>Total no of sanctioned post</b>';
									echo "</td>";
								    echo "<td>";
									echo  $total_existing;
									echo "</td>";
									echo "<td>";
									echo  $total_vacant;
									echo "</td>";
									echo "<td>";
									echo  $total_sanction;
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
        </div>
    </body>
</html>
