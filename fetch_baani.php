<?php
include 'dbcontroller.php';
$db_handle = new DBController();

$request = mysqli_real_escape_string($db_handle->connectDB(), $_POST["query"]);

$result = $db_handle->executeUpdate("SELECT * FROM baani WHERE baani_name LIKE '%".$request."%'");
$data = array();

if(mysqli_num_rows($result) > 0)
{
 while($row = mysqli_fetch_assoc($result))
 {
  $data[] = $row["baani_name"];
 }
 echo json_encode($data);
}

?>