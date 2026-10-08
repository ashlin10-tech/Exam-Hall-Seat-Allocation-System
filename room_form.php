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

    <title>Add Exam Hall</title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            min-height: 100vh;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background:
                linear-gradient(
                    135deg,
                    #450a0a,
                    #7f1d1d,
                    #991b1b
                );

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 30px;

        }


        /* =================================================
           MAIN CARD
        ================================================= */

        .container {

            width: 500px;

            max-width: 100%;

            background: white;

            padding: 38px 40px;

            border-radius: 18px;

            box-shadow:
                0 20px 50px
                rgba(0,0,0,0.25);

        }


        /* =================================================
           ICON
        ================================================= */

        .icon {

            width: 65px;

            height: 65px;

            margin:
                0 auto 15px;

            border-radius: 50%;

            background: #7f1d1d;

            color: white;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 30px;

            box-shadow:
                0 8px 20px
                rgba(127,29,29,0.3);

        }


        /* =================================================
           TITLE
        ================================================= */

        h1 {

            text-align: center;

            margin: 0;

            color: #7f1d1d;

            font-size: 28px;

        }


        .subtitle {

            text-align: center;

            color: #6b7280;

            font-size: 14px;

            margin-top: 8px;

            margin-bottom: 30px;

        }


        /* =================================================
           FORM
        ================================================= */

        .form-group {

            margin-bottom: 22px;

        }


        label {

            display: block;

            font-size: 14px;

            font-weight: bold;

            color: #374151;

            margin-bottom: 8px;

        }


        input {

            width: 100%;

            padding: 13px 14px;

            border:
                1px solid #d1d5db;

            border-radius: 8px;

            font-size: 15px;

            background: #fafafa;

            transition: 0.2s;

        }


        input:focus {

            outline: none;

            border-color: #991b1b;

            background: white;

            box-shadow:
                0 0 0 3px
                rgba(153,27,27,0.10);

        }


        input::placeholder {

            color: #9ca3af;

        }


        /* =================================================
           BUTTONS
        ================================================= */

        .button-group {

            display: flex;

            gap: 12px;

            margin-top: 8px;

        }


        .btn {

            flex: 1;

            padding: 14px;

            border: none;

            border-radius: 8px;

            font-size: 15px;

            font-weight: bold;

            cursor: pointer;

            text-align: center;

            text-decoration: none;

            transition: 0.2s;

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
                0 7px 16px
                rgba(127,29,29,0.25);

        }


        .save-btn:hover {

            transform:
                translateY(-1px);

            box-shadow:
                0 10px 20px
                rgba(127,29,29,0.30);

        }


        .back-btn {

            background: #f3f4f6;

            color: #4b5563;

        }


        .back-btn:hover {

            background: #e5e7eb;

        }


        /* =================================================
           INFO BOX
        ================================================= */

        .info-box {

            margin-top: 20px;

            padding: 14px 15px;

            background: #fef2f2;

            border-left:
                4px solid #991b1b;

            border-radius: 7px;

            color: #7f1d1d;

            font-size: 13px;

            line-height: 1.6;

        }


        .info-box strong {

            color: #650f0f;

        }


        /* =================================================
           DASHBOARD LINK
        ================================================= */

        .dashboard-link {

            display: block;

            text-align: center;

            margin-top: 22px;

            color: #7f1d1d;

            text-decoration: none;

            font-size: 14px;

            font-weight: bold;

        }


        .dashboard-link:hover {

            text-decoration: underline;

        }


        /* =================================================
           RESPONSIVE
        ================================================= */

        @media(max-width: 550px) {

            body {

                padding: 15px;

            }


            .container {

                padding:
                    30px 25px;

            }


            h1 {

                font-size: 24px;

            }


            .button-group {

                flex-direction: column;

            }

        }

    </style>

</head>


<body>


<div class="container">


    <!-- =================================================
         ICON
    ================================================= -->

    <div class="icon">

        🏫

    </div>


    <!-- =================================================
         TITLE
    ================================================= -->

    <h1>

        Add Exam Hall

    </h1>


    <div class="subtitle">

        Enter hall details and seating capacity

    </div>


    <!-- =================================================
         FORM
    ================================================= -->

    <form
        action="save_room.php"
        method="POST"
        onsubmit="return validateForm()"
    >


        <!-- =================================================
             HALL NUMBER
        ================================================= -->

        <div class="form-group">


            <label for="room_no">

                Hall Number

            </label>


            <input
                type="text"
                id="room_no"
                name="room_no"
                placeholder="Example: LH41"
                maxlength="30"
                required
                autocomplete="off"
            >


        </div>


        <!-- =================================================
             CAPACITY
        ================================================= -->

        <div class="form-group">


            <label for="capacity">

                Hall Capacity

            </label>


            <input
                type="number"
                id="capacity"
                name="capacity"
                placeholder="Example: 60"
                min="30"
                required
            >


        </div>


        <!-- =================================================
             BUTTONS
        ================================================= -->

        <div class="button-group">


            <a
                href="admin_dashboard.php"
                class="btn back-btn"
            >

                ← Back

            </a>


            <button
                type="submit"
                class="btn save-btn"
            >

                Save Hall

            </button>


        </div>


        <!-- =================================================
             INFO
        ================================================= -->

        <div class="info-box">


            <strong>Example:</strong>

            If LH41 has 60 seats, enter
            <strong>LH41</strong> as the hall number
            and <strong>60</strong> as the capacity.


            <br><br>


            <strong>Note:</strong>

            The seating layout supports a maximum
            of 30 students per allocation.

        </div>


    </form>


    <!-- =================================================
         DASHBOARD
    ================================================= -->

    <a
        href="admin_dashboard.php"
        class="dashboard-link"
    >

        ← Back to Admin Dashboard

    </a>


</div>


<script>


/* =========================================================
   FORM VALIDATION
========================================================= */

function validateForm() {


    const roomNo =
        document.getElementById(
            "room_no"
        ).value.trim();


    const capacity =
        parseInt(
            document.getElementById(
                "capacity"
            ).value
        );


    /* =============================================
       HALL NUMBER
    ============================================= */

    if (roomNo === "") {

        alert(
            "Please enter the hall number."
        );

        document.getElementById(
            "room_no"
        ).focus();

        return false;

    }


    /* =============================================
       CAPACITY
    ============================================= */

    if (
        isNaN(capacity) ||
        capacity < 30
    ) {

        alert(
            "Hall capacity must be at least 30."
        );

        document.getElementById(
            "capacity"
        ).focus();

        return false;

    }


    return true;

}

</script>


</body>

</html>