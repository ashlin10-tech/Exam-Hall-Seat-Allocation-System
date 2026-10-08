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
   SEARCH
========================================================= */

$search = trim($_GET['search'] ?? '');


/* =========================================================
   RETRIEVE STUDENTS
========================================================= */

$sql = "
    SELECT
        s.student_id,
        s.register_no,
        s.student_name,
        d.dept_name,
        s.year
    FROM student s
    INNER JOIN department d
        ON s.dept_id = d.dept_id
";


if ($search !== '') {

    $sql .= "
        WHERE
            s.register_no LIKE ?
            OR s.student_name LIKE ?
            OR d.dept_name LIKE ?
    ";

}


$sql .= "
    ORDER BY
        s.register_no ASC
";


$stmt = mysqli_prepare($conn, $sql);


if (!$stmt) {

    die(
        "Database Error: " .
        htmlspecialchars(mysqli_error($conn))
    );

}


if ($search !== '') {

    $search_value = "%" . $search . "%";

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $search_value,
        $search_value,
        $search_value
    );

}


mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result($stmt);


if (!$result) {

    die(
        "Database Error: " .
        htmlspecialchars(mysqli_error($conn))
    );

}


/* =========================================================
   TOTAL STUDENTS
========================================================= */

$count_sql = "
    SELECT COUNT(*) AS total
    FROM student
";


$count_result =
    mysqli_query($conn, $count_sql);


$total_students = 0;


if ($count_result) {

    $count_row =
        mysqli_fetch_assoc($count_result);

    $total_students =
        $count_row['total'];

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

    <title>Manage Students</title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            padding: 30px;

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

            min-height: 100vh;

        }


        .container {

            width: 100%;

            max-width: 1250px;

            margin: auto;

            background: #ffffff;

            border-radius: 20px;

            padding: 30px;

            box-shadow:
                0 20px 50px
                rgba(0,0,0,0.25);

        }


        /* =================================================
           HEADER
        ================================================= */

        .header {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 25px;

        }


        .title-section {

            display: flex;

            align-items: center;

            gap: 15px;

        }


        .icon {

            width: 58px;

            height: 58px;

            border-radius: 14px;

            background:
                linear-gradient(
                    135deg,
                    #7f1d1d,
                    #991b1b
                );

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 28px;

            color: white;

            flex-shrink: 0;

        }


        h1 {

            margin: 0;

            color: #450a0a;

            font-size: 28px;

        }


        .subtitle {

            margin: 5px 0 0;

            color: #64748b;

            font-size: 14px;

        }


        .total-box {

            background: #fff7f7;

            border:
                1px solid #f3d5d5;

            border-radius: 12px;

            padding: 12px 18px;

            text-align: center;

            min-width: 120px;

        }


        .total-number {

            display: block;

            font-size: 24px;

            font-weight: bold;

            color: #991b1b;

        }


        .total-label {

            color: #64748b;

            font-size: 12px;

        }


        /* =================================================
           SEARCH
        ================================================= */

        .search-card {

            background: #fffafa;

            border:
                1px solid #f1d5d5;

            border-radius: 14px;

            padding: 18px;

            margin-bottom: 25px;

        }


        .search-title {

            color: #450a0a;

            font-size: 14px;

            font-weight: bold;

            margin-bottom: 10px;

        }


        .search-box {

            display: flex;

            gap: 10px;

        }


        .search-box input {

            flex: 1;

            padding: 13px 15px;

            border:
                1px solid #cbd5e1;

            border-radius: 8px;

            font-size: 14px;

            outline: none;

        }


        .search-box input:focus {

            border-color: #991b1b;

            box-shadow:
                0 0 0 3px
                rgba(153,29,29,0.1);

        }


        .search-btn {

            border: none;

            border-radius: 8px;

            padding: 0 25px;

            background:
                linear-gradient(
                    135deg,
                    #7f1d1d,
                    #991b1b
                );

            color: white;

            font-weight: bold;

            cursor: pointer;

        }


        .search-btn:hover {

            background:
                linear-gradient(
                    135deg,
                    #450a0a,
                    #7f1d1d
                );

        }


        .clear-btn {

            display: flex;

            align-items: center;

            padding: 0 18px;

            border-radius: 8px;

            background: #f1f5f9;

            color: #475569;

            text-decoration: none;

            font-size: 13px;

            font-weight: bold;

        }


        .clear-btn:hover {

            background: #e2e8f0;

        }


        /* =================================================
           TABLE
        ================================================= */

        .table-container {

            overflow-x: auto;

            border-radius: 12px;

            border:
                1px solid #e2e8f0;

        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 750px;

        }


        th {

            background:
                linear-gradient(
                    135deg,
                    #7f1d1d,
                    #991b1b
                );

            color: white;

            padding: 15px 12px;

            text-align: center;

            font-size: 13px;

            letter-spacing: 0.2px;

        }


        td {

            padding: 13px 12px;

            text-align: center;

            border-bottom:
                1px solid #f1f5f9;

            color: #334155;

            font-size: 13px;

        }


        tr:nth-child(even) {

            background: #fffafa;

        }


        tr:hover {

            background: #fef2f2;

        }


        .id {

            font-weight: bold;

            color: #64748b;

        }


        .register {

            font-weight: bold;

            color: #991b1b;

        }


        .student {

            font-weight: bold;

            color: #334155;

        }


        .department {

            display: inline-block;

            background: #f3e8ff;

            color: #7e22ce;

            padding: 5px 10px;

            border-radius: 20px;

            font-weight: bold;

            font-size: 12px;

        }


        .year {

            display: inline-block;

            background: #dcfce7;

            color: #15803d;

            padding: 5px 10px;

            border-radius: 20px;

            font-weight: bold;

            font-size: 12px;

        }


        .no-data {

            padding: 45px !important;

            color: #64748b;

            font-size: 16px;

        }


        /* =================================================
           BOTTOM BUTTONS
        ================================================= */

        .buttons {

            margin-top: 25px;

            display: flex;

            justify-content: space-between;

            gap: 15px;

        }


        .btn {

            padding: 12px 22px;

            text-decoration: none;

            border-radius: 8px;

            font-weight: bold;

            font-size: 13px;

            text-align: center;

        }


        .add-btn {

            background:
                linear-gradient(
                    135deg,
                    #7f1d1d,
                    #991b1b
                );

            color: white;

        }


        .add-btn:hover {

            background:
                linear-gradient(
                    135deg,
                    #450a0a,
                    #7f1d1d
                );

        }


        .dashboard-btn {

            background: #f1f5f9;

            color: #334155;

        }


        .dashboard-btn:hover {

            background: #e2e8f0;

        }


        /* =================================================
           MOBILE
        ================================================= */

        @media (max-width: 700px) {

            body {

                padding: 15px;

            }


            .container {

                padding: 18px;

                border-radius: 14px;

            }


            .header {

                flex-direction: column;

                align-items: flex-start;

            }


            .total-box {

                width: 100%;

            }


            .search-box {

                flex-direction: column;

            }


            .search-btn {

                height: 45px;

            }


            .clear-btn {

                height: 45px;

                justify-content: center;

            }


            .buttons {

                flex-direction: column;

            }


            .btn {

                width: 100%;

            }

        }

    </style>

</head>


<body>


<div class="container">


    <!-- =================================================
         HEADER
    ================================================= -->

    <div class="header">


        <div class="title-section">


            <div class="icon">

                🎓

            </div>


            <div>

                <h1>

                    Manage Students

                </h1>


                <p class="subtitle">

                    View and search registered student information

                </p>

            </div>


        </div>


        <div class="total-box">

            <span class="total-number">

                <?php
                echo $total_students;
                ?>

            </span>

            <span class="total-label">

                Total Students

            </span>

        </div>


    </div>


    <!-- =================================================
         SEARCH
    ================================================= -->

    <div class="search-card">


        <div class="search-title">

            🔍 Search Students

        </div>


        <form
            method="GET"
            class="search-box"
        >


            <input
                type="text"
                name="search"
                placeholder="Register number, name or department"
                value="<?php
                    echo htmlspecialchars($search);
                ?>"
            >


            <button
                type="submit"
                class="search-btn"
            >

                Search

            </button>


            <?php if ($search !== '') { ?>

                <a
                    href="manage_students.php"
                    class="clear-btn"
                >

                    Clear

                </a>

            <?php } ?>


        </form>


    </div>


    <!-- =================================================
         STUDENT TABLE
    ================================================= -->

    <div class="table-container">


        <table>


            <thead>

                <tr>

                    <th>ID</th>

                    <th>Register Number</th>

                    <th>Student Name</th>

                    <th>Department</th>

                    <th>Year</th>

                </tr>

            </thead>


            <tbody>


            <?php

            if (mysqli_num_rows($result) > 0) {


                while (
                    $row =
                    mysqli_fetch_assoc($result)
                ) {

            ?>


                <tr>


                    <td class="id">

                        <?php
                        echo htmlspecialchars(
                            $row['student_id']
                        );
                        ?>

                    </td>


                    <td class="register">

                        <?php
                        echo htmlspecialchars(
                            $row['register_no']
                        );
                        ?>

                    </td>


                    <td class="student">

                        <?php
                        echo htmlspecialchars(
                            $row['student_name']
                        );
                        ?>

                    </td>


                    <td>

                        <span class="department">

                            <?php
                            echo htmlspecialchars(
                                $row['dept_name']
                            );
                            ?>

                        </span>

                    </td>


                    <td>

                        <span class="year">

                            Year
                            <?php
                            echo htmlspecialchars(
                                $row['year']
                            );
                            ?>

                        </span>

                    </td>


                </tr>


            <?php

                }

            } else {

            ?>


                <tr>

                    <td
                        colspan="5"
                        class="no-data"
                    >

                        📭 No students found.

                    </td>

                </tr>


            <?php

            }

            ?>


            </tbody>


        </table>


    </div>


    <!-- =================================================
         BOTTOM BUTTONS
    ================================================= -->

    <div class="buttons">


        <a
            href="student_form.php"
            class="btn add-btn"
        >

            + Add Student

        </a>


        <a
            href="admin_dashboard.php"
            class="btn dashboard-btn"
        >

            ← Back to Dashboard

        </a>


    </div>


</div>


</body>

</html>


<?php

mysqli_stmt_close($stmt);

mysqli_close($conn);

?>