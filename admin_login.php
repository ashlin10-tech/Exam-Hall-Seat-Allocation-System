<?php

session_start();

include 'db_connect.php';

$error = "";


/* =========================================================
   ADMIN LOGIN
========================================================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username =
        trim($_POST['username'] ?? '');

    $password =
        $_POST['password'] ?? '';


    /* EMPTY FIELD CHECK */

    if (
        $username == "" ||
        $password == ""
    ) {

        $error =
            "Please enter username and password.";

    }

    else {

        $sql = "
            SELECT *
            FROM admin
            WHERE username = ?
        ";


        $stmt =
            mysqli_prepare(
                $conn,
                $sql
            );


        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $username
        );


        mysqli_stmt_execute($stmt);


        $result =
            mysqli_stmt_get_result($stmt);


        if (
            mysqli_num_rows($result) == 1
        ) {

            $admin =
                mysqli_fetch_assoc($result);


            /* CURRENT DATABASE USES PLAIN PASSWORD */

            if (
                $password ===
                $admin['password']
            ) {

                $_SESSION[
                    'admin_logged_in'
                ] = true;


                $_SESSION[
                    'admin_id'
                ] =
                    $admin['admin_id'];


                $_SESSION[
                    'admin_username'
                ] =
                    $admin['username'];


                header(
                    "Location: admin_dashboard.php"
                );

                exit();

            }

            else {

                $error =
                    "Invalid password.";

            }

        }

        else {

            $error =
                "Invalid username.";

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

<title>Admin Login</title>


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
            #3b0d0d,
            #7f1d1d,
            #991b1b
        );

    display: flex;

    justify-content: center;

    align-items: center;

    padding: 25px;

}


/* =========================================================
   CONTAINER
========================================================= */

.login-container {

    width: 100%;

    max-width: 440px;

}


/* =========================================================
   LOGIN CARD
========================================================= */

.login-box {

    background: white;

    padding: 40px;

    border-radius: 20px;

    box-shadow:
        0 20px 50px
        rgba(0,0,0,0.30);

    position: relative;

    overflow: hidden;

}


/* TOP LINE */

.login-box::before {

    content: "";

    position: absolute;

    top: 0;

    left: 0;

    width: 100%;

    height: 5px;

    background:
        linear-gradient(
            90deg,
            #7f1d1d,
            #b91c1c
        );

}


/* =========================================================
   ICON
========================================================= */

.icon {

    width: 78px;

    height: 78px;

    margin:
        0 auto 18px;

    border-radius: 20px;

    background:
        linear-gradient(
            135deg,
            #7f1d1d,
            #b91c1c
        );

    color: white;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 36px;

    box-shadow:
        0 8px 20px
        rgba(127,29,29,0.25);

}


/* =========================================================
   TITLE
========================================================= */

h2 {

    text-align: center;

    color: #450a0a;

    font-size: 27px;

    margin-bottom: 7px;

}


.subtitle {

    text-align: center;

    color: #7b7280;

    font-size: 13px;

    margin-bottom: 27px;

}


/* =========================================================
   ERROR
========================================================= */

.error {

    background: #fee2e2;

    color: #991b1b;

    border:
        1px solid #fecaca;

    padding: 12px;

    border-radius: 8px;

    margin-bottom: 20px;

    text-align: center;

    font-size: 13px;

    font-weight: bold;

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

    color: #450a0a;

    font-size: 14px;

    font-weight: bold;

}


input {

    width: 100%;

    padding: 13px 14px;

    border:
        1px solid #d6caca;

    border-radius: 9px;

    background: #fffafa;

    font-size: 14px;

    outline: none;

    transition: 0.25s;

}


input:focus {

    border-color: #991b1b;

    background: white;

    box-shadow:
        0 0 0 3px
        rgba(153,27,27,0.10);

}


/* =========================================================
   LOGIN BUTTON
========================================================= */

.login-btn {

    width: 100%;

    padding: 14px;

    border: none;

    border-radius: 9px;

    background:
        linear-gradient(
            135deg,
            #7f1d1d,
            #991b1b
        );

    color: white;

    font-size: 15px;

    font-weight: bold;

    cursor: pointer;

    transition: 0.25s;

    box-shadow:
        0 6px 16px
        rgba(127,29,29,0.22);

}


.login-btn:hover {

    background:
        linear-gradient(
            135deg,
            #450a0a,
            #7f1d1d
        );

    transform:
        translateY(-2px);

}


/* =========================================================
   BACK LINK
========================================================= */

.back {

    display: block;

    text-align: center;

    margin-top: 21px;

    color: #991b1b;

    text-decoration: none;

    font-size: 14px;

    font-weight: bold;

}


.back:hover {

    color: #7f1d1d;

    text-decoration: underline;

}


/* =========================================================
   SECURITY NOTE
========================================================= */

.security-note {

    margin-top: 22px;

    padding: 12px;

    background: #fff7f7;

    border:
        1px solid #f3d5d5;

    border-radius: 8px;

    color: #7f1d1d;

    font-size: 12px;

    text-align: center;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 500px) {

    body {

        padding: 15px;

    }


    .login-box {

        padding: 30px 23px;

    }


    h2 {

        font-size: 24px;

    }

}

</style>

</head>


<body>


<div class="login-container">


    <div class="login-box">


        <!-- ICON -->

        <div class="icon">

            🔐

        </div>


        <!-- TITLE -->

        <h2>

            Admin Login

        </h2>


        <div class="subtitle">

            Exam Seat Allocation System

        </div>


        <!-- ERROR -->

        <?php if ($error != "") { ?>

            <div class="error">

                ⚠

                <?php

                echo htmlspecialchars(
                    $error
                );

                ?>

            </div>

        <?php } ?>


        <!-- LOGIN FORM -->

        <form method="POST">


            <!-- USERNAME -->

            <div class="form-group">

                <label>

                    Username

                </label>


                <input
                    type="text"
                    name="username"
                    placeholder="Enter admin username"
                    autocomplete="username"
                    required
                >

            </div>


            <!-- PASSWORD -->

            <div class="form-group">

                <label>

                    Password

                </label>


                <input
                    type="password"
                    name="password"
                    placeholder="Enter admin password"
                    autocomplete="current-password"
                    required
                >

            </div>


            <!-- LOGIN -->

            <button
                type="submit"
                class="login-btn"
            >

                🔐 Login

            </button>


        </form>


        <!-- BACK -->

        <a
            href="index.php"
            class="back"
        >

            ← Back to Home

        </a>


        <!-- SECURITY -->

        <div class="security-note">

            🔒 Authorized administrators only

        </div>


    </div>

</div>


</body>

</html>