<?php

include "../config/database.php";


$user_id = 1;


$sql = "SELECT * FROM rides 
WHERE user_id='$user_id'";


$result = $conn->query($sql);


$rides=[];


while($row=$result->fetch_assoc()){

    $rides[]=$row;

}


echo json_encode($rides);


$conn->close();


?>