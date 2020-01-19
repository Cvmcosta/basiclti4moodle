<?php
$servername = getenv("DB_HOST");
$username = getenv("DB_USER");
$password = getenv("DB_PASS");
$dbname = getenv("DB_NAME");

$lti_host = getenv("LTI_HOST");

echo "Connecting to database ...\n";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT * FROM mdl_lti_types WHERE id=1";
$query = mysqli_query($conn, $sql);

if (!$query) {
  die('Error: ' . mysqli_error($con));
}

if(mysqli_num_rows($query) > 0){
  echo "Database already configured.\n";
  exit();
}

$t = "asdas" . "";

echo "Populating database ...\n";
$sql = "INSERT INTO mdl_lti_types (id, name, baseurl, tooldomain, state, course, coursevisible, ltiversion, clientid, toolproxyid, enabledcapability, parameter, icon, secureicon, createdby, timecreated, timemodified, description) VALUES ('1', 'LTI provider', 'http://" . $lti_host . "', '" . $lti_host . "', '1', '1', '1', '1.3.0', 'grRonGwE7uZ4pgo', NULL, NULL, NULL, '', '', '2', '1576430808', '1579225569', 'Demo local lti tool');";

$sql .= "INSERT INTO mdl_lti_types_config (typeid, name, value) VALUES ('1', 'publickey', '');";
$sql .= "INSERT INTO mdl_lti_types_config (typeid, name, value) VALUES ('1', 'initiatelogin', 'http://" . $lti_host . "/login');";
$sql .= "INSERT INTO mdl_lti_types_config (typeid, name, value) VALUES ('1', 'redirectionuris', 'http://" . $lti_host . "');";
$sql .= "INSERT INTO mdl_lti_types_config (typeid, name, value) VALUES ('1', 'customparameters', '');";
$sql .= "INSERT INTO mdl_lti_types_config (typeid, name, value) VALUES ('1', 'coursevisible', '1');";
$sql .= "INSERT INTO mdl_lti_types_config (typeid, name, value) VALUES ('1', 'launchcontainer', '3');";
$sql .= "INSERT INTO mdl_lti_types_config (typeid, name, value) VALUES ('1', 'contentitem', '0');";
$sql .= "INSERT INTO mdl_lti_types_config (typeid, name, value) VALUES ('1', 'ltiservice_gradesynchronization', '2');";
$sql .= "INSERT INTO mdl_lti_types_config (typeid, name, value) VALUES ('1', 'ltiservice_memberships', '1');";
$sql .= "INSERT INTO mdl_lti_types_config (typeid, name, value) VALUES ('1', 'ltiservice_toolsettings', '1');";
$sql .= "INSERT INTO mdl_lti_types_config (typeid, name, value) VALUES ('1', 'sendname', '1');";
$sql .= "INSERT INTO mdl_lti_types_config (typeid, name, value) VALUES ('1', 'sendemailaddr', '1');";
$sql .= "INSERT INTO mdl_lti_types_config (typeid, name, value) VALUES ('1', 'acceptgrades', '1');";
$sql .= "INSERT INTO mdl_lti_types_config (typeid, name, value) VALUES ('1', 'organizationid', '');";
$sql .= "INSERT INTO mdl_lti_types_config (typeid, name, value) VALUES ('1', 'organizationurl', '');";
$sql .= "INSERT INTO mdl_lti_types_config (typeid, name, value) VALUES ('1', 'forcessl', '0');";
$sql .= "INSERT INTO mdl_lti_types_config (typeid, name, value) VALUES ('1', 'servicesalt', '5df66cd88365f9.83446329');";
$sql .= "INSERT INTO mdl_lti_types_config (typeid, name, value) VALUES ('1', 'publickeyset', 'http://" . $lti_host . "/keys');";
$sql .= "INSERT INTO mdl_lti_types_config (typeid, name, value) VALUES ('1', 'keytype', 'JWK_KEYSET');";
  

if (mysqli_multi_query($conn, $sql)) {
    echo "Database successfully populated!";
} else {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}


mysqli_close($conn);
?>

