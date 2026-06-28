<?php

include "../config/database.php";


$user_id = $_POST['id'];
$name = $_POST['name'];
$phone = $_POST['phone'];
$address = $_POST['address'];


$sql = "UPDATE users SET

name='$name',
phone='$phone',
address='$address'

WHERE id='$user_id'";


if($conn->query($sql)){

    echo "Profile Updated";

}
else{

    echo "Update Failed";

}


$conn->close();

?>