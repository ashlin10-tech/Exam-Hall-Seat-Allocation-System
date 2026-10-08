<?php
include "db_connect.php";

$result = null;
$register_no = "";

if (isset($_POST['search'])) {

    $register_no = trim($_POST['register_no']);

    $sql = "
        SELECT
            s.register_no,
            s.student_name,
            d.dept_name,
            e.subject,
            e.exam_date,
            e.start_time,
            e.end_time,
            r.room_no,
            sa.seat_no
        FROM seat_allocation sa

        INNER JOIN student s
            ON sa.student_id = s.student_id

        INNER JOIN department d
            ON s.dept_id = d.dept_id

        INNER JOIN exam e
            ON sa.exam_id = e.exam_id

        INNER JOIN room r
            ON sa.room_id = r.room_id

        WHERE s.register_no = ?
    ";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $register_no
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Search Student Allocation</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;

            font-family: Arial, sans-serif;

            background: linear-gradient(
                135deg,
                #dbeafe,
                #eef2ff,
                #f8fafc
            );

            padding: 40px 20px;
        }

        .container {
            width: 100%;
            max-width: 750px;

            margin: auto;
        }

        .search-card {
            background: white;

            padding: 35px;

            border-radius: 18px;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,0.12);
        }

        h1 {
            text-align: center;

            color: #1e3a8a;

            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;

            color: #64748b;

            margin-bottom: 30px;
        }

        label {
            display: block;

            font-weight: bold;

            color: #334155;

            margin-bottom: 8px;
        }

        .search-row {
            display: flex;

            gap: 10px;
        }

        input {
            flex: 1;

            padding: 13px;

            border: 1px solid #cbd5e1;

            border-radius: 8px;

            font-size: 15px;

            outline: none;
        }

        input:focus {
            border-color: #2563eb;

            box-shadow:
                0 0 0 3px
                rgba(37,99,235,0.12);
        }

        button {
            padding: 13px 25px;

            border: none;

            border-radius: 8px;

            background: #2563eb;

            color: white;

            font-size: 15px;

            font-weight: bold;

            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        .result-card {
            margin-top: 25px;

            background: #f8fafc;

            border-radius: 12px;

            padding: 25px;

            border: 1px solid #e2e8f0;
        }

        .success-title {
            color: #166534;

            text-align: center;

            margin-bottom: 20px;
        }

        .details {
            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 15px;
        }

        .detail {
            background: white;

            padding: 15px;

            border-radius: 8px;

            border: 1px solid #e2e8f0;
        }

        .detail-label {
            font-size: 12px;

            color: #64748b;

            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 16px;

            font-weight: bold;

            color: #1e293b;
        }

        .seat {
            color: #2563eb;

            font-size: 22px;
        }

        .not-found {
            margin-top: 25px;

            padding: 20px;

            text-align: center;

            background: #fef2f2;

            border: 1px solid #fecaca;

            color: #b91c1c;

            border-radius: 10px;
        }

        .buttons {
            margin-top: 25px;

            display: flex;

            justify-content: center;

            gap: 12px;
        }

        .btn {
            padding: 12px 22px;

            border-radius: 8px;

            text-decoration: none;

            font-weight: bold;
        }

        .allocation-btn {
            background: #2563eb;

            color: white;
        }

        .view-btn {
            background: #e2e8f0;

            color: #334155;
        }

        @media(max-width:600px) {

            .search-row {
                flex-direction: column;
            }

            .details {
                grid-template-columns: 1fr;
            }

            button {
                width: 100%;
            }

        }

    </style>

</head>

<body>


<div class="container">


    <div class="search-card">

        <h1>
            🔍 Search Student Allocation
        </h1>

        <p class="subtitle">
            Find a student's examination hall and seat
        </p>


        <form method="POST">


            <label>
                Register Number
            </label>


            <div class="search-row">

                <input
                    type="text"
                    name="register_no"
                    placeholder="Example: 23CS001"
                    value="<?php
                        echo htmlspecialchars($register_no);
                    ?>"
                    required
                >

                <button type="submit" name="search">
                    Search
                </button>

            </div>


        </form>


        <?php

        if (
            isset($_POST['search']) &&
            $result &&
            mysqli_num_rows($result) > 0
        ) {

            $row = mysqli_fetch_assoc($result);

        ?>


        <div class="result-card">


            <h2 class="success-title">
                ✓ Student Allocation Found
            </h2>


            <div class="details">


                <div class="detail">

                    <div class="detail-label">
                        Register Number
                    </div>

                    <div class="detail-value">

                        <?php
                        echo htmlspecialchars(
                            $row['register_no']
                        );
                        ?>

                    </div>

                </div>


                <div class="detail">

                    <div class="detail-label">
                        Student Name
                    </div>

                    <div class="detail-value">

                        <?php
                        echo htmlspecialchars(
                            $row['student_name']
                        );
                        ?>

                    </div>

                </div>


                <div class="detail">

                    <div class="detail-label">
                        Department
                    </div>

                    <div class="detail-value">

                        <?php
                        echo htmlspecialchars(
                            $row['dept_name']
                        );
                        ?>

                    </div>

                </div>


                <div class="detail">

                    <div class="detail-label">
                        Subject
                    </div>

                    <div class="detail-value">

                        <?php
                        echo htmlspecialchars(
                            $row['subject']
                        );
                        ?>

                    </div>

                </div>


                <div class="detail">

                    <div class="detail-label">
                        Exam Date
                    </div>

                    <div class="detail-value">

                        <?php
                        echo htmlspecialchars(
                            $row['exam_date']
                        );
                        ?>

                    </div>

                </div>


                <div class="detail">

                    <div class="detail-label">
                        Exam Time
                    </div>

                    <div class="detail-value">

                        <?php
                        echo htmlspecialchars(
                            $row['start_time']
                        );
                        ?>

                        -

                        <?php
                        echo htmlspecialchars(
                            $row['end_time']
                        );
                        ?>

                    </div>

                </div>


                <div class="detail">

                    <div class="detail-label">
                        Exam Hall
                    </div>

                    <div class="detail-value">

                        <?php
                        echo htmlspecialchars(
                            $row['room_no']
                        );
                        ?>

                    </div>

                </div>


                <div class="detail">

                    <div class="detail-label">
                        Seat Number
                    </div>

                    <div class="detail-value seat">

                        <?php
                        echo htmlspecialchars(
                            $row['seat_no']
                        );
                        ?>

                    </div>

                </div>


            </div>


        </div>


        <?php

        } elseif (
            isset($_POST['search'])
        ) {

        ?>

            <div class="not-found">

                ❌ No allocation found for

                <strong>
                    <?php
                    echo htmlspecialchars($register_no);
                    ?>
                </strong>

                <br><br>

                Please check the register number.

            </div>

        <?php

        }

        ?>


        <div class="buttons">

            <a
                href="seat_allocation.php"
                class="btn allocation-btn"
            >
                New Allocation
            </a>


            <a
                href="view.php"
                class="btn view-btn"
            >
                View All
            </a>

        </div>


    </div>


</div>


</body>

</html>

<?php
mysqli_close($conn);
?>