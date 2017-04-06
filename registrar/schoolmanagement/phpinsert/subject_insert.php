<?php
	session_start();
	require_once "../../../resources/config.php";
	include('../../../resources/classes/Popover.php');


	if(!$conn) {
		die("Connection failed: " . mysqli_connect_error());
	}
	
	$subj_id = 1;
	$statement = "SELECT * FROM pcnhsdb.subjects order by subj_id desc limit 1;";
	$result = $conn->query($statement);
	if ($result->num_rows > 0) {
	// output data of each row
		while($row = $result->fetch_assoc()) {
		$subj_id = $row['subj_id'];
		$subj_id = $subj_id+1;

		}
	}else {
		$subj_id = 1;
	}


	$subj_name = htmlspecialchars(filter_var($_POST['subj_name'], FILTER_SANITIZE_STRING), ENT_QUOTES, 'UTF-8');
	$subj_level = htmlspecialchars(filter_var($_POST['subj_level'], FILTER_SANITIZE_NUMBER_INT), ENT_QUOTES, 'UTF-8');
	$curriculum = $_POST['curr_id'];
	$program = $_POST['prog_id'];
	$yr_level_needed = htmlspecialchars(filter_var($_POST['yr_level_needed'], FILTER_SANITIZE_NUMBER_INT), ENT_QUOTES, 'UTF-8');
	$subj_order = intval(htmlspecialchars(filter_var($_POST['subj_order'], FILTER_SANITIZE_NUMBER_INT), ENT_QUOTES));

	$multipleinsert = "";
	//$insertprogram = "";
	$willInsert = true;
	if(is_null($curriculum)) {
		$willInsert = false;
		$popover = new Popover();
		$popover->set_popover("danger", "No Curriculum checked.");
		$_SESSION['error_pop'] = $popover->get_popover();
		header("Location: ".$_SERVER['HTTP_REFERER']);
	}
	if(is_null($program)) {
		$willInsert = false;
		$popover = new Popover();
		$popover->set_popover("danger", "No Program checked.");
		$_SESSION['error_pop'] = $popover->get_popover();
		header("Location: ".$_SERVER['HTTP_REFERER']);
	}

	if($subj_level !=  $yr_level_needed) {
		$willInsert = false;
		$popover = new Popover();
		$popover->set_popover("danger", "Subject Level must be equal to Year Level Needed.");
		$_SESSION['error_pop'] = $popover->get_popover();
		header("Location: ".$_SERVER['HTTP_REFERER']);
	}
	if($subj_level > 6 && $yr_level_needed > 6) {
		$yr_level_needed -= 6;
	}
	if($subj_order < 1 || !is_numeric($subj_order) || $subj_order > 20) {
		$willInsert = false;
		$popover = new Popover();
		$popover->set_popover("danger", "Invalid Subject Order");
		$_SESSION['error_pop'] = $popover->get_popover();
		header("Location: ".$_SERVER['HTTP_REFERER']);
	}

	$insertsubject = "INSERT INTO `pcnhsdb`.`subjects` (`subj_id`,`subj_name`, `subj_level`, `yr_level_needed`, `subj_order`) VALUES ('$subj_id', '$subj_name', '$subj_level', '$yr_level_needed', '$subj_order');";


	foreach ($curriculum as $key => $value) {
		# code...
		$curr_id = $curriculum[$key];
		$multipleinsert .= "INSERT INTO `pcnhsdb`.`subjectcurriculum` (`subj_id`,`curr_id`) VALUES ('$subj_id', '$curr_id');";
	}
	
	foreach ($program as $key => $value) {
		# code...
		$prog_id = $program[$key];
		$multipleinsert .= "INSERT INTO `pcnhsdb`.`subjectprogram` (`subj_id`,`prog_id`) VALUES ('$subj_id', '$prog_id');";
	}
	if($willInsert) {
		mysqli_query($conn, $insertsubject);
		mysqli_multi_query($conn, $multipleinsert);
		$_SESSION['user_activity'][] = "Added New Subject: $subj_name";
		mysqli_multi_query($conn, $insertprogram);
		echo "<p>Updating Database, please wait...</p>";
		header("Refresh:3; url=../student_subjects.php");
	}
	

	$conn->close();
?>