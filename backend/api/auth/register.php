<?php
include '../../config/connection.php';
include '../../config/utilities/intro.php';
include '../../config/utilities/encryption.php';


if(isset($_POST['register'])){
    //Declearing the variables
    $fullname = cleanme($_POST['fullname']);
    $email = cleanme($_POST['email']);
    $password = Password_encrypt(cleanme($_POST['password']));


    if ($_POST['password'] == $_POST['confirm_password']){
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = $connection->query($sql);
        if(mysqli_num_rows($result) > 0){
            echo json_encode(
                array(
                    'status' => false,
                    'message' => 'Registration Unsuccessful',
                    'error' => array('email'=>'<i class="bx bxs-error-circle"></i> &nbsp; An account already exists using this email.')
                    )
                );
        }
        else{
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                echo json_encode(array(
                    'status' => false,
                    'message' => 'Registration Unsuccessful',
                    'error' => array(
                        'email'=>'<i class="bx bxs-error-circle"></i> &nbsp; Enter valid email address.'
                        )
                    )
                );
            }
            else if(!empty($password) && !empty($email) && !empty($fullname)){
                $sql = "INSERT INTO `users` (`name`,`email`,`password`,`status`) VALUES ('$fullname', '$email','$password','1')";
                $ref = generateShortKey(7);
                $result = $connection->query($sql);
                
                //Adding a new notification (Utilities/Intro)
                addnotification($email,$fullname.' have successfully registered into the database','New User Created.',$ref,0);
                //Login Users In (Utilities/Intro)
                login_user($email);

                if(!$result){
                    echo json_encode(array(
                        'status' => false,
                        'message' => 'Error submitting form',
                        'error' => array(
                            'email'=>'<i class="bx bxs-error-circle"></i>  Enter valid email address.'
                            )
                        )
                    );
                }else{
                    echo json_encode(array(
                        'status'=>true,
                        'message'=>'Registration successful'
                    ));
                }
            }    
        }
    }else{
        echo json_encode(
            array(
                'status' => false,
                'message' => 'Registration Unsuccessful',
                'error' => 'Password not equal.'
            )
        );
    }
    



}

?>