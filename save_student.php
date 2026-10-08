<?php

session_start();

include "db_connect.php";


/* =========================================================
   ADMIN ACCESS CHECK
========================================================= */

if (
    !isset($_SESSION['admin_logged_in']) ||
    $_SESSION['admin_logged_in'] !== true
) {

    header("Location: admin_login.php");
    exit();

}


/* =========================================================
   CHECK REQUEST METHOD
========================================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: student_form.php");
    exit();

}


/* =========================================================
   GET FORM DATA
========================================================= */

$register_no =
    trim($_POST['register_no'] ?? '');

$student_name =
    trim($_POST['student_name'] ?? '');

$dept_id =
    intval($_POST['dept_id'] ?? 0);

$year =
    intval($_POST['year'] ?? 0);


/* =========================================================
   VALIDATION
========================================================= */

if (
    $register_no === '' ||
    $student_name === '' ||
    $dept_id <= 0 ||
    $year < 1 ||
    $year > 4
) {

    die("Please enter all student details correctly.");

}


/* =========================================================
   CHECK REGISTER NUMBER
========================================================= */

$check = mysqli_prepare(
    $conn,
    "SELECT student_id
     FROM student
     WHERE register_no = ?
     LIMIT 1"
);


mysqli_stmt_bind_param(
    $check,
    "s",
    $register_no
);


mysqli_stmt_execute($check);


$result =
    mysqli_stmt_get_result($check);


if (mysqli_num_rows($result) > 0) {

    die(
        "A student with register number " .
        htmlspecialchars($register_no) .
        " already exists."
    );

}


/* =========================================================
   TEMPORARY PASSWORD
========================================================= */

$temp_password = "temp@2025";


$hashed_password =
    password_hash(
        $temp_password,
        PASSWORD_DEFAULT
    );


/* =========================================================
   INSERT STUDENT
========================================================= */

$sql = "
    INSERT INTO student
    (
        register_no,
        password,
        must_change_password,
        student_name,
        dept_id,
        year
    )
    VALUES
    (?, ?, 1, ?, ?, ?)
";


$stmt = mysqli_prepare(
    $conn,
    $sql
);


mysqli_stmt_bind_param(
    $stmt,
    "sssii",
    $register_no,
    $hashed_password,
    $student_name,
    $dept_id,
    $year
);


/* =========================================================
   SAVE
========================================================= */

if (mysqli_stmt_execute($stmt)) {

    ?>

    <!DOCTYPE html>

    <html lang="en">

    <head>

        <meta charset="UTF-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        >

        <title>Student Saved</title>


        <style>

            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }


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

                align-items: center;

                justify-content: center;

                padding: 20px;

            }


            .box {

                width: 100%;

                max-width: 470px;

                background: white;

                padding: 40px;

                border-radius: 18px;

                text-align: center;

                box-shadow:
                    0 20px 50px
                    rgba(0,0,0,0.25);

            }


            .success-icon {

                width: 70px;

                height: 70px;

                margin:
                    0 auto 20px;

                border-radius: 50%;

                background: #dcfce7;

                color: #16a34a;

                display: flex;

                align-items: center;

                justify-content: center;

                font-size: 34px;

            }


            h2 {

                color: #450a0a;

                margin-bottom: 10px;

            }


            .message {

                color: #6b7280;

                font-size: 14px;

                line-height: 1.6;

                margin-bottom: 20px;

            }


            .credentials {

                background: #fff7f7;

                border:
                    1px solid #f3d5d5;

                border-radius: 10px;

                padding: 16px;

                margin-bottom: 22px;

                text-align: left;

            }


            .credentials h3 {

                color: #7f1d1d;

                font-size: 15px;

                margin-bottom: 10px;

            }


            .credentials p {

                color: #4b5563;

                font-size: 13px;

                margin: 7px 0;

            }


            .credentials strong {

                color: #450a0a;

            }


            .btn {

                display: inline-block;

                width: 100%;

                padding: 12px;

                border-radius: 8px;

                background:
                    linear-gradient(
                        135deg,
                        #7f1d1d,
                        #991b1b
                    );

                color: white;

                text-decoration: none;

                font-size: 14px;

                font-weight: bold;

            }


            .btn:hover {

                background:
                    linear-gradient(
                        135deg,
                        #450a0a,
                        #7f1d1d
                    );

            }

        </style>

    </head>


    <body>


        <div class="box">


            <div class="success-icon">

                ✓

            </div>


            <h2>

                Student Saved Successfully!

            </h2>


            <p class="message">

                The student has been added to the
                Exam Seat Allocation System.

            </p>


            <div class="credentials">

                <h3>

                    Student Login Details

                </h3>


                <p>

                    <strong>Username:</strong>

                    <?php
                    echo htmlspecialchars(
                        $register_no
                    );
                    ?>

                </p>


                <p>

                    <strong>Temporary Password:</strong>

                    <?php
                    echo htmlspecialchars(
                        $temp_password
                    );
                    ?>

                </p>


                <p>

                    <strong>Status:</strong>

                    Password change required on first login

                </p>

            </div>


            <a
                href="student_form.php"
                class="btn"
            >

                + Add Another Student

            </a>


            <br><br>


            <a
                href="admin_dashboard.php"
                style="
                    color:#991b1b;
                    text-decoration:none;
                    font-size:13px;
                "
            >

                ← Back to Admin Dashboard

            </a>


        </div>


    </body>

    </html>

    <?php

}

else {

    ?>

    <!DOCTYPE html>

    <html lang="en">

    <head>

        <meta charset="UTF-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        >

        <title>Unable to Save Student</title>

        <style>

            body {

                min-height: 100vh;

                margin: 0;

                font-family: Arial, sans-serif;

                background:
                    linear-gradient(
                        135deg,
                        #3b0d0d,
                        #7f1d1d
                    );

                display: flex;

                align-items: center;

                justify-content: center;

                padding: 20px;

            }


            .box {

                max-width: 500px;

                width: 100%;

                background: white;

                padding: 35px;

                border-radius: 16px;

                text-align: center;

                box-shadow:
                    0 20px 50px
                    rgba(0,0,0,0.25);

            }


            h2 {

                color: #991b1b;

                margin-bottom: 15px;

            }


            .error {

                background: #fee2e2;

                color: #991b1b;

                padding: 13px;

                border-radius: 8px;

                font-size: 13px;

                margin-bottom: 20px;

            }


            a {

                display: inline-block;

                padding: 11px 18px;

                background: #991b1b;

                color: white;

                text-decoration: none;

                border-radius: 7px;

                font-size: 13px;

                font-weight: bold;

            }

        </style>

    </head>


    <body>


        <div class="box">

            <h2>

                Unable to Save Student

            </h2>


            <div class="error">

                <?php

                echo htmlspecialchars(
                    mysqli_stmt_error($stmt)
                );

                ?>

            </div>


            <a href="student_form.php">

                ← Back to Add Student

            </a>

        </div>


    </body>

    </html>

    <?php

}


mysqli_close($conn);

?>