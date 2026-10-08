<?php

session_start();


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

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Add Student - Exam Seat Allocation</title>


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

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    min-height: 100vh;

    background:
        linear-gradient(
            135deg,
            #fff7f7,
            #f8f5f5,
            #fef2f2
        );

    color: #1f2937;

}


/* =========================================================
   HEADER
========================================================= */

header {

    background:
        linear-gradient(
            135deg,
            #450a0a,
            #7f1d1d,
            #991b1b
        );

    color: white;

    padding: 18px 40px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,0.18);

}


/* HEADER LEFT */

.logo {

    display: flex;

    align-items: center;

    gap: 12px;

}


.logo-icon {

    width: 46px;

    height: 46px;

    background:
        rgba(255,255,255,0.15);

    border-radius: 12px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 21px;

}


.logo h2 {

    font-size: 21px;

}


.logo p {

    margin-top: 3px;

    font-size: 12px;

    color: #fecaca;

}


/* HEADER RIGHT */

.header-actions {

    display: flex;

    align-items: center;

    gap: 9px;

}


.header-btn {

    color: white;

    text-decoration: none;

    padding: 9px 14px;

    border-radius: 7px;

    font-size: 13px;

    font-weight: bold;

    transition: 0.2s;

}


.dashboard-btn {

    background:
        rgba(255,255,255,0.15);

}


.dashboard-btn:hover {

    background:
        rgba(255,255,255,0.25);

}


.logout-btn {

    background: #dc2626;

}


.logout-btn:hover {

    background: #b91c1c;

}


/* =========================================================
   MAIN CONTAINER
========================================================= */

.container {

    width: 100%;

    max-width: 680px;

    margin: 42px auto;

    padding: 0 20px;

}


/* =========================================================
   PAGE TITLE
========================================================= */

.page-title {

    text-align: center;

    margin-bottom: 25px;

}


.page-title .icon {

    width: 62px;

    height: 62px;

    margin:
        0 auto 14px;

    border-radius: 16px;

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

    font-size: 29px;

    box-shadow:
        0 8px 20px
        rgba(127,29,29,0.20);

}


.page-title h1 {

    color: #450a0a;

    font-size: 29px;

    margin-bottom: 7px;

}


.page-title p {

    color: #6b7280;

    font-size: 14px;

}


/* =========================================================
   FORM CARD
========================================================= */

.form-card {

    background: white;

    padding: 35px;

    border-radius: 17px;

    box-shadow:
        0 10px 30px
        rgba(69,10,10,0.09);

    border:
        1px solid #eee3e3;

}


/* =========================================================
   FORM GROUP
========================================================= */

.form-group {

    margin-bottom: 20px;

}


label {

    display: block;

    margin-bottom: 8px;

    font-weight: bold;

    color: #450a0a;

    font-size: 14px;

}


/* =========================================================
   INPUTS
========================================================= */

input,
select {

    width: 100%;

    padding: 13px 14px;

    border:
        1px solid #d6caca;

    border-radius: 9px;

    font-size: 14px;

    outline: none;

    transition: 0.25s;

    background: #fffafa;

    color: #374151;

}


input:focus,
select:focus {

    border-color: #991b1b;

    background: white;

    box-shadow:
        0 0 0 3px
        rgba(153,27,27,0.10);

}


input::placeholder {

    color: #9ca3af;

}


/* =========================================================
   BUTTONS
========================================================= */

.button-group {

    display: flex;

    gap: 12px;

    margin-top: 8px;

}


.btn {

    flex: 1;

    padding: 13px;

    border: none;

    border-radius: 8px;

    font-size: 14px;

    font-weight: bold;

    cursor: pointer;

    text-decoration: none;

    text-align: center;

    transition: 0.25s;

}


.save-btn {

    background:
        linear-gradient(
            135deg,
            #7f1d1d,
            #991b1b
        );

    color: white;

    box-shadow:
        0 5px 14px
        rgba(127,29,29,0.18);

}


.save-btn:hover {

    background:
        linear-gradient(
            135deg,
            #450a0a,
            #7f1d1d
        );

    transform:
        translateY(-2px);

}


.back-btn {

    background: #f1e7e7;

    color: #7f1d1d;

}


.back-btn:hover {

    background: #ead7d7;

}


/* =========================================================
   INFORMATION BOX
========================================================= */

.info-box {

    margin-top: 22px;

    padding: 15px;

    background: #fff7f7;

    border-left:
        4px solid #991b1b;

    border-radius: 7px;

    color: #7f1d1d;

    font-size: 13px;

    line-height: 1.6;

}


.info-box strong {

    color: #450a0a;

}


/* =========================================================
   REQUIRED NOTE
========================================================= */

.required-note {

    margin-top: 15px;

    color: #9ca3af;

    font-size: 12px;

}


/* =========================================================
   FOOTER
========================================================= */

footer {

    margin-top: 50px;

    background: #450a0a;

    color: #fecaca;

    text-align: center;

    padding: 20px;

    font-size: 12px;

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 700px) {

    header {

        padding:
            17px 20px;

    }


    .logo h2 {

        font-size: 18px;

    }


    .logo p {

        display: none;

    }


    .header-actions {

        gap: 5px;

    }


    .header-btn {

        padding:
            8px 10px;

        font-size: 12px;

    }


    .container {

        margin-top: 30px;

    }


    .form-card {

        padding:
            27px 20px;

    }


    .page-title h1 {

        font-size: 25px;

    }


    .button-group {

        flex-direction: column;

    }

}


@media (max-width: 480px) {

    header {

        flex-direction: column;

        gap: 13px;

        text-align: center;

    }


    .header-actions {

        justify-content: center;

    }


    .logo {

        justify-content: center;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     HEADER
===================================================== -->

<header>


    <div class="logo">


        <div class="logo-icon">

            👨‍🎓

        </div>


        <div>

            <h2>

                Exam Seat Allocation System

            </h2>


            <p>

                Admin • Student Management

            </p>

        </div>


    </div>


    <div class="header-actions">


        <a
            href="admin_dashboard.php"
            class="header-btn dashboard-btn"
        >

            ← Dashboard

        </a>


        <a
            href="logout.php"
            class="header-btn logout-btn"
        >

            Logout

        </a>


    </div>


</header>



<!-- =====================================================
     MAIN
===================================================== -->

<div class="container">


    <!-- TITLE -->

    <div class="page-title">


        <div class="icon">

            👨‍🎓

        </div>


        <h1>

            Add Student

        </h1>


        <p>

            Enter student details for exam seat allocation

        </p>


    </div>



    <!-- FORM CARD -->

    <div class="form-card">


        <form
            action="save_student.php"
            method="POST"
        >


            <!-- REGISTER NUMBER -->

            <div class="form-group">


                <label for="register_no">

                    Register Number

                </label>


                <input
                    type="text"
                    id="register_no"
                    name="register_no"
                    placeholder="Enter register number"
                    required
                >


            </div>



            <!-- STUDENT NAME -->

            <div class="form-group">


                <label for="student_name">

                    Student Name

                </label>


                <input
                    type="text"
                    id="student_name"
                    name="student_name"
                    placeholder="Enter student name"
                    required
                >


            </div>



            <!-- DEPARTMENT -->

            <div class="form-group">


                <label for="dept_id">

                    Department

                </label>


                <select
                    id="dept_id"
                    name="dept_id"
                    required
                >


                    <option value="">

                        Select Department

                    </option>


                    <option value="1">
                        CSE
                    </option>


                    <option value="2">
                        IT
                    </option>


                    <option value="3">
                        ECE
                    </option>


                    <option value="4">
                        EEE
                    </option>


                    <option value="5">
                        MECH
                    </option>


                    <option value="6">
                        AIDS
                    </option>


                    <option value="7">
                        AIML
                    </option>


                    <option value="8">
                        CYBER
                    </option>


                    <option value="9">
                        FOOD TECH
                    </option>


                    <option value="10">
                        BIOTECH
                    </option>


                    <option value="11">
                        BME
                    </option>


                    <option value="12">
                        AGRI
                    </option>


                    <option value="13">
                        VLSI
                    </option>


                    <option value="14">
                        CIVIL
                    </option>


                </select>


            </div>



            <!-- YEAR -->

            <div class="form-group">


                <label for="year">

                    Year

                </label>


                <select
                    id="year"
                    name="year"
                    required
                >


                    <option value="">

                        Select Year

                    </option>


                    <option value="1">

                        1st Year

                    </option>


                    <option value="2">

                        2nd Year

                    </option>


                    <option value="3">

                        3rd Year

                    </option>


                    <option value="4">

                        4th Year

                    </option>


                </select>


            </div>



            <!-- BUTTONS -->

            <div class="button-group">


                <a
                    href="admin_dashboard.php"
                    class="btn back-btn"
                >

                    ← Back to Dashboard

                </a>


                <button
                    type="submit"
                    class="btn save-btn"
                >

                    ✓ Save Student

                </button>


            </div>



            <!-- INFORMATION -->

            <div class="info-box">


                <strong>Student Information</strong><br>


                Register number, name, department and year
                will be stored in the student database.


                Students from different departments can later
                be selected for mixed examination hall
                seat allocation.


            </div>


            <div class="required-note">

                * All fields are required.

            </div>


        </form>


    </div>

</div>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer>

    © 2026 Exam Seat Allocation System

</footer>


</body>

</html>