<?php

session_start();

include("db_connect.php");


/* =========================================================
   FACULTY LOGIN CHECK
========================================================= */

if (
    !isset($_SESSION["faculty_logged_in"]) ||
    $_SESSION["faculty_logged_in"] !== true
) {
    header("Location: faculty_login.php");
    exit();
}


$faculty_id = $_SESSION["faculty_id"] ?? 0;

$message = "";
$message_type = "";


/* =========================================================
   CHANGE PASSWORD
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $current_password =
        $_POST["current_password"] ?? "";

    $new_password =
        $_POST["new_password"] ?? "";

    $confirm_password =
        $_POST["confirm_password"] ?? "";


    /* =====================================================
       BASIC VALIDATION
    ===================================================== */

    if (
        $current_password === "" ||
        $new_password === "" ||
        $confirm_password === ""
    ) {

        $message =
            "Please fill in all password fields.";

        $message_type = "error";

    }

    elseif (strlen($new_password) < 6) {

        $message =
            "New password must contain at least 6 characters.";

        $message_type = "error";

    }

    elseif ($new_password !== $confirm_password) {

        $message =
            "New password and confirm password do not match.";

        $message_type = "error";

    }

    else {

        /* =================================================
           GET CURRENT FACULTY PASSWORD
        ================================================= */

        $stmt = mysqli_prepare(
            $conn,
            "SELECT password
             FROM faculty
             WHERE faculty_id = ?
             LIMIT 1"
        );


        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $faculty_id
            );

            mysqli_stmt_execute($stmt);

            $result =
                mysqli_stmt_get_result($stmt);


            if (mysqli_num_rows($result) !== 1) {

                $message =
                    "Faculty account not found.";

                $message_type = "error";

            }

            else {

                $faculty =
                    mysqli_fetch_assoc($result);

                $stored_password =
                    $faculty["password"];


                /* =========================================
                   VERIFY CURRENT PASSWORD
                ========================================= */

                $current_password_valid = false;


                /*
                 * First check hashed password.
                 */

                if (
                    !empty($stored_password) &&
                    password_verify(
                        $current_password,
                        $stored_password
                    )
                ) {

                    $current_password_valid = true;

                }


                /*
                 * Also support existing plain-text
                 * passwords in the database.
                 */

                if (
                    !$current_password_valid &&
                    $stored_password === $current_password
                ) {

                    $current_password_valid = true;

                }


                /* =========================================
                   CURRENT PASSWORD CHECK
                ========================================= */

                if (!$current_password_valid) {

                    $message =
                        "Current password is incorrect.";

                    $message_type = "error";

                }


                /* =========================================
                   SAME PASSWORD CHECK
                ========================================= */

                elseif (
                    $new_password === $current_password
                ) {

                    $message =
                        "New password must be different from current password.";

                    $message_type = "error";

                }


                /* =========================================
                   HASH NEW PASSWORD
                ========================================= */

                else {

                    $hashed_password =
                        password_hash(
                            $new_password,
                            PASSWORD_DEFAULT
                        );


                    /* =====================================
                       UPDATE PASSWORD
                    ===================================== */

                    $update = mysqli_prepare(
                        $conn,
                        "UPDATE faculty
                         SET password = ?
                         WHERE faculty_id = ?"
                    );


                    if ($update) {

                        mysqli_stmt_bind_param(
                            $update,
                            "si",
                            $hashed_password,
                            $faculty_id
                        );


                        if (
                            mysqli_stmt_execute($update)
                        ) {

                            mysqli_stmt_close($update);

                            mysqli_stmt_close($stmt);

                            /*
                             * Redirect immediately after
                             * successful password change.
                             */

                            header(
                                "Location: faculty_dashboard.php"
                            );

                            exit();

                        }

                        else {

                            $message =
                                "Unable to change password. Please try again.";

                            $message_type = "error";

                        }


                        mysqli_stmt_close($update);

                    }

                    else {

                        $message =
                            "Database error. Please try again.";

                        $message_type = "error";

                    }

                }

            }


            mysqli_stmt_close($stmt);

        }

        else {

            $message =
                "Database error. Please try again.";

            $message_type = "error";

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

<title>
    Faculty Change Password
</title>


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
            #064e3b,
            #047857,
            #10b981
        );

    display: flex;

    justify-content: center;

    align-items: center;

    padding: 25px;

}


/* =========================================================
   MAIN CARD
========================================================= */

.container {

    width: 450px;

    max-width: 95%;

    background: white;

    padding: 38px;

    border-radius: 20px;

    box-shadow:
        0 20px 50px
        rgba(0, 0, 0, 0.25);

    position: relative;

    overflow: hidden;

}


/* =========================================================
   TOP GREEN LINE
========================================================= */

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
            #047857,
            #10b981
        );

}


/* =========================================================
   ICON
========================================================= */

.icon {

    width: 78px;

    height: 78px;

    background: #d1fae5;

    color: #047857;

    border-radius: 20px;

    display: flex;

    justify-content: center;

    align-items: center;

    font-size: 38px;

    margin: 0 auto 20px;

}


/* =========================================================
   TITLE
========================================================= */

h1 {

    text-align: center;

    color: #064e3b;

    margin-bottom: 7px;

    font-size: 26px;

}


.subtitle {

    text-align: center;

    color: #777;

    font-size: 14px;

    margin-bottom: 28px;

}


/* =========================================================
   MESSAGE
========================================================= */

.message {

    padding: 13px;

    border-radius: 9px;

    text-align: center;

    margin-bottom: 22px;

    font-size: 14px;

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

    margin-bottom: 19px;

}


label {

    display: block;

    color: #064e3b;

    font-weight: bold;

    font-size: 14px;

    margin-bottom: 8px;

}


input {

    width: 100%;

    padding: 13px 14px;

    border:
        1px solid #cde8dc;

    border-radius: 9px;

    background: #f8fffb;

    font-size: 14px;

    outline: none;

    transition: 0.3s;

}


input:focus {

    border-color: #059669;

    background: white;

    box-shadow:
        0 0 0 3px
        rgba(5, 150, 105, 0.10);

}


/* =========================================================
   PASSWORD NOTE
========================================================= */

.password-note {

    margin-top: -7px;

    margin-bottom: 22px;

    color: #888;

    font-size: 12px;

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
            #047857,
            #059669
        );

    color: white;

    font-size: 15px;

    font-weight: bold;

    cursor: pointer;

    transition: 0.3s;

    box-shadow:
        0 5px 15px
        rgba(4, 120, 87, 0.25);

}


button:hover {

    background:
        linear-gradient(
            135deg,
            #065f46,
            #047857
        );

    transform: translateY(-2px);

}


/* =========================================================
   BACK LINK
========================================================= */

.back {

    display: block;

    text-align: center;

    margin-top: 22px;

    color: #047857;

    text-decoration: none;

    font-size: 14px;

    font-weight: bold;

}


.back:hover {

    color: #064e3b;

    text-decoration: underline;

}


/* =========================================================
   SECURITY NOTE
========================================================= */

.security-note {

    margin-top: 25px;

    padding: 13px;

    background: #ecfdf5;

    border:
        1px solid #a7f3d0;

    border-radius: 9px;

    color: #047857;

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

        padding: 30px 22px;

    }


    h1 {

        font-size: 23px;

    }

}

</style>

</head>


<body>


<div class="container">


    <!-- =====================================================
         ICON
    ====================================================== -->

    <div class="icon">

        🔐

    </div>


    <!-- =====================================================
         TITLE
    ====================================================== -->

    <h1>

        Change Password

    </h1>


    <p class="subtitle">

        Faculty Account Security

    </p>


    <!-- =====================================================
         MESSAGE
    ====================================================== -->

    <?php if ($message !== "") { ?>

        <div
            class="message
            <?php echo htmlspecialchars($message_type); ?>"
        >

            <?php

            echo htmlspecialchars($message);

            ?>

        </div>

    <?php } ?>


    <!-- =====================================================
         FORM
    ====================================================== -->

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


        <div class="password-note">

            Password must contain at least 6 characters.

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
                placeholder="Re-enter new password"
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


    <!-- =====================================================
         BACK
    ====================================================== -->

    <a
        href="faculty_dashboard.php"
        class="back"
    >

        ← Back to Dashboard

    </a>


    <!-- =====================================================
         SECURITY NOTE
    ====================================================== -->

    <div class="security-note">

        🔒 Keep your faculty account password private
        and do not share it with others.

    </div>


</div>


</body>

</html>