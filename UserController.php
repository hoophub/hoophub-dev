<?php 
session_start();
require_once '../bl/UserMan.php';
require_once '../helper/sendEmail.php';

    $usermanagement = new UserManagement();

    if (isset($_POST['fName'], $_POST['lName'],$_POST['username'], $_POST['number'], $_POST['email'], $_POST['password'], $_POST['confPassword'],)) {
       // $usermanagement -> addUserFunc($_POST['fName'], $_POST['lName'], $_POST['username'], $_POST['number'], $_POST['email'], $_POST['password'], $_POST['confPassword'],);
        //exit;

        $fname = htmlspecialchars($_POST['fName']);
        $lname = htmlspecialchars($_POST['lName']);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $message = htmlspecialchars('WASAAAAAHHHH');

        if (!$email) {
            die("Invalid email");
        }

        $body = "
            <h3>New Message</h3>
            <p><strong>Name:</strong> $fname $lname</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Message:</strong> $message</p>
        ";

        $result = sendEmail(
            "patmosh.redor@gmail.com",
            'hoophub',
            "Email Sample Message",
            $body
        );

        if ($result === true) {
            echo "Email sent successfully";
        } else {
            echo "Failed: $result";
        }
        exit;
    }