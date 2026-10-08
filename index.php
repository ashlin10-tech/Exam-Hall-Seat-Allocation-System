<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Exam Seat Allocation System</title>


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
                radial-gradient(
                    circle at top,
                    #374151,
                    #111827 55%,
                    #030712
                );

            color: white;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 30px 15px;

        }


        /* =================================================
           MAIN CONTAINER
        ================================================= */

        .container {

            width: 100%;

            max-width: 1100px;

            text-align: center;

        }


        /* =================================================
           LOGO
        ================================================= */

        .logo {

            width: 82px;

            height: 82px;

            margin: 0 auto 18px;

            border-radius: 50%;

            background:
                linear-gradient(
                    135deg,
                    #7f1d1d,
                    #b91c1c
                );

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 37px;

            box-shadow:
                0 10px 35px
                rgba(185,28,28,0.35);

        }


        /* =================================================
           TITLE
        ================================================= */

        h1 {

            margin: 0;

            font-size: 36px;

            font-weight: 700;

            letter-spacing: 0.3px;

        }


        .subtitle {

            margin-top: 11px;

            margin-bottom: 42px;

            color: #d1d5db;

            font-size: 15px;

        }


        /* =================================================
           ROLE CARDS
        ================================================= */

        .roles {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 24px;

        }


        .role-card {

            background: white;

            color: #1f2937;

            border-radius: 18px;

            padding: 32px 25px 28px;

            text-decoration: none;

            position: relative;

            overflow: hidden;

            transition:
                transform 0.25s ease,
                box-shadow 0.25s ease;

        }


        .role-card:hover {

            transform:
                translateY(-7px);

        }


        /* =================================================
           TOP LINE
        ================================================= */

        .role-card::before {

            content: "";

            position: absolute;

            top: 0;

            left: 0;

            width: 100%;

            height: 5px;

        }


        /* =================================================
           ROLE ICON
        ================================================= */

        .role-icon {

            width: 74px;

            height: 74px;

            margin: 0 auto 19px;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 33px;

            color: white;

        }


        /* =================================================
           ADMIN
        ================================================= */

        .admin::before {

            background:
                linear-gradient(
                    90deg,
                    #7f1d1d,
                    #b91c1c
                );

        }


        .admin .role-icon {

            background:
                linear-gradient(
                    135deg,
                    #7f1d1d,
                    #b91c1c
                );

        }


        .admin:hover {

            box-shadow:
                0 18px 38px
                rgba(153,27,27,0.28);

        }


        .admin .login-link {

            background: #991b1b;

        }


        .admin .login-link:hover {

            background: #7f1d1d;

        }


        /* =================================================
           FACULTY
        ================================================= */

        .faculty::before {

            background:
                linear-gradient(
                    90deg,
                    #047857,
                    #10b981
                );

        }


        .faculty .role-icon {

            background:
                linear-gradient(
                    135deg,
                    #047857,
                    #10b981
                );

        }


        .faculty:hover {

            box-shadow:
                0 18px 38px
                rgba(5,150,105,0.25);

        }


        .faculty .login-link {

            background: #059669;

        }


        .faculty .login-link:hover {

            background: #047857;

        }


        /* =================================================
           STUDENT
        ================================================= */

        .student::before {

            background:
                linear-gradient(
                    90deg,
                    #6d28d9,
                    #9333ea
                );

        }


        .student .role-icon {

            background:
                linear-gradient(
                    135deg,
                    #6d28d9,
                    #9333ea
                );

        }


        .student:hover {

            box-shadow:
                0 18px 38px
                rgba(124,58,237,0.25);

        }


        .student .login-link {

            background: #7c3aed;

        }


        .student .login-link:hover {

            background: #6d28d9;

        }


        /* =================================================
           CARD TEXT
        ================================================= */

        .role-card h2 {

            margin: 0 0 9px;

            font-size: 21px;

        }


        .role-card p {

            color: #6b7280;

            font-size: 13px;

            line-height: 1.6;

            min-height: 43px;

            margin:
                0 auto 21px;

            max-width: 270px;

        }


        /* =================================================
           LOGIN BUTTON
        ================================================= */

        .login-link {

            display: inline-block;

            color: white;

            padding:
                11px 25px;

            border-radius: 8px;

            font-size: 13px;

            font-weight: bold;

            transition: 0.2s;

        }


        /* =================================================
           FOOTER
        ================================================= */

        .footer {

            margin-top: 35px;

            color: #9ca3af;

            font-size: 12px;

        }


        /* =================================================
           RESPONSIVE
        ================================================= */

        @media (max-width: 850px) {

            .roles {

                grid-template-columns: 1fr;

                max-width: 450px;

                margin: auto;

            }


            h1 {

                font-size: 30px;

            }

        }


        @media (max-width: 500px) {

            body {

                padding:
                    25px 15px;

            }


            .logo {

                width: 70px;

                height: 70px;

                font-size: 31px;

            }


            h1 {

                font-size: 25px;

            }


            .subtitle {

                font-size: 14px;

                margin-bottom: 28px;

            }


            .role-card {

                padding:
                    30px 20px 25px;

            }

        }

    </style>

</head>


<body>


<div class="container">


    <!-- =================================================
         LOGO
    ================================================== -->

    <div class="logo">

        🎓

    </div>


    <!-- =================================================
         TITLE
    ================================================== -->

    <h1>

        Exam Seat Allocation System

    </h1>


    <div class="subtitle">

        Secure and Smart Examination Management

    </div>



    <!-- =================================================
         ROLE CARDS
    ================================================== -->

    <div class="roles">


        <!-- ================= ADMIN ================= -->

        <a
            href="admin_login.php"
            class="role-card admin"
        >


            <div class="role-icon">

                🔐

            </div>


            <h2>

                Admin

            </h2>


            <p>

                Manage students, exams, halls,
                faculty and seat allocation.

            </p>


            <span class="login-link">

                Admin Login

            </span>


        </a>



        <!-- ================= FACULTY ================= -->

        <a
            href="faculty_login.php"
            class="role-card faculty"
        >


            <div class="role-icon">

                👨‍🏫

            </div>


            <h2>

                Faculty

            </h2>


            <p>

                View assigned halls,
                invigilation details and seating layout.

            </p>


            <span class="login-link">

                Faculty Login

            </span>


        </a>



        <!-- ================= STUDENT ================= -->

        <a
            href="student_login.php"
            class="role-card student"
        >


            <div class="role-icon">

                🎓

            </div>


            <h2>

                Student

            </h2>


            <p>

                View examination details,
                hall number and allocated seat.

            </p>


            <span class="login-link">

                Student Login

            </span>


        </a>


    </div>



    <!-- =================================================
         FOOTER
    ================================================== -->

    <div class="footer">

        Exam Seat Allocation System

    </div>


</div>


</body>

</html>