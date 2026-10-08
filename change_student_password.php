<?php

session_start();


/* =========================================================
   STUDENT LOGIN CHECK
========================================================= */

if (
    !isset($_SESSION['student_logged_in']) ||
    $_SESSION['student_logged_in'] !== true
) {

    header("Location: student_login.php");
    exit();

}


include 'db_connect.php';


$student_id =
    $_SESSION['student_id'];

$message = "";

$error = "";


/* =========================================================
   GET STUDENT DETAILS
========================================================= */

$sql = "
    SELECT
        student_id,
        register_no,
        student_name,
        password,
        must_change_password
    FROM student
    WHERE student_id = ?
";


$stmt = mysqli_prepare(
    $conn,
    $sql
);


if (!$stmt) {

    die("Database error.");

}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $student_id
);


mysqli_stmt_execute($stmt);


$result =
    mysqli_stmt_get_result($stmt);


if (mysqli_num_rows($result) !== 1) {

    session_unset();
    session_destroy();

    header("Location: student_login.php");
    exit();

}


$student =
    mysqli_fetch_assoc($result);


mysqli_stmt_close($stmt);


/* =========================================================
   CHANGE PASSWORD
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $current_password =
        $_POST['current_password'] ?? '';

    $new_password =
        $_POST['new_password'] ?? '';

    $confirm_password =
        $_POST['confirm_password'] ?? '';


    /* =====================================================
       EMPTY FIELD CHECK
    ===================================================== */

    if (
        trim($current_password) === "" ||
        trim($new_password) === "" ||
        trim($confirm_password) === ""
    ) {

        $error =
            "Please fill in all fields.";

    }


    /* =====================================================
       CURRENT PASSWORD CHECK
    ===================================================== */

    else {

        $password_valid = false;


        /* -----------------------------------------------
           CHECK HASHED PASSWORD
        ------------------------------------------------ */

        if (
            !empty($student['password']) &&
            password_verify(
                $current_password,
                $student['password']
            )
        ) {

            $password_valid = true;

        }


        /* -----------------------------------------------
           CHECK TEMPORARY PLAIN PASSWORD
        ------------------------------------------------ */

        if (
            !$password_valid &&
            $student['password'] === $current_password
        ) {

            $password_valid = true;

        }


        if (!$password_valid) {

            $error =
                "Current password is incorrect.";

        }


        /* =================================================
           NEW PASSWORD LENGTH
        ================================================= */

        elseif (strlen($new_password) < 6) {

            $error =
                "New password must contain at least 6 characters.";

        }


        /* =================================================
           CONFIRM PASSWORD
        ================================================= */

        elseif (
            $new_password !== $confirm_password
        ) {

            $error =
                "New passwords do not match.";

        }


        /* =================================================
           SAME PASSWORD
        ================================================= */

        elseif (
            $new_password === $current_password
        ) {

            $error =
                "New password must be different from the current password.";

        }


        /* =================================================
           UPDATE PASSWORD
        ================================================= */

        else {


            /*
             * Always store the new password as a secure hash.
             */

            $hashed_password =
                password_hash(
                    $new_password,
                    PASSWORD_DEFAULT
                );


            $update_sql = "
                UPDATE student
                SET
                    password = ?,
                    must_change_password = 0
                WHERE student_id = ?
            ";


            $update_stmt =
                mysqli_prepare(
                    $conn,
                    $update_sql
                );


            if (!$update_stmt) {

                $error =
                    "Database error. Please try again.";

            }

            else {


                mysqli_stmt_bind_param(
                    $update_stmt,
                    "si",
                    $hashed_password,
                    $student_id
                );


                if (
                    mysqli_stmt_execute(
                        $update_stmt
                    )
                ) {

                    mysqli_stmt_close(
                        $update_stmt
                    );


                    /*
                     * Password has been changed successfully.
                     * Send the student directly to dashboard.
                     */

                    header(
                        "Location: student_dashboard.php"
                    );

                    exit();

                }

                else {

                    $error =
                        "Unable to change password. Please try again.";

                    mysqli_stmt_close(
                        $update_stmt
                    );

                }

            }

        }

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Change Student Password</title>


<style>

/* =========================================================
   RESET
========================================================= */

* {

    margin: 0;

    padding: 0;

    box-sizing: border-box;

}


/* =========================================================
   BODY
========================================================= */

body {

    min-height: 100vh;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:
        linear-gradient(
            135deg,
            #3b0764,
            #6d28d9,
            #9333ea
        );

    display: flex;

    justify-content: center;

    align-items: center;

    padding: 25px;

}


/* =========================================================
   CONTAINER
========================================================= */

.container {

    width: 100%;

    max-width: 460px;

}


/* =========================================================
   CARD
========================================================= */

.box {

    background: white;

    padding: 38px;

    border-radius: 20px;

    box-shadow:
        0 20px 50px
        rgba(0,0,0,0.25);

    position: relative;

    overflow: hidden;

}


/* =========================================================
   TOP LINE
========================================================= */

.box::before {

    content: "";

    position: absolute;

    top: 0;

    left: 0;

    width: 100%;

    height: 5px;

    background:
        linear-gradient(
            90deg,
            #6d28d9,
            #9333ea
        );

}


/* =========================================================
   ICON
========================================================= */

.icon {

    width: 78px;

    height: 78px;

    background: #f3e8ff;

    color: #6d28d9;

    border-radius: 20px;

    display: flex;

    justify-content: center;

    align-items: center;

    font-size: 38px;

    margin: 0 auto 18px;

}


/* =========================================================
   TITLE
========================================================= */

h1 {

    text-align: center;

    color: #3b0764;

    font-size: 26px;

    margin-bottom: 7px;

}


.welcome {

    text-align: center;

    color: #7c6f8a;

    font-size: 14px;

    margin-bottom: 25px;

}


.welcome strong {

    color: #6d28d9;

}


/* =========================================================
   ERROR
========================================================= */

.error {

    background: #fee2e2;

    color: #991b1b;

    border:
        1px solid #fecaca;

    padding: 13px;

    border-radius: 9px;

    margin-bottom: 20px;

    text-align: center;

    font-size: 14px;

    font-weight: bold;

}


/* =========================================================
   PASSWORD REQUIREMENTS
========================================================= */

.requirements {

    background: #faf5ff;

    border:
        1px solid #e9d5ff;

    border-left:
        4px solid #7c3aed;

    padding: 15px;

    border-radius: 9px;

    margin-bottom: 22px;

    color: #5b21b6;

    font-size: 13px;

    line-height: 1.7;

}


.requirements strong {

    display: block;

    color: #4c1d95;

    margin-bottom: 5px;

    font-size: 14px;

}


/* =========================================================
   FORM
========================================================= */

.form-group {

    margin-bottom: 19px;

}


label {

    display: block;

    margin-bottom: 8px;

    font-weight: bold;

    color: #4c1d95;

    font-size: 14px;

}


input {

    width: 100%;

    padding: 13px 14px;

    border:
        1px solid #ddd0eb;

    border-radius: 9px;

    background: #fcfaff;

    font-size: 14px;

    outline: none;

    transition: 0.3s;

}


input:focus {

    border-color: #7c3aed;

    background: white;

    box-shadow:
        0 0 0 3px
        rgba(124,58,237,0.10);

}


/* =========================================================
   BUTTON
========================================================= */

button {

    width: 100%;

    padding: 14px;

    border: none;

    border-radius: 9px;

    background:
        linear-gradient(
            135deg,
            #6d28d9,
            #9333ea
        );

    color: white;

    font-size: 15px;

    font-weight: bold;

    cursor: pointer;

    transition: 0.3s;

    box-shadow:
        0 5px 15px
        rgba(109,40,217,0.25);

}


button:hover {

    background:
        linear-gradient(
            135deg,
            #5b21b6,
            #7e22ce
        );

    transform: translateY(-2px);

}


/* =========================================================
   LOGOUT
========================================================= */

.back {

    display: block;

    text-align: center;

    margin-top: 22px;

    color: #6d28d9;

    text-decoration: none;

    font-size: 14px;

    font-weight: bold;

}


.back:hover {

    color: #4c1d95;

    text-decoration: underline;

}


/* =========================================================
   SECURITY NOTE
========================================================= */

.security-note {

    margin-top: 23px;

    padding: 13px;

    background: #faf5ff;

    border:
        1px solid #e9d5ff;

    border-radius: 9px;

    color: #6b21a8;

    font-size: 12px;

    text-align: center;

    line-height: 1.5;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 500px) {

    body {

        padding: 15px;

    }


    .box {

        padding: 30px 22px;

    }


    h1 {

        font-size: 24px;

    }

}

</style>

</head>


<body>


<div class="container">


    <div class="box">


        <!-- =================================================
             ICON
        ================================================== -->

        <div class="icon">

            🔐

        </div>


        <!-- =================================================
             TITLE
        ================================================== -->

        <h1>

            Change Password

        </h1>


        <div class="welcome">

            Welcome,

            <strong>

                <?php

                echo htmlspecialchars(
                    $student['student_name']
                );

                ?>

            </strong>

        </div>


        <!-- =================================================
             ERROR
        ================================================== -->

        <?php if ($error !== "") { ?>

            <div class="error">

                ⚠️

                <?php

                echo htmlspecialchars($error);

                ?>

            </div>

        <?php } ?>


        <!-- =================================================
             REQUIREMENTS
        ================================================== -->

        <div class="requirements">

            <strong>

                🔒 Password Requirements

            </strong>

            • Minimum 6 characters<br>

            • Use a password only you know<br>

            • New password must be different from the current password

        </div>


        <!-- =================================================
             FORM
        ================================================== -->

        <form method="POST">


            <!-- CURRENT PASSWORD -->

            <div class="form-group">

                <label for="current_password">

                    Current Password

                </label>


                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                    placeholder="Enter current password"
                    autocomplete="current-password"
                    required
                >

            </div>


            <!-- NEW PASSWORD -->

            <div class="form-group">

                <label for="new_password">

                    New Password

                </label>


                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    placeholder="Enter new password"
                    minlength="6"
                    autocomplete="new-password"
                    required
                >

            </div>


            <!-- CONFIRM PASSWORD -->

            <div class="form-group">

                <label for="confirm_password">

                    Confirm New Password

                </label>


                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Confirm new password"
                    minlength="6"
                    autocomplete="new-password"
                    required
                >

            </div>


            <!-- BUTTON -->

            <button type="submit">

                🔐 Change Password

            </button>


        </form>


        <!-- =================================================
             LOGOUT
        ================================================== -->

        <a
            href="student_logout.php"
            class="back"
        >

            ← Logout

        </a>


        <!-- =================================================
             SECURITY NOTE
        ================================================== -->

        <div class="security-note">

            🔒 Keep your password private and
            do not share it with anyone.

        </div>


    </div>

</div>


</body>

</html>