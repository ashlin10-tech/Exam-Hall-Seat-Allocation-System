<?php

session_start();

include("db_connect.php");


/* =========================================================
   ADMIN SESSION CHECK
========================================================= */

if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true
) {

    header("Location: admin_login.php");
    exit();

}


$admin_id =
    $_SESSION["admin_id"] ?? 0;


$message = "";

$message_type = "";


/* =========================================================
   CHANGE PASSWORD
========================================================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $current_password =
        $_POST["current_password"] ?? "";

    $new_password =
        $_POST["new_password"] ?? "";

    $confirm_password =
        $_POST["confirm_password"] ?? "";


    /* GET CURRENT PASSWORD */

    $stmt = mysqli_prepare(
        $conn,
        "SELECT password
         FROM admin
         WHERE admin_id = ?
         LIMIT 1"
    );


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $admin_id
    );


    mysqli_stmt_execute($stmt);


    $result =
        mysqli_stmt_get_result($stmt);


    if (mysqli_num_rows($result) != 1) {

        $message =
            "Admin account not found.";

        $message_type =
            "error";

    }

    else {

        $admin =
            mysqli_fetch_assoc($result);


        /* CHECK CURRENT PASSWORD */

        if (
            $current_password !==
            $admin["password"]
        ) {

            $message =
                "Current password is incorrect.";

            $message_type =
                "error";

        }


        /* CHECK PASSWORD LENGTH */

        elseif (
            strlen($new_password) < 6
        ) {

            $message =
                "New password must contain at least 6 characters.";

            $message_type =
                "error";

        }


        /* CHECK CONFIRM PASSWORD */

        elseif (
            $new_password !==
            $confirm_password
        ) {

            $message =
                "New password and confirm password do not match.";

            $message_type =
                "error";

        }


        /* CHECK SAME PASSWORD */

        elseif (
            $current_password ===
            $new_password
        ) {

            $message =
                "New password must be different from the current password.";

            $message_type =
                "error";

        }


        else {

            /* UPDATE PASSWORD */

            $update = mysqli_prepare(
                $conn,
                "UPDATE admin
                 SET password = ?
                 WHERE admin_id = ?"
            );


            mysqli_stmt_bind_param(
                $update,
                "si",
                $new_password,
                $admin_id
            );


            if (
                mysqli_stmt_execute($update)
            ) {

                $message =
                    "Password changed successfully.";

                $message_type =
                    "success";

            }

            else {

                $message =
                    "Unable to change password.";

                $message_type =
                    "error";

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

<title>Change Admin Password</title>


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

    padding: 20px;

}


/* =========================================================
   CONTAINER
========================================================= */

.container {

    width: 100%;

    max-width: 440px;

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

.container::before {

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

h1 {

    text-align: center;

    color: #450a0a;

    font-size: 26px;

    margin-bottom: 7px;

}


.subtitle {

    text-align: center;

    color: #7b7280;

    font-size: 13px;

    margin-bottom: 27px;

}


/* =========================================================
   MESSAGE
========================================================= */

.message {

    padding: 12px;

    border-radius: 8px;

    text-align: center;

    margin-bottom: 20px;

    font-size: 13px;

    font-weight: bold;

}


.success {

    background: #dcfce7;

    color: #166534;

    border:
        1px solid #bbf7d0;

}


.error {

    background: #fee2e2;

    color: #991b1b;

    border:
        1px solid #fecaca;

}


/* =========================================================
   FORM
========================================================= */

.form-group {

    margin-bottom: 18px;

}


label {

    display: block;

    color: #450a0a;

    font-weight: bold;

    font-size: 14px;

    margin-bottom: 7px;

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
   PASSWORD REQUIREMENT
========================================================= */

.requirement {

    margin-top: -7px;

    margin-bottom: 20px;

    font-size: 12px;

    color: #6b7280;

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


button:hover {

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


    .container {

        padding: 30px 23px;

    }


    h1 {

        font-size: 23px;

    }

}

</style>

</head>


<body>


<div class="container">


    <!-- ICON -->

    <div class="icon">

        🔐

    </div>


    <!-- TITLE -->

    <h1>

        Change Password

    </h1>


    <p class="subtitle">

        Admin Account Security

    </p>


    <!-- MESSAGE -->

    <?php if ($message != "") { ?>

        <div
            class="message
            <?php echo $message_type; ?>"
        >

            <?php

            echo htmlspecialchars(
                $message
            );

            ?>

        </div>

    <?php } ?>


    <!-- FORM -->

    <form method="POST">


        <!-- CURRENT PASSWORD -->

        <div class="form-group">

            <label>

                Current Password

            </label>


            <input
                type="password"
                name="current_password"
                placeholder="Enter current password"
                autocomplete="current-password"
                required
            >

        </div>


        <!-- NEW PASSWORD -->

        <div class="form-group">

            <label>

                New Password

            </label>


            <input
                type="password"
                name="new_password"
                placeholder="Enter new password"
                minlength="6"
                autocomplete="new-password"
                required
            >

        </div>


        <div class="requirement">

            Password must contain at least 6 characters.

        </div>


        <!-- CONFIRM PASSWORD -->

        <div class="form-group">

            <label>

                Confirm New Password

            </label>


            <input
                type="password"
                name="confirm_password"
                placeholder="Confirm new password"
                minlength="6"
                autocomplete="new-password"
                required
            >

        </div>


        <!-- SUBMIT -->

        <button type="submit">

            🔑 Change Password

        </button>


    </form>


    <!-- BACK -->

    <a
        href="admin_dashboard.php"
        class="back"
    >

        ← Back to Dashboard

    </a>


    <!-- SECURITY -->

    <div class="security-note">

        🔒 Keep your administrator password secure

    </div>


</div>


</body>

</html>