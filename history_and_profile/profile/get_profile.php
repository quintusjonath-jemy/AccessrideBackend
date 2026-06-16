<?php

include "../config/database.php";


$user_id = 1;


$sql = "SELECT * FROM users WHERE id='$user_id'";


$result = $conn->query($sql);


if($result->num_rows > 0){

    $user = $result->fetch_assoc();

    echo json_encode($user);

}
else{

    echo json_encode([
        "message"=>"User not found"
    ]);

}


$conn->close();

?>