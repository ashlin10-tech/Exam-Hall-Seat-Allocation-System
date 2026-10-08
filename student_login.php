<?php

session_start();

include 'db_connect.php';

$error = "";


/* =========================================================
   LOGIN
========================================================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $register_no =
        trim($_POST['register_no'] ?? '');

    $password =
        $_POST['password'] ?? '';


    if (
        $register_no == "" ||
        $password == ""
    ) {

        $error =
            "Please enter Register Number and Password.";

    } else {


        $sql = "
            SELECT
                s.student_id,
                s.register_no,
                s.student_name,
                s.password,
                s.must_change_password,
                d.dept_name
            FROM student s
            INNER JOIN department d
                ON s.dept_id = d.dept_id
            WHERE s.register_no = ?
        ";


        $stmt =
            mysqli_prepare($conn, $sql);


        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "s",
                $register_no
            );


            mysqli_stmt_execute($stmt);


            $result =
                mysqli_stmt_get_result($stmt);


            if (mysqli_num_rows($result) == 1) {

                $student =
                    mysqli_fetch_assoc($result);


                /* =========================================
                   PASSWORD VERIFICATION
                ========================================= */

                $password_valid = false;


                /* HASHED PASSWORD */

                if (
                    !empty($student['password']) &&
                    password_verify(
                        $password,
                        $student['password']
                    )
                ) {

                    $password_valid = true;

                }


                /* TEMPORARY PLAIN PASSWORD */

                if (
                    !$password_valid &&
                    $student['password'] === $password
                ) {

                    $password_valid = true;

                }


                /* =========================================
                   LOGIN SUCCESS
                ========================================= */

                if ($password_valid) {


                    $_SESSION['student_logged_in'] =
                        true;


                    $_SESSION['student_id'] =
                        $student['student_id'];


                    $_SESSION['student_register_no'] =
                        $student['register_no'];


                    $_SESSION['student_name'] =
                        $student['student_name'];


                    $_SESSION['student_department'] =
                        $student['dept_name'];


                    /* =====================================
                       FIRST LOGIN
                    ===================================== */

                    if (
                        isset(
                            $student['must_change_password']
                        ) &&
                        $student['must_change_password'] == 1
                    ) {

                        header(
                            "Location: change_student_password.php"
                        );

                        exit();

                    }


                    /* =====================================
                       NORMAL LOGIN
                    ===================================== */

                    header(
                        "Location: student_dashboard.php"
                    );

                    exit();


                } else {

                    $error =
                        "Invalid password.";

                }


            } else {

                $error =
                    "Student not found.";

            }


            mysqli_stmt_close($stmt);


        } else {

            $error =
                "Database error. Please try again.";

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

<title>Student Login</title>


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

.login-container {

    width: 100%;

    max-width: 430px;

}


/* =========================================================
   LOGIN CARD
========================================================= */

.login-box {

    background: white;

    padding: 38px;

    border-radius: 20px;

    box-shadow:
        0 20px 50px
        rgba(0,0,0,0.25);

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
            #6d28d9,
            #9333ea
        );

}


/* =========================================================
   ICON
========================================================= */

.login-icon {

    width: 78px;

    height: 78px;

    background: #f3e8ff;

    color: #6d28d9;

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

.login-box h2 {

    text-align: center;

    color: #3b0764;

    font-size: 27px;

    margin-bottom: 7px;

}


.subtitle {

    text-align: center;

    color: #7c6f8a;

    font-size: 14px;

    margin-bottom: 28px;

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

    margin-bottom: 22px;

    text-align: center;

    font-size: 14px;

    font-weight: bold;

}


/* =========================================================
   FORM
========================================================= */

.form-group {

    margin-bottom: 20px;

}


.form-group label {

    display: block;

    margin-bottom: 8px;

    font-size: 14px;

    font-weight: bold;

    color: #4c1d95;

}


.form-group input {

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


.form-group input:focus {

    border-color: #7c3aed;

    background: white;

    box-shadow:
        0 0 0 3px
        rgba(124,58,237,0.10);

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


.login-btn:hover {

    background:
        linear-gradient(
            135deg,
            #5b21b6,
            #7e22ce
        );

    transform: translateY(-2px);

}


/* =========================================================
   BACK
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
   INFO
========================================================= */

.info {

    margin-top: 25px;

    padding: 13px;

    background: #faf5ff;

    border:
        1px solid #e9d5ff;

    border-left:
        4px solid #7c3aed;

    border-radius: 8px;

    color: #6b21a8;

    font-size: 12px;

    line-height: 1.5;

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

        padding: 30px 22px;

    }


    .login-box h2 {

        font-size: 24px;

    }

}

</style>

</head>


<body>


<div class="login-container">


    <div class="login-box">


        <!-- ICON -->

        <div class="login-icon">

            🎓

        </div>


        <!-- TITLE -->

        <h2>

            Student Login

        </h2>


        <div class="subtitle">

            Exam Seat Allocation System

        </div>


        <!-- ERROR -->

        <?php if ($error != "") { ?>

            <div class="error">

                ⚠

                <?php

                echo htmlspecialchars($error);

                ?>

            </div>

        <?php } ?>


        <!-- LOGIN FORM -->

        <form method="POST">


            <!-- REGISTER NUMBER -->

            <div class="form-group">


                <label for="register_no">

                    Register Number

                </label>


                <input
                    type="text"
                    id="register_no"
                    name="register_no"
                    placeholder="Enter Register Number"
                    value="<?php

                        echo htmlspecialchars(
                            $_POST['register_no'] ?? ''
                        );

                    ?>"
                    required
                >


            </div>


            <!-- PASSWORD -->

            <div class="form-group">


                <label for="password">

                    Password

                </label>


                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter Password"
                    required
                >


            </div>


            <!-- LOGIN -->

            <button
                type="submit"
                class="login-btn"
            >

                🎓 Login

            </button>


        </form>


        <!-- BACK -->

        <a
            href="index.php"
            class="back"
        >

            ← Back to Home

        </a>


        <!-- INFO -->

        <div class="info">

            Use your Register Number and password
            to access your examination details.

        </div>


    </div>


</div>


</body>

</html>