<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "security2";

$conn = new mysqli($servername, $username, $password,$database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Post functions
if(isset($_POST['submit'])) {
    if($_POST['username'] != '' && $_POST['password'] != '')
    {
        $username = $conn->real_escape_string($_POST["username"]);
        $password = $conn->real_escape_string($_POST["password"]);

        $passwordSalt = "Salting Password For Security";
        $password = md5($passwordSalt.$password.$passwordSalt);

        if($_POST['message'] != ''){
            $message = $conn->real_escape_string($_POST["message"]);
            $message = Encrypt($password, $message);
            //encrypt
            $sql = "INSERT INTO encrypt (username,password, message)
            VALUES ('".$username."', '".$password."','".$message."')";
            if ($conn->query($sql) === TRUE) {
                echo "New record created successfully";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }else{
            //decrypt
            $sql = "SELECT message FROM encrypt WHERE username='".$username."' AND password='".$password."'";
            $result = $conn->query($sql);
            if($result->num_rows > 0) {
                $row = $result->fetch_array();
                $message = $row["message"];
                echo "Het bericht is: ".Decrypt($password, $message);
            }else {
                echo "Gegevens bestaan niet.";
            }
        }
    }else{
        echo "Gegevens bestaan niet.";
    }

}

$conn->close();
echo '<a href="index.html">Terug</a>';

function Encrypt($password, $data)
{

    $salt = substr(md5(mt_rand(), true), 8);

    $key = md5($password . $salt, true);
    $iv  = md5($key . $password . $salt, true);

    $ct = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv);

    return base64_encode('Salted__' . $salt . $ct);
}

function Decrypt($password, $data)
{

    $data = base64_decode($data);
    $salt = substr($data, 8, 8);
    $ct   = substr($data, 16);

    $key = md5($password . $salt, true);
    $iv  = md5($key . $password . $salt, true);

    $pt = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ct, MCRYPT_MODE_CBC, $iv);

    return $pt;
}
