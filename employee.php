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

//GET values
$staff_id = (int) mysql_real_escape_string($_GET['staff_id']);
$sanctioned_post_id = (int) mysql_real_escape_string($_GET['sanctioned_post_id']);
$action = mysql_real_escape_string($_GET['action']);

if ($sanctioned_post_id != "") {
    $temp = checkStaffExistsBySanctionedPost($sanctioned_post_id);
    $staff_exists = $temp['exists'];
    $staff_id = $temp['staff_id'];
    $staff_profile_exists = checkStaffProfileExists($staff_id);

    $sanctioned_post_within_org = checkSanctionedPostWithinOrgFromSanctionedPostId($sanctioned_post_id, $org_code);

    $designation = getDesignationNameFormSanctionedPostId($sanctioned_post_id);
} else if ($staff_id != "") {
    $staff_exists = checkStaffExists($staff_id);
    $staff_profile_exists = checkStaffProfileExists($staff_id);
    $designation = getDesignationNameFormStaffId($staff_id);
    $sanctioned_post_id = getSanctionedPostIdFromStaffId($staff_id);
}

$staff_org_code = getOrgCodeFromStaffId($staff_id);

$userCanEdit = FALSE;
if ($_SESSION['user_type'] == 'admin') {
    $userCanEdit = TRUE;
}
if ($staff_org_code == $org_code) {
    $userCanEdit = TRUE;
}
if ($sanctioned_post_within_org) {
    $userCanEdit = TRUE;
}


// set staff display mode
//if ($staff_exists && !$userCanEdit) {
//    $display_mode = "view"; 
//    
//    // data fetched form staff table
//    $data = getStaffInfoFromStaffId($staff_id);    
//    
//} else if ($staff_exists && $userCanEdit) {
//    $display_mode = "edit";
//    
//    // data fetched form staff table
//    $data = getStaffInfoFromStaffId($staff_id);
//    
//} else if ($action == "new" && $userCanEdit) {
//    if ($sanctioned_post_id != "") {
//        
//    }
//    $display_mode = "new";
//}
// Set Staff profile Display mode
if (!$userCanEdit && $staff_profile_exists) {
    $display_mode = "view";

    // data fetched form staff table
    $data = getStaffInfoFromStaffId($staff_id);
} else if ($staff_profile_exists && $userCanEdit) {
    $display_mode = "edit";

    // data fetched form staff table
    $data = getStaffInfoFromStaffId($staff_id);
} else if ($action == "new" && $userCanEdit) {
    if ($sanctioned_post_id != "") {
        
    }
    $display_mode = "new";
}

// staff search option
if (isset($_POST['search'])) {
    $search_string = mysql_real_escape_string($_POST['search']);
    $sql = "SELECT
                old_tbl_staff_organization.staff_id
            FROM
                `old_tbl_staff_organization`
            WHERE
                old_tbl_staff_organization.staff_name LIKE \"%$search_string%\" OR
                old_tbl_staff_organization.staff_id = \"$search_string\" AND
                old_tbl_staff_organization.org_code = $org_code";
    $s_result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>a:2</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");

    $s_data = mysql_fetch_assoc($s_result);

    if ($s_data['staff_id'] > 0) {
        $staff_id = $s_data['staff_id'];
        header("location:employee.php?staff_id=$staff_id");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?php echo $org_name . " | " . $app_name; ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <!-- Le styles -->
        <link href="assets/css/bootstrap.min.css" rel="stylesheet">
        <link href="assets/css/bootstrap-responsive.css" rel="stylesheet">
        <link href="library/font-awesome/css/font-awesome.min.css" rel="stylesheet">
        <link href="library/bootstrap-editable/css/bootstrap-editable.css" rel="stylesheet">
        <link href="assets/css/style.css" rel="stylesheet">
        <link href="assets/js/google-code-prettify/prettify.css" rel="stylesheet">


        <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="assets/js/html5shiv.js"></script>
        <![endif]-->

        <!-- Le fav and touch icons -->
        <link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/ico/apple-touch-icon-144-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
        <link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">
        <link rel="shortcut icon" href="assets/ico/favicon.png">

        <!--Google analytics code-->
        <?php include_once 'include/header/header_ga.inc.php'; ?>
        <style>
            .warning { color: red; }
            .error{ font-size: 12px;color: red; }
        </style>
    </head>

    <body data-spy="scroll" data-target=".bs-docs-sidebar">

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
                        <?php if ($_SESSION['user_type'] == "admin"): ?>
                            <li><a href="admin_home.php?org_code=<?php echo $org_code; ?>"><i class="icon-chevron-right"></i><i class="icon-qrcode"></i> Admin Homepage</a>
                            <?php endif; ?>
                            <?php
                            $active_menu = "employee";
                            include_once 'include/left_menu.php';
                            ?>
                    </ul>
                </div>
                <div class="span9">
                    <!-- Download
                    ================================================== -->
                    <section id="organization-profile">

                        <div class="row">
                            <div class="span9">

                                <?php if ($staff_id == "" && $action != "new"): ?>
                                    <div class="alert alert-success">
                                        <div>
                                            If you want to view a specific staff profile, please use the following serchbox to find. <br />
                                            Or, you can find the staff form the <a href="sanctioned_post.php">Sanctioned Post Page</a>.
                                        </div>
                                        <div>
                                            <form class="form-signin" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                                                <div class="input-append">
                                                    <!--<input class="span4" id="org_code" name="org_code" type="hidden" value="<?php echo $org_code; ?>">-->                                                                                                       
                                                    <input class="span4" id="search" name="search" type="text" placeholder="Enter Staff Name or Staff ID" >
                                                    <button class="btn" type="submit" >Search</button>
                                                </div>
                                            </form>
                                        </div>
                                        <div id="staff_search_main">
                                            <div id="staff_search_input">

                                            </div>

                                            <div id="staff_search_result">

                                            </div>
                                        </div>

                                    </div>
                                <?php endif; ?>

                                <?php if ($display_mode == "view"): ?>
                                    <table class="table table-striped table-hover" id="employee-profile">
                                        <tr>
                                            <td width="50%"><strong>Organization Name</strong></td>
                                            <td><?php echo getOrgNameFormOrgCode($data['org_code']); ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Organization Code</strong></td>
                                            <td><?php echo $data['org_code']; ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Staff Name</strong></td>
                                            <td><?php echo $data['staff_name']; ?></td>
                                        </tr>  

                                        <tr>
                                            <td width="50%"><strong>Staff Name (Bangla) </strong></td>
                                            <td><?php echo $data['staff_bangla_name']; ?></td>
                                        </tr> 
                                        <tr>
                                            <td width="50%"><strong>National ID</strong></td>
                                            <td><?php echo $data['staff_national_id']; ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong><a href="#">Code No.(Doctors Only):</a></strong></td>
                                            <td>
                                                <?php echo $data['staff_pds_code']; ?>,
                                                <?php if($data['staff_pds_code']) : ?>,
                                                <a href="view_staff_pds.php?pds_code=<?php echo $data['staff_pds_code']; ?>&type=s">View Short PDS</a>, 
                                                <a href="view_staff_pds.php?pds_code=<?php echo $data['staff_pds_code']; ?>&type=f">View Full PDS</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Department</strong></td>
                                            <td><?php echo getStaffDepertmentFromDepertmentId($data['department_id']); ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Date of Birth</strong></td>
                                            <td><?php echo $data['birth_date']; ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Sex</strong></td>
                                            <td><?php echo getSexNameFromId($data['sex']); ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Religious Group</strong></td> <!--religion -->
                                            <td><?php echo getReligeonNameFromId($data['religion']); ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Marital Status</strong></td>
                                            <td><?php echo getMaritalStatusFromId($data['marital_status']); ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Fathers Name</strong></td> <!--father_name -->
                                            <td><?php echo $data['father_name']; ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Mothers Name</strong></td>
                                            <td><?php echo $data['mother_name']; ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Email Address</strong></td>
                                            <td><?php echo $data['email_address']; ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Contact No.</strong></td>
                                            <td><?php echo $data['contact_no']; ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Present Address</strong></td><!-- mailing_address  -->
                                            <td><?php echo $data['mailing_address']; ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Permanent Address</strong></td>
                                            <td><?php echo $data['permanent_address']; ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Freedom Fighter? </strong></td>
                                            <td><?php echo getFreedomFighterNameFromId($data['freedom_fighter_id']); ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Tribal?</strong></td>
                                            <td><?php echo getTribalNameFromId($data['tribal_id']); ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Job Class</strong></td>
                                            <td><?php echo getClassNameformId($data['staff_job_class']); ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Staff Professional Category</strong></td>
                                            <td><?php echo getProfessionalCategoryFromId($data['staff_professional_category']); ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Designation</strong></td>
                                            <td><?php echo getDesignationNameFormSanctionedPostId($data['sanctioned_post_id']); ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Post Type</strong></td>
                                            <td><?php echo getPostTypeFromId($data['post_type_id']); ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Staff Posting</strong></td>
                                            <td><?php echo getStaffPostingTypeFormId($data['staff_posting']); ?></td>
                                        </tr>

                                        <tr>
                                            <td width="50%"><strong>Draw Salary from which place:</strong></td> <!-- draw_salary_id-->
                                            <td><?php echo getSalaryDrawNameFromID($data['draw_salary_id']); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Designation Type</strong></td>
                                            <td><?php echo getDesignationTypeNameFromId($data['designation_type_id']); ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Posted As</strong></td> <!--job_posting_id-->
                                            <td><?php echo getJobPostingNameFromId($data['job_posting_id']); ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Working Status</strong></td>
                                            <td><?php echo getWorkingStatusNameFromId($data['working_status_id']); ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Salary drawn from which head:</strong></td><!-- draw_type_id-->
                                            <td><?php echo getDrawTypeNameFromId($data['draw_type_id']); ?></td>
                                        </tr>
                                        <!--
                                        <tr>
                                            <td width="50%"><strong>Pay Scale of Current Designation</strong></td>
                                            <td><?php echo $data['pay_scale_of_current_designation']; ?></td>
                                        </tr>
                                        
                                        <tr>
                                            <td width="50%"><strong>Current Basic Pay (Tk.):</strong></td>
                                            <td><?php echo $data['current_basic_pay_taka']; ?></td>
                                        </tr>
                                        -->
                                        <tr>
                                            <td width="50%"><strong>IST (In Service Training) GO </strong></td>
                                            <td><?php echo $data['ist_go']; ?></td>
                                        </tr>

                                        <tr>
                                            <td width="50%"><strong>IST SL No.</strong></td>
                                            <td><?php echo $data['ist_sl_no']; ?></td>
                                        </tr>

                                        <tr>
                                            <td width="50%"><strong>IST Appointment Date</strong></td>
                                            <td><?php echo $data['ist_appoinment_date']; ?></td>
                                        </tr>

                                        <tr>
                                            <td width="50%"><strong>IST Year</strong></td>
                                            <td><?php echo $data['ist_year']; ?></td>
                                        </tr>

                                        <tr>
                                            <td width="50%"><strong>BCS Batch No.</strong></td>
                                            <td><?php echo $data['bcs_batch_no']; ?></td>
                                        </tr>

                                        <tr>
                                            <td width="50%"><strong>BCS/PSC Regularization GO</strong></td>
                                            <td><?php echo $data['bcs_psc_regularization_go']; ?></td>
                                        </tr>


                                        <tr>
                                            <td width="50%"><strong>BCS/PSC Regularization SL No.</strong></td>
                                            <td><?php echo $data['bcs_psc_regularization_sl_no']; ?></td>
                                        </tr>

                                        <tr>
                                            <td width="50%"><strong>BCS/PSC Regularization Date </strong></td>
                                            <td><?php echo $data['bcs_psc_regularization_date']; ?></td>
                                        </tr>

                                        <tr>
                                            <td width="50%"><strong>Service Confirmation GO </strong></td>
                                            <td><?php echo $data['service_confirmation_go']; ?></td>
                                        </tr>

                                        <tr>
                                            <td width="50%"><strong>Service Confirmation  SL No.</strong></td>
                                            <td><?php echo $data['service_confirmation_sl_no']; ?></td>
                                        </tr>

                                        <tr>
                                            <td width="50%"><strong>Service Confirmation Date</strong></td>
                                            <td><?php echo $data['service_confirmation_date']; ?></td>
                                        </tr>


                                        <tr>
                                            <td width="50%"><strong>Date Of Joining to Govt. Health Service</strong></td>
                                            <td><?php echo $data['date_of_joining_to_govt_health_service']; ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Date Of Joining to Current Place</strong></td>
                                            <td><?php echo $data['date_of_joining_to_current_place']; ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Date Of Joining to Current Designation</strong></td>
                                            <td><?php echo $data['date_of_joining_to_current_designation']; ?></td>

                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Professional Discipline of Current Designation</strong></td>
                                            <td><?php echo getProfessionalDisciplineNameFromId($data['professional_discipline_of_current_designation']); ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Education Qualification</strong></td><!--type_of_educational_qualification-->
                                            <td><?php //  echo getEducationalQualification($data['type_of_educational_qualification']);                      ?></td>
                                        </tr>

                                        <script>
                                            var type_of_educational_qualification = "[";
                                            type_of_educational_qualification += "<?php echo $data['type_of_educational_qualification']; ?>";
                                            type_of_educational_qualification += "]";
                                        </script>

                                        <tr>
                                            <td width="50%"><strong>Actual Degree</strong></td>
                                            <td><?php echo $data['actual_degree']; ?></td>
                                        </tr>
                                        <!--
                                        <tr>
                                            <td width="50%"><strong>Designation Id</strong></td>
                                            <td><?php echo getDesignationNameformCode($data['designation_id']); ?></td>
                                        </tr>
                                        -->
                                        <tr>
                                            <td width="50%"><strong>Sanctioned Post ID</strong></td>
                                            <?php
                                            if ($data['sanctioned_post_id'] > 0) {
                                                echo "<td>" . $data['sanctioned_post_id'] . "</td>";
                                            } else if ($data['sanctioned_post_id'] == 0) {
                                                echo "<td>OSD</td>";
                                            }
                                            ?>
                                            <td><?php echo $data['sanctioned_post_id']; ?></td>
                                        </tr>

                                        <tr>
                                            <td width="50%"><strong>Staff ID</strong></td>
                                            <td><?php echo $data['staff_id']; ?></td>
                                        </tr>
                                        <!--
                                        <tr>
                                            <td width="50%"><strong>Posting Status</strong></td>
                                            <td></td>
                                        </tr>
                                      
                                        <tr>
                                            <td width="50%"><strong>Staff Local ID</strong></td>
                                            <td><a href="#" class="text-input" data-type="text" id="staff_local_id"><?php echo $data['staff_local_id']; ?></a></td>
                                        </tr>  -->
                                        <tr>
                                            <td width="50%"><strong>Reside in Govt. Quarter?</strong></td><!-- govt_quarter_id-->
                                            <td><?php echo getGovtQuarter($data['govt_quarter_id']); ?></td>
                                        </tr>
                                        <!--
                                        <tr>
                                            <td width="50%"><strong>Job Status</strong></td>
                                            <td><a href="#" class="text-input" data-type="text" id="job_status" name="job_status"><?php echo $data['job_status']; ?></a></td>
                                        </tr>
                                        -->
                                        <tr>
                                            <td width="50%"><strong>Further Remarks/Explanation:</strong></td><!--reason -->
                                            <td><?php echo $data['reason']; ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Last Updated On</strong></td>
                                            <td><?php echo $data['last_update']; ?></td>
                                        </tr>

                                    </table>

                                <?php elseif ($display_mode == "edit"):
                                    ?>
                                    <table class="table table-striped table-hover" id="employee-profile">
                                        <tr>
                                            <td width="50%"><strong>Staff Name</strong></td>
                                            <td><a href="#" class="text-input" data-type="text" id="staff_name"><?php echo $data['staff_name']; ?></a></td>
                                        </tr>

                                        <tr>
                                            <td width="50%"><strong>Staff Name (Bangla)</strong></td>
                                            <td><a href="#" class="text-input" data-type="text" id="staff_bangla_name"><?php echo $data['staff_bangla_name']; ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>National ID</strong></td>
                                            <td><a href="#" class="text-input" data-type="text" id="staff_national_id"><?php echo $data['staff_national_id']; ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Organization Name</strong></td>
                                            <td><?php echo getOrgNameFormOrgCode($data['org_code']); ?></td>
                                        </tr>                                        
                                        <tr>
                                            <td width="50%"><strong><a href="#">Code No.(Doctors Only):</a></strong></td>
                                            <td>
                                                <a href="#" class="text-input" data-type="text" id="staff_pds_code"><?php echo $data['staff_pds_code']; ?></a>
                                                <?php if($data['staff_pds_code']) : ?>,
                                                <a href="view_staff_pds.php?pds_code=<?php echo $data['staff_pds_code']; ?>&type=s">View Short PDS</a>, 
                                                <a href="view_staff_pds.php?pds_code=<?php echo $data['staff_pds_code']; ?>&type=f">View Full PDS</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Department</strong></td>
                                            <td><a href="#" id="department_id"><?php echo getStaffDepertmentFromDepertmentId($data['department_id']); ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Date of Birth</strong></td>
                                            <td><a href="#" class="date-input" id="birth_date" ><?php echo $data['birth_date']; ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Sex</strong></td>
                                            <td><a href="#" class="" id="sex" ><?php echo getSexNameFromId($data['sex']); ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Religious Group</strong></td> <!--religion -->
                                            <td><a href="#" id="religion" name="religion"><?php echo getReligeonNameFromId($data['religion']); ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Marital Status</strong></td>
                                            <td><a href="#" class="" id="marital_status" ><?php echo getMaritalStatusFromId($data['marital_status']); ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Fathers Name</strong></td> <!--father_name -->
                                            <td><a href="#" class="text-input" data-type="text" id="father_name"><?php echo $data['father_name']; ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Mothers Name</strong></td>
                                            <td><a href="#" class="text-input" data-type="text" id="mother_name"><?php echo $data['mother_name']; ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Email Address</strong></td>
                                            <td><a href="#" class="text-input" data-type="email" id="email_address" ><?php echo $data['email_address']; ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Contact No.</strong></td>
                                            <td><a href="#" class="text-input" id="contact_no" ><?php echo $data['contact_no']; ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Present Address</strong></td><!-- mailing_address  -->
                                            <td><a href="#" class="date-textarea" id="mailing_address" ><?php echo $data['mailing_address']; ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Permanent Address</strong></td>
                                            <td><a href="#" class="date-textarea" id="permanent_address" ><?php echo $data['permanent_address']; ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Freedom Fighter? </strong></td>
                                            <td><a href="#" id="freedom_fighter_id" ><?php echo getFreedomFighterNameFromId($data['freedom_fighter_id']); ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Tribal?</strong></td>
                                            <td><a href="#" id="tribal_id" ><?php echo getTribalNameFromId($data['tribal_id']); ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Job Class</strong></td>
                                            <td><a href="#" id="staff_job_class" ><?php echo getClassNameformId($data['staff_job_class']); ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Staff Professional Category</strong></td>
                                            <td><a href="#" id="staff_professional_category" ><?php echo getProfessionalCategoryFromId($data['staff_professional_category']); ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Designation</strong></td>
                                            <td><?php echo getDesignationNameFormSanctionedPostId($data['sanctioned_post_id']); ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Post Type</strong></td>
                                            <td><a href="#" class="" id="post_type_id" ><?php echo getPostTypeFromId($data['post_type_id']); ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Staff Posting</strong></td>
                                            <td><a href="#" id="staff_posting"><?php echo getStaffPostingTypeFormId($data['staff_posting']); ?></a></td>
                                        </tr>

                                        <tr>
                                            <td width="50%"><strong>Draw Salary from which place:</strong></td> <!-- draw_salary_id-->
                                            <td><a href="#" id="draw_salary_id" ><?php echo getSalaryDrawNameFromID($data['draw_salary_id']); ?></a></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Designation Type</strong></td>
                                            <td><a href="#" id="designation_type_id" ><?php echo getDesignationTypeNameFromId($data['designation_type_id']); ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Posted As</strong></td> <!--job_posting_id-->
                                            <td><a href="#" id="job_posting_id" ><?php echo getJobPostingNameFromId($data['job_posting_id']); ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Working Status</strong></td>
                                            <td><a href="#" id="working_status_id" ><?php echo getWorkingStatusNameFromId($data['working_status_id']); ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Salary drawn from which head:</strong></td><!-- draw_type_id-->
                                            <td><a href="#" id="draw_type_id" ><?php echo getDrawTypeNameFromId($data['draw_type_id']); ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Pay Scale of Current Designation</strong></td>
                                            <td><a href="#" id="pay_scale_of_current_designation" name="pay_scale_of_current_designation"><?php echo getPayScaleId($data['pay_scale_of_current_designation']); ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Current Basic Pay (Tk.):</strong></td>
                                            <td><a href="#" class="text-input" data-type="text" id="current_basic_pay_taka" name="current_basic_pay_taka"><?php echo $data['current_basic_pay_taka']; ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>IST GO</strong></td>
                                            <td><a href="#" class="text-input" data-type="text" id="ist_go" name="ist_go"><?php echo $data['ist_go']; ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>IST SL No.</strong></td>
                                            <td><a href="#" class="text-input" data-type="text" id="ist_sl_no" name="ist_sl_no"><?php echo $data['ist_sl_no']; ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>IST Appointment Date</strong></td>
                                            <td><a href="#" class="date-input" id="ist_appoinment_date" ><?php echo $data['ist_appoinment_date']; ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>IST Year</strong></td>
                                            <td><a href="#" class="text-input" data-type="text" id="ist_year" name="ist_year"><?php echo $data['ist_year']; ?></a></td>
                                        </tr>

                                        <tr>
                                            <td width="50%"><strong>BCS Batch No.</strong></td>
                                            <td><a href="#" class="text-input" data-type="text" id="bcs_batch_no" name="bcs_batch_no"><?php echo $data['bcs_batch_no']; ?></a></td>
                                        </tr>

                                        <tr>
                                            <td width="50%"><strong>BCS/PSC Regularization GO</strong></td>
                                            <td><a href="#" class="text-input" data-type="text" id="bcs_psc_regularization_go" name="bcs_psc_regularization_go"><?php echo $data['bcs_psc_regularization_go']; ?></a></td>
                                        </tr>


                                        <tr>
                                            <td width="50%"><strong>BCS/PSC Regularization SL No.</strong></td>
                                            <td><a href="#" class="text-input" data-type="text" id="bcs_psc_regularization_sl_no" name="bcs_psc_regularization_sl_no"><?php echo $data['bcs_psc_regularization_sl_no']; ?></a></td>
                                        </tr>

                                        <tr>
                                            <td width="50%"><strong>BCS/PSC Regularization Date </strong></td>
                                            <td><a href="#" class="date-input" id="bcs_psc_regularization_date" ><?php echo $data['bcs_psc_regularization_date']; ?></a></td>
                                        </tr>

                                        <tr>
                                            <td width="50%"><strong>Service Confirmation GO </strong></td>
                                            <td><a href="#" class="text-input" data-type="text" id="service_confirmation_go" name="service_confirmation_go"><?php echo $data['service_confirmation_go']; ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Service Confirmation  SL No.</strong></td>
                                            <td><a href="#" class="text-input" data-type="text" id="service_confirmation_sl_no" name="service_confirmation_sl_no"><?php echo $data['service_confirmation_sl_no']; ?></a></td>
                                        </tr>


                                        <tr>
                                            <td width="50%"><strong>Service Confirmation Date</strong></td>
                                            <td><a href="#" class="date-input" id="service_confirmation_date" ><?php echo $data['service_confirmation_date']; ?></a></td>
                                        </tr>


                                        <tr>
                                            <td width="50%"><strong>Date Of Joining to Govt. Health Service</strong></td>
                                            <td><a href="#" class="date-input" id="date_of_joining_to_govt_health_service" ><?php echo $data['date_of_joining_to_govt_health_service']; ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Date Of Joining to Current Place</strong></td>
                                            <td><a href="#" class="date-input" id="date_of_joining_to_current_place" ><?php echo $data['date_of_joining_to_current_place']; ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Date Of Joining to Current Designation</strong></td>
                                            <td><a href="#" class="date-input" id="date_of_joining_to_current_designation" ><?php echo $data['date_of_joining_to_current_designation']; ?></a></td>

                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Professional Discipline of Current Designation</strong></td>
                                            <td><a href="#" id="professional_discipline_of_current_designation" name="professional_discipline_of_current_designation"><?php echo getProfessionalDisciplineNameFromId($data['professional_discipline_of_current_designation']); ?></a></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Education Qualification</strong></td><!--type_of_educational_qualification-->
                                            <td><a href="#" id="type_of_educational_qualification" name="type_of_educational_qualification"><?php //  echo getEducationalQualification($data['type_of_educational_qualification']);                      ?></a></td>
                                        </tr>

                                        <script>
                                            var type_of_educational_qualification = "[";
                                            type_of_educational_qualification += "<?php echo $data['type_of_educational_qualification']; ?>";
                                            type_of_educational_qualification += "]";
                                        </script>

                                        <tr>
                                            <td width="50%"><strong>Actual Degree</strong></td>
                                            <td><a href="#" class="text-input" data-type="text" id="actual_degree" name="actual_degree"><?php echo $data['actual_degree']; ?></a></td>
                                        </tr>
                                        <!--
                                        <tr>
                                            <td width="50%"><strong>Designation Id</strong></td>
                                            <td><?php echo getDesignationNameformCode($data['designation_id']); ?></td>
                                        </tr>
                                        -->
                                        <tr>
                                            <td width="50%"><strong>Sanctioned Post ID</strong></td>
                                            <td><?php echo $data['sanctioned_post_id']; ?></td>
                                        </tr>

                                        <tr>
                                            <td width="50%"><strong>Staff ID</strong></td>
                                            <td><?php echo $data['staff_id']; ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Organization Code</strong></td>
                                            <td><?php echo $data['org_code']; ?></td>
                                        </tr>
                                        <!--
                                        <tr>
                                            <td width="50%"><strong>Posting Status</strong></td>
                                            <td></td>
                                        </tr>
                                      
                                        <tr>
                                            <td width="50%"><strong>Staff Local ID</strong></td>
                                            <td><a href="#" class="text-input" data-type="text" id="staff_local_id"><?php echo $data['staff_local_id']; ?></a></td>
                                        </tr>  -->
                                        <tr>
                                            <td width="50%"><strong>Reside in Govt. Quarter?</strong></td><!-- govt_quarter_id-->
                                            <td><a href="#" id="govt_quarter_id" name="govt_quarter_id"><?php echo getGovtQuarter($data['govt_quarter_id']); ?></a></td>
                                        </tr>
                                        <!--
                                        <tr>
                                            <td width="50%"><strong>Job Status</strong></td>
                                            <td><a href="#" class="text-input" data-type="text" id="job_status" name="job_status"><?php echo $data['job_status']; ?></a></td>
                                        </tr>
                                        -->
                                        <tr>
                                            <td width="50%"><strong>Further Remarks/Explanation:</strong></td><!--reason -->
                                            <td><a href="#" class="text-input" data-type="text" id="reason" name="reason"><?php echo $data['reason']; ?></a> </td>
                                        </tr>
                                        <tr>
                                            <td width="50%"><strong>Last Updated On</strong></td>
                                            <td><?php echo $data['last_update']; ?></td>
                                        </tr>

                                    </table>
                                    <?php
// add new employee
                                elseif ($display_mode == "new") :
                                    ?>
                                    <form class="form-horizontal" action="<?php echo "post/post_new_staff.php"; ?>" method="post" id="new_employee">
                                        <fieldset>
                                            <table class="table table-striped">
                                                <tr>
                                                    <td width="50%"><strong>Organization Name</strong></td>
                                                    <td><?php echo $org_name; ?></td>
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>Organization Code</strong></td>
                                                    <td><?php echo $org_code; ?></td>
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>Sanctioned Post ID</strong></td>
                                                    <td>
                                                        <?php echo $sanctioned_post_id; ?>
                                                        <input type="hidden" name="sanctioned_post" value="<?php echo $sanctioned_post_id; ?>" />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>Staff ID</strong></td>
                                                    <td><?php echo $data['staff_id']; ?> <em class="text-success">(Will be added automatically)</em> </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>Staff Name</strong></td>
                                                    <td>
                                                        <input type="text" id="staff_name" name="staff_name" placeholder="" required>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>Staff Name (Bangla)</strong></td>
                                                    <td>
                                                        <input type="text" id="staff_bangla_name" name="staff_bangla_name" placeholder="">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>National ID</strong></td>
                                                    <td>
                                                        <input type="text" id="staff_national_id" name="staff_national_id" placeholder="">
                                                    </td>                                                    
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>Code No.(Doctors Only):</strong></td>
                                                    <td>
                                                        <input type="text" id="staff_pds_code" name="staff_pds_code" placeholder="" >
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>Department:</strong></td>
                                                    <td>
                                                        <?php
                                                        $sql = "SELECT
                                                                                very_old_departments.`name`,very_old_departments.`department_id`
                                                                                FROM
                                                                                very_old_departments order by name asc";
                                                        $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>getStaffDepertmentFromDepertmentId:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                                        ?>                                   <select id="staff_department_id" name="staff_department_id" >
                                                            <option value="0">-- Select form the list --</option>
                                                            <?php while ($data = mysql_fetch_assoc($result)): ?>
                                                                <option value="<?php echo $data['department_id']; ?>"><?php echo $data['name']; ?></option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>Date of Birth</strong></td>
                                                    <td>
                                                        <input type="text" id="birth_date" name="birth_date" placeholder="yyy-mm-dd" class='date-data' >
                                                    </td>
                                                </tr>


                                                <tr>
                                                    <td width="50%"><strong>Sex</strong></td>
                                                    <td>
                                                        <?php
                                                        $sql = "SELECT staff_sex.sex_name,staff_sex.sex_type_id
                                                                    FROM
                                                                    staff_sex order by sex_name";
                                                        $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>checkPasswordIsCorrect:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                                        ?>
                                                        <select id="staff_sex" name="staff_sex" >
                                                            <option value="0">-- Select form the list --</option>
                                                            <?php while ($data = mysql_fetch_assoc($result)): ?>
                                                                <option value="<?php echo $data['sex_type_id']; ?>"><?php echo $data['sex_name']; ?></option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>Religious Group</strong></td>
                                                    <td>
                                                        <?php
                                                        $sql = "SELECT staff_religious_group.religious_group_name,staff_religious_group.religious_group_id
                                                                    FROM
                                                                    staff_religious_group order by religious_group_name";
                                                        $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>checkPasswordIsCorrect:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                                        ?>
                                                        <select id="staff_religion" name="staff_religion" >
                                                            <option value="0">-- Select form the list --</option>
                                                            <?php while ($data = mysql_fetch_assoc($result)): ?>
                                                                <option value="<?php echo $data['religious_group_id']; ?>"><?php echo $data['religious_group_name']; ?></option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>Marital Status</strong></td>
                                                    <td>
                                                        <?php
                                                        $sql = "SELECT
                                                                staff_marital_status.marital_status, staff_marital_status.marital_status_id
                                                                FROM
                                                                staff_marital_status";
                                                        $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>checkPasswordIsCorrect:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                                        ?>
                                                        <select id="staff_marital_status" name="staff_marital_status" >
                                                            <option value="0">-- Select form the list --</option>
                                                            <?php while ($data = mysql_fetch_assoc($result)): ?>
                                                                <option value="<?php echo $data['marital_status_id']; ?>"><?php echo $data['marital_status']; ?></option>
                                                            <?php endwhile; ?>
                                                        </select>

                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>Father's Name</strong></td>
                                                    <td>
                                                        <input type="text" id="father_name" name="father_name" placeholder="" >
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>Mother's Name</strong></td>
                                                    <td>
                                                        <input type="text" id="mother_name" name="mother_name" placeholder="" >
                                                    </td>
                                                </tr>
                                                <!--
                                                   <tr>
                                                    <td width="50%"><strong>Date of Birth</strong></td>
                                                    <td>
                                                         
                                                        <input type="text" id="date_of_birth" name="date_of_birth" >
                                                    </td>
                                                </tr>
                                                -->
                                                <tr>
                                                    <td width="50%"><strong>Contact No.</strong></td>
                                                    <td>
                                                        <input type="text" id="contact_no" name="contact_no" placeholder="" required,number>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>Email</strong></td>
                                                    <td>
                                                        <input type="text" id="email_address1" name="email_address1" placeholder="" required,email>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>Present Address</strong></td>
                                                    <td>
                                                        <textarea type="text" id="present_address" name="present_address" placeholder="" ></textarea>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>Permanent Address</strong></td>
                                                    <td>
                                                        <textarea type="text" id="permanent_address" name="permanent_address" placeholder="" ></textarea>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>Freedom Fighter? </strong></td>
                                                    <td>
                                                        <?php
                                                        $sql2 = "SELECT staff_freedom_fighter.id,staff_freedom_fighter.freedom_fighter_name
                                                            FROM
                                                            staff_freedom_fighter";
                                                        $result2 = mysql_query($sql2) or die(mysql_error() . "<br /><br />Code:<b>getSalaryDrawTypeNameFromID:1</b><br /><br /><b>Query:</b><br />___<br />$sql2<br />");
                                                        ?>
                                                        <select id="staff_freedom_fighter" name="staff_freedom_fighter" >
                                                            <option value="0">-- Select form the list --</option>
                                                            <?php while ($data2 = mysql_fetch_assoc($result2)): ?>
                                                                <option value="<?php echo $data2['id']; ?>"><?php echo $data2['freedom_fighter_name']; ?></option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>Tribal?</strong></td>
                                                    <td>
                                                        <?php
                                                        $sql = "SELECT staff_tribal.id, staff_tribal.tribal_value
                                                           FROM
                                                           staff_tribal";
                                                        $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>getSalaryDrawTypeNameFromID:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                                        ?>
                                                        <select id="staff_tribal_id" name="staff_tribal_id" >

                                                            <?php while ($data = mysql_fetch_assoc($result)): ?>
                                                                <option value="<?php echo $data['id']; ?>"><?php echo $data['tribal_value']; ?></option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>Job Class</strong></td>
                                                    <td>
                                                        <?php
                                                        $sql = "SELECT staff_job_class.job_class_name,staff_job_class.job_class_id
                                                                FROM
                                                                staff_job_class ";
                                                        $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>checkPasswordIsCorrect:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                                        ?>
                                                        <select id="staff_job_class_value" name="staff_job_class_value" >
                                                            <option value="0">-- Select form the list --</option>
                                                            <?php while ($data = mysql_fetch_assoc($result)): ?>
                                                                <option value="<?php echo $data['job_class_id']; ?>"><?php echo $data['job_class_name']; ?></option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>Category</strong></td>
                                                    <?php
                                                    $sql = "SELECT
													  staff_professional_category_type.professional_type_name,staff_professional_category_type.professional_type_id
													FROM
													  staff_professional_category_type order by professional_type_id";
                                                    $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>getProfessionalCategoryFromId:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                                    ?>
                                                    <td><select id="staff_professional_categories" name="staff_professional_categories" >
                                                            <option value="0">-- Select form the list --</option>
                                                            <?php while ($data = mysql_fetch_assoc($result)): ?>
                                                                <option value="<?php echo $data['professional_type_id']; ?>"><?php echo $data['professional_type_name']; ?></option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>Post Type</strong></td>
                                                    <td> <?php
                                                        $sql = "SELECT
                                                    staff_post_type.post_type_name,staff_post_type.post_type_id
                                                    FROM
                                                    staff_post_type ";
                                                        $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>getPostTypeFromId:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                                        ?>

                                                        <select id="staff_post_type_id" name="staff_post_type_id" >

                                                            <?php while ($data = mysql_fetch_assoc($result)): ?>
                                                                <option value="<?php echo $data['post_type_id']; ?>"><?php echo $data['post_type_name']; ?></option>
                                                            <?php endwhile; ?>
                                                        </select>

                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>Staff Posting</strong></td>
                                                    <td>
                                                        <?php
                                                        $sql = "SELECT
                                                    staff_posting_type.staff_posting_type_name, staff_posting_type.staff_posting_type_id
                                                    FROM
                                                    staff_posting_type
                                                   ";
                                                        $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>getStaffPostingTypeFormId:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                                        ?>    <select id="staff_posting_type_id" name="staff_posting_type_id" >

                                                            <?php while ($data = mysql_fetch_assoc($result)): ?>
                                                                <option value="<?php echo $data['staff_posting_type_id']; ?>"><?php echo $data['staff_posting_type_name']; ?></option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>Salary drawn from which head:</strong></td><!-- draw_type_id-->
                                                    <td> <?php
                                                        $sql = "SELECT
            staff_salary_draw_type.salary_draw_type_name,staff_salary_draw_type.salary_draw_type_id
            FROM
            staff_salary_draw_type order by salary_draw_type_id asc ";
                                                        $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>getSexNameFromId:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                                        ?>
                                                        <select id="staff_salary_draw_type_id" name="staff_salary_draw_type_id" >

                                                            <?php while ($data = mysql_fetch_assoc($result)): ?>
                                                                <option value="<?php echo $data['salary_draw_type_id']; ?>"><?php echo $data['salary_draw_type_name']; ?></option>
                                                            <?php endwhile; ?>
                                                        </select></td>
                                                </tr>

                                                <tr>
                                                    <td><strong>Designation Type</strong></td>
                                                    <td>  <?php
                                                        $sql = "SELECT
              staff_designation_type.designation_type,  staff_designation_type.designation_type_id
            FROM
             staff_designation_type order by designation_type_id asc";
                                                        $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>getSexNameFromId:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                                        ?>  <select id="staff_designation_type_id" name="staff_designation_type_id" >

                                                            <?php while ($data = mysql_fetch_assoc($result)): ?>
                                                                <option value="<?php echo $data['designation_type_id']; ?>"><?php echo $data['designation_type']; ?></option>
                                                            <?php endwhile; ?>
                                                        </select></td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>Posted As</strong></td> <!--job_posting_id-->
                                                    <td>  <?php
                                                        $sql = "SELECT staff_job_posting.job_posting_name,staff_job_posting.job_posting_id
            FROM
            staff_job_posting";
                                                        $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>getSexNameFromId:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                                        ?>
                                                        <select id="staff_job_posting_id" name="staff_job_posting_id" >

                                                            <?php while ($data = mysql_fetch_assoc($result)): ?>
                                                                <option value="<?php echo $data['job_posting_id']; ?>"><?php echo $data['job_posting_name']; ?></option>
                                                            <?php endwhile; ?>
                                                        </select></td>
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>Working Status</strong></td>
                                                    <td> <?php
                                                        $sql = "SELECT
            staff_working_status.working_status_name,staff_working_status.working_status_id
            FROM
            staff_working_status order by working_status_id asc ";
                                                        $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>getSexNameFromId:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                                        ?> <select id="staff_working_status_id" name="staff_working_status_id" >

                                                            <?php while ($data = mysql_fetch_assoc($result)): ?>
                                                                <option value="<?php echo $data['working_status_id']; ?>"><?php echo $data['working_status_name']; ?></option>
                                                            <?php endwhile; ?>
                                                        </select> </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>Draw Salary from which place:</strong></td> <!-- draw_salary_id-->
                                                    <td>  <?php
                                                        $sql = "SELECT
                                                    staff_draw_salaray_place.draw_salaray_place,staff_draw_salaray_place.draw_salary_id
                                                    FROM
                                                    staff_draw_salaray_place order by draw_salary_id asc
                                                   ";
                                                        $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>getSalaryDrawTypeNameFromID:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                                        ?>    <select id="staff_draw_salary_id" name="staff_draw_salary_id" >

                                                            <?php while ($data = mysql_fetch_assoc($result)): ?>
                                                                <option value="<?php echo $data['draw_salary_id']; ?>"><?php echo $data['draw_salaray_place']; ?></option>
                                                            <?php endwhile; ?>
                                                        </select></td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>Current Basic Pay (Tk.):</strong></td>
                                                    <td> <input type="text" id="current_basic_pay_tk" name="current_basic_pay_tk" placeholder="" number ></td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>IST (In Service Training) GO No.:</strong></td>
                                                    <td> <input type="text" id="ist_go" name="ist_go" placeholder="" ></td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>IST SL No.:</strong></td>
                                                    <td> <input type="text" id="ist_sl_no" name="ist_sl_no" placeholder="" ></td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>IST Appointment Date:</strong></td>
                                                    <td> <input type="text" id="ist_appoinment_date" name="ist_appoinment_date" placeholder="yyyy-mm-dd" class='date-data'></td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>IST Year:</strong></td>
                                                    <td> <input type="text" id="ist_year" name="ist_year" placeholder="yyyy" number ></td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>BCS Batch No.:</strong></td>
                                                    <td> <input type="text" id="bcs_batch_no" name="bcs_batch_no" placeholder="" ></td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>BCS/PSC Regularization GO:</strong></td>
                                                    <td> <input type="text" id="bcs_psc_regularization_go" name="bcs_psc_regularization_go" placeholder="" ></td>
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>BCS/PSC Regularization SL No.:</strong></td>
                                                    <td> <input type="text" id="bcs_psc_regularization_sl_no" name="bcs_psc_regularization_sl_no" placeholder="" ></td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>BCS/PSC Regularization Date:</strong></td>
                                                    <td> <input type="text" id="bcs_psc_regularization_date" name="bcs_psc_regularization_date"  placeholder="yyyy-mm-dd" class='date-data' ></td>
                                                </tr>


                                                <tr>
                                                    <td width="50%"><strong>Service Confirmation GO:</strong></td>
                                                    <td> <input type="text" id="service_confirmation_go" name="service_confirmation_go" placeholder="" ></td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>Service Confirmation SL No.:</strong></td>
                                                    <td> <input type="text" id="service_confirmation_sl_no" name="service_confirmation_sl_no" placeholder="" ></td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>Service Confirmation Date:</strong></td>
                                                    <td> <input type="text" id="service_confirmation_date" name="service_confirmation_date"  placeholder="yyyy-mm-dd" class='date-data' ></td>
                                                </tr>


                                                <tr>
                                                    <td width="50%"><strong>Date Of Joining to Govt. Health Service</strong></td>
                                                    <td> <input type="text" id="date_of_joining_to_govt_service" name="date_of_joining_to_govt_service" placeholder="yyyy-mm-dd" class='date-data'></td>
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>Date Of Joining to Current Place</strong></td>
                                                    <td> <input type="text" id="date_of_joining_to_current_place" name="date_of_joining_to_current_place" placeholder="yyyy-mm-dd" class='date-data' ></td>
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>Date Of Joining to Current Designation</strong></td>
                                                    <td> <input type="text" id="date_of_joining_to_current_designation" name="date_of_joining_to_current_designation" placeholder="yyyy-mm-dd" class='date-data' ></td>

                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>Professional Discipline of Current Designation</strong></td>
                                                    <td>  <?php
                                                        $sql = "SELECT
                                                                                very_old_departments.`name`,very_old_departments.`department_id`
                                                                                FROM
                                                                                very_old_departments order by name asc";

                                                        $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>getTypeOfPostNameFromCode:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                                        ?> <select id="staff_professional_discipline" name="staff_professional_discipline" >
                                                            <option value="0">-- Select form the list --</option>
                                                            <?php while ($data = mysql_fetch_assoc($result)): ?>
                                                                <option value="<?php echo $data['department_id']; ?>"><?php echo $data['name']; ?></option>
                                                            <?php endwhile; ?>
                                                        </select></td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>Education Qualification</strong></td><!--type_of_educational_qualification-->
                                                    <td>  <?php
                                                        $sql = "SELECT
                staff_educational_qualification.educational_qualification, staff_educational_qualification.educational_qualifiaction_Id
            FROM
                staff_educational_qualification";
                                                        $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>getSalaryDrawTypeNameFromID:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                                        ?>    <select id="staff_educational_qualifiaction_Id" name="staff_educational_qualifiaction_Id" >

                                                            <?php while ($data = mysql_fetch_assoc($result)): ?>
                                                                <option value="<?php echo $data['educational_qualifiaction_Id']; ?>"><?php echo $data['educational_qualification']; ?></option>
                                                            <?php endwhile; ?>
                                                        </select></td>
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>Actual Degree</strong></td>
                                                    <td> <input type="text" id="actual_degree" name="actual_degree" placeholder="" ></td>
                                                </tr>
                                                <tr>
                                                    <td width="50%"><strong>Pay Scale of Current Designation</strong></td>
                                                    <td>  <?php
                                                        $sql = "SELECT
                staff_pay_scale.pay_scale, staff_pay_scale.pay_scale_id
            FROM
                staff_pay_scale order by pay_scale_id asc";
                                                        $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>getTypeOfPostNameFromCode:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                                        ?> <select id="staff_pay_scale_id" name="staff_pay_scale_id" >

                                                            <?php while ($data = mysql_fetch_assoc($result)): ?>
                                                                <option value="<?php echo $data['pay_scale_id']; ?>"><?php echo $data['pay_scale']; ?></option>
                                                            <?php endwhile; ?>
                                                        </select></td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong> Reside in Govt. Quarter?</strong></td>
                                                    <td> <?php
                                                        $sql = "SELECT staff_govt_quater.govt_quater,staff_govt_quater.govt_quater_id FROM staff_govt_quater";
                                                        $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>getSalaryDrawTypeNameFromID:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                                        ?>  
                                                        <select id="staff_govt_quarter" name="staff_govt_quarter" >

                                                            <?php while ($data = mysql_fetch_assoc($result)): ?>
                                                                <option value="<?php echo $data['govt_quater_id']; ?>"><?php echo $data['govt_quater']; ?></option>
                                                            <?php endwhile; ?>
                                                        </select></td>
                                                </tr>

                                                <tr>
                                                    <td width="50%"><strong>Further Remarks/Explanation:</strong></td><!--reason -->
                                                    <td>   <textarea type="text" id="reason" name="reason" placeholder="" ></textarea>  </td>
                                                </tr>

                                                <input type="hidden" id="new_staff" name="new_staff" value="yes" >


                                                <tr>
                                                    <td width="50%"><strong></strong></td>
                                                    <td><button type="submit" class="btn btn-success btn-large">Submit</button></td>
                                                </tr>
                                            </table>
                                        </fieldset>
                                    </form>
                                    <?php
                                else:
                                    // echo "ELSE";
                                    ?>

                                <?php endif; ?>
                            </div>
                        </div>

                    </section>

                </div>
            </div>

        </div>



        <!-- Footer
        ================================================== -->
        <?php include_once 'include/footer/footer_menu.inc.php'; ?>



        <!-- Le javascript
        ================================================== -->
        <script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
        <script src="assets/js/jquery.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>

        <script src="assets/js/holder/holder.js"></script>
        <script src="assets/js/google-code-prettify/prettify.js"></script>

        <script src="assets/js/application.js"></script>


        <script src="library/bootstrap-editable/js/bootstrap-editable.min.js"></script>

        <link href="assets/css/datepicker.css" rel="stylesheet">
        <script src="assets/js/bootstrap-datepicker.js"></script>

        <script>


                                        $('.date-data').datepicker({
                                            format: 'yyyy-mm-dd'
                                        });


                                        $(document).ready(function() {



                                            $("#new_employee").validate({
                                                rules: {
                                                    staff_name: "required",
                                                    contact_no: {
                                                        required: true,
                                                        number: true
                                                    },
                                                    email_address1: {
                                                        email: true
                                                    },
                                                    current_basic_pay_tk: {number: true}
                                                },
                                                messages: {
                                                    staff_name: "You must type the name",
                                                    contact_no: {
                                                        required: "You must type contact number",
                                                        number: "Please write only numbers (0 - 9)!"
                                                    },
                                                    email_address1: {
                                                        email: "Please write a valid email address"
                                                    },
                                                    current_basic_pay_tk: {number: "Please write only numbers (0 - 9)!"}
                                                }

                                            });

                                        });



        </script>
        <script>
            $.fn.editable.defaults.mode = 'inline';

            var staff_id = <?php echo $staff_id; ?>;
            var org_code = <?php echo $org_code; ?>;

            function IsNumeric(input)
            {
                return (input - 0) == input && (input + '').replace(/^\s+|\s+$/g, "").length > 0;
            }

            function ValidateEmail(mail)
            {
                if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(myForm.emailAddr.value))
                {
                    return (true)
                }
                alert("Your email address is not valid!")
                return (false)
            }



            var staff_name = $('#staff_name').val();
            var contact_no = $('#contact_no').val();
            var current_basic_pay_taka = $('#current_basic_pay_taka').val();
            var email_address = $('#email_address').val();
            var birth_date = $('#birth_date').val();


            $('#employee-profile #staff_name').editable({
                validate: function(staff_name) {
                    if ($.trim(staff_name) === '')
                        return 'You must type staff name.';
                },
                type: 'text',
                url: 'post/post_employee.php',
                pk: <?php echo $staff_id; ?>,
                params: function(params) {
                    params.org_code = <?php echo $org_code; ?>;
                    return params;
                }

            });

            $('#contact_no').editable({
                validate: function(contact_no) {
                    if ($.trim(contact_no) === '') {
                        return 'You must type contact no.';
                    }
                    else if (!IsNumeric(contact_no)) {
                        return 'Please write only numers (0 - 9) !';
                    }
                },
                type: 'text',
                url: 'post/post_employee.php',
                pk: <?php echo $staff_id; ?>,
                params: function(params) {
                    params.org_code = <?php echo $org_code; ?>;
                    return params;
                }

            });

            $('#current_basic_pay_taka').editable({
                validate: function(current_basic_pay_taka) {
                    if (!IsNumeric(current_basic_pay_taka)) {
                        return 'Please write only numers (0 - 9) !';
                    }
                },
                type: 'text',
                url: 'post/post_employee.php',
                pk: <?php echo $staff_id; ?>,
                params: function(params) {
                    params.org_code = <?php echo $org_code; ?>;
                    return params;
                }

            });

            /*
             $('#email_address').editable({
             validate: function(email_address) {
             if (!ValidateEmail(email_address)) {
             return 'Your email address is not valid';
             }
             },
             type: 'text',
             url: 'post/post_employee.php',
             pk: <?php echo $staff_id; ?>,
             params: function(params) {
             params.org_code = <?php echo $org_code; ?>;
             return params;
             }
             
             });
             */





            $('#employee-profile a.text-input').editable({
                type: 'text',
                pk: <?php echo $staff_id; ?>,
                url: 'post/post_employee.php',
                params: function(params) {
                    params.org_code = <?php echo $org_code; ?>;
                    return params;
                }

            });

            $('#employee-profile a.date-input').editable({
                type: 'date',
                pk: <?php echo $staff_id; ?>,
                url: 'post/post_employee.php',
                format: 'yyyy-mm-dd',
                datepicker: {
                    weekStart: 1
                },
                params: function(params) {
                    params.org_code = <?php echo $org_code; ?>;
                    return params;
                }
            });

            $('#employee-profile a.date-textarea').editable({
                type: 'textarea',
                pk: <?php echo $staff_id; ?>,
                url: 'post/post_employee.php',
                rows: 5,
                params: function(params) {
                    params.org_code = <?php echo $org_code; ?>;
                    return params;
                }
            });
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd'
            });


            //post_type_id
            $(function() {
                $('#post_type_id').editable({
                    type: 'select',
                    pk: staff_id,
                    url: 'post/post_employee.php',
                    source: 'get/get_post_type_id.php',
                    params: function(params) {
                        params.org_code = org_code;
                        return params;
                    }
                });
            });

            //get_department_id
            $(function() {
                $('#department_id').editable({
                    type: 'select',
                    pk: staff_id,
                    url: 'post/post_employee.php',
                    source: 'get/get_department_id.php',
                    params: function(params) {
                        params.org_code = org_code;
                        return params;
                    }
                });
            });

            //religion
            $(function() {
                $('#religion').editable({
                    type: 'select',
                    pk: staff_id,
                    url: 'post/post_employee.php',
                    source: 'get/get_religous_id.php',
                    params: function(params) {
                        params.org_code = org_code;
                        return params;
                    }
                });
            });

            //marital_status
            $(function() {
                $('#marital_status').editable({
                    type: 'select',
                    pk: staff_id,
                    url: 'post/post_employee.php',
                    source: 'get/get_marital_status_id.php',
                    params: function(params) {
                        params.org_code = org_code;
                        return params;
                    }
                });
            });

            //sex
            $(function() {
                $('#sex').editable({
                    type: 'select',
                    pk: staff_id,
                    url: 'post/post_employee.php',
                    source: 'get/get_sex_type_id.php',
                    params: function(params) {
                        params.org_code = org_code;
                        return params;
                    }
                });
            });

            //freedom_fighter_idtribal_id
            $(function() {
                $('#freedom_fighter_id').editable({
                    type: 'select',
                    pk: staff_id,
                    url: 'post/post_employee.php',
                    source: 'get/get_freedom_fighter_id.php',
                    params: function(params) {
                        params.org_code = org_code;
                        return params;
                    }
                });
            });

            //tribal_id
            $(function() {
                $('#tribal_id').editable({
                    type: 'select',
                    pk: staff_id,
                    url: 'post/post_employee.php',
                    source: 'get/get_tribal_id.php',
                    params: function(params) {
                        params.org_code = org_code;
                        return params;
                    }
                });
            });

            //staff_job_class
            $(function() {
                $('#staff_job_class').editable({
                    type: 'select',
                    pk: staff_id,
                    url: 'post/post_employee.php',
                    source: 'get/get_job_class_id.php',
                    params: function(params) {
                        params.org_code = org_code;
                        return params;
                    }
                });
            });


            // staff_professional_category
            $(function() {
                $('#staff_professional_category').editable({
                    type: 'select',
                    pk: staff_id,
                    url: 'post/post_employee.php',
                    source: 'get/get_staff_professional_category_id.php',
                    params: function(params) {
                        params.org_code = org_code;
                        return params;
                    }
                });
            });

            //  staff_posting
            $(function() {
                $('#staff_posting').editable({
                    type: 'select',
                    pk: staff_id,
                    url: 'post/post_employee.php',
                    source: "get/get_staff_posting_type.php",
                    params: function(params) {
                        params.org_code = org_code;
                        return params;
                    }
                });
            });

            //  work_status
            $(function() {
                $('#working_status_id').editable({
                    type: 'select',
                    pk: staff_id,
                    url: 'post/post_employee.php',
                    source: "get/get_working_status_id.php",
                    params: function(params) {
                        params.org_code = org_code;
                        return params;
                    }
                });
            });


            // staff job posting

            $(function() {
                $('#job_posting_id').editable({
                    type: 'select',
                    pk: staff_id,
                    url: 'post/post_employee.php',
                    source: "get/get_job_posting_id.php",
                    params: function(params) {
                        params.org_code = org_code;
                        return params;
                    }
                });
            });
            // salary draw type

            $(function() {
                $('#draw_type_id').editable({
                    type: 'select',
                    pk: staff_id,
                    url: 'post/post_employee.php',
                    source: "get/get_draw_type_id.php",
                    params: function(params) {
                        params.org_code = org_code;
                        return params;
                    }
                });
            });

            // salary draw head
            $(function() {
                $('#draw_salary_id').editable({
                    type: 'select',
                    pk: staff_id,
                    url: 'post/post_employee.php',
                    source: "get/get_draw_salary_id.php",
                    params: function(params) {
                        params.org_code = org_code;
                        return params;
                    }
                });
            });

            // education qualification

            //$(function() {
            //    $('#type_of_educational_qualification').editable({
            //        type: 'select',
            //        pk: staff_id,
            //        source: "get/get_type_of_educational_qualification.php",
            //        params: function(params) {
            //            params.org_code = org_code;
            //            return params;
            //        }
            //    });
            //});

            // Govt Quater

            $(function() {
                $('#govt_quarter_id').editable({
                    type: 'select',
                    pk: staff_id,
                    url: 'post/post_employee.php',
                    source: "get/get_govt_quater_id.php",
                    params: function(params) {
                        params.org_code = org_code;
                        return params;
                    }
                });
            });

            // Designation Type

            $(function() {
                $('#designation_type_id').editable({
                    type: 'select',
                    pk: staff_id,
                    url: 'post/post_employee.php',
                    source: "get/get_designation_type_id.php",
                    params: function(params) {
                        params.org_code = org_code;
                        return params;
                    }
                });
            });

            // Professional discipline

            $(function() {
                $('#professional_discipline_of_current_designation').editable({
                    type: 'select',
                    pk: staff_id,
                    url: 'post/post_employee.php',
                    source: "get/get_professional_discipline_id.php",
                    params: function(params) {
                        params.org_code = org_code;
                        return params;
                    }
                });
            });

            //type_of_educational_qualification
            $(function() {
                $('#type_of_educational_qualification').editable({
                    type: 'checklist',
                    value: type_of_educational_qualification,
                    pk: staff_id,
                    url: 'post/post_employee.php',
                    source: 'get/get_type_of_educational_qualification.php',
                    params: function(params) {
                        params.org_code = org_code;
                        params.post_type = 'checklist';
                        return params;
                    }
                });
            });

            //pay scale
            $(function() {
                $('#pay_scale_of_current_designation').editable({
                    type: 'select',
                    value: pay_scale_of_current_designation,
                    pk: staff_id,
                    url: 'post/post_employee.php',
                    source: 'get/get_pay_scale_of_designation.php',
                    params: function(params) {
                        params.org_code = org_code;
                        return params;
                    }
                });
            });

        </script>
        <script src="assets/js/common.js"></script>

        <script src="assets/jquery Validation/dist/jquery.validate.js"></script>
    </body>
</html>
