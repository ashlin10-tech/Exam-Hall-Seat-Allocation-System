<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include 'db_connect.php';

$sql = "
    SELECT DISTINCT
        sa.room_id,
        r.room_no,
        sa.allocation_date,
        sa.session
    FROM seat_allocation sa
    INNER JOIN room r ON sa.room_id = r.room_id
    ORDER BY sa.allocation_date DESC, r.room_no ASC, sa.session ASC
";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Select Seating Layout</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #fff7f7;
            color: #333;
        }

        /* HEADER */

        .header {
            background: linear-gradient(135deg, #450a0a, #7f1d1d, #991b1b);
            color: white;
            padding: 22px 45px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(69, 10, 10, 0.25);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-icon {
            width: 45px;
            height: 45px;
            background: rgba(255,255,255,0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
        }

        .header h2 {
            margin: 0;
            font-size: 24px;
        }

        .back {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.25);
            padding: 11px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            transition: 0.3s;
        }

        .back:hover {
            background: rgba(255,255,255,0.25);
        }

        /* MAIN CONTAINER */

        .container {
            width: 92%;
            max-width: 1100px;
            margin: 40px auto;
        }

        .page-title {
            text-align: center;
            margin-bottom: 35px;
        }

        .page-title h1 {
            margin: 0;
            color: #450a0a;
            font-size: 30px;
        }

        .page-title p {
            margin-top: 8px;
            color: #777;
            font-size: 15px;
        }

        /* CARDS */

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
            gap: 24px;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 26px;
            border: 1px solid #f0dede;
            box-shadow: 0 6px 18px rgba(69, 10, 10, 0.08);
            transition: 0.3s;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #7f1d1d, #991b1b);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(69, 10, 10, 0.14);
        }

        /* HALL TITLE */

        .hall-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 22px;
        }

        .hall-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #fce7e7;
            color: #7f1d1d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
        }

        .hall-title h3 {
            margin: 0;
            color: #450a0a;
            font-size: 21px;
        }

        .hall-title span {
            display: block;
            margin-top: 4px;
            color: #999;
            font-size: 12px;
        }

        /* INFORMATION */

        .info-box {
            background: #fffafa;
            border: 1px solid #f2dddd;
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 12px;
        }

        .info-label {
            color: #888;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .info-value {
            color: #333;
            font-size: 15px;
            font-weight: bold;
        }

        /* SESSION */

        .session {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .fn {
            background: #dcfce7;
            color: #166534;
        }

        .an {
            background: #fef3c7;
            color: #92400e;
        }

        /* VIEW BUTTON */

        .view-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            background: linear-gradient(135deg, #7f1d1d, #991b1b);
            color: white;
            padding: 13px;
            border-radius: 9px;
            margin-top: 20px;
            font-size: 14px;
            font-weight: bold;
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(127, 29, 29, 0.2);
        }

        .view-btn:hover {
            background: linear-gradient(135deg, #5f1111, #7f1d1d);
            transform: translateY(-2px);
        }

        /* EMPTY */

        .empty {
            background: white;
            padding: 55px 25px;
            text-align: center;
            border-radius: 16px;
            border: 1px solid #f0dede;
            box-shadow: 0 6px 18px rgba(69, 10, 10, 0.08);
        }

        .empty-icon {
            width: 65px;
            height: 65px;
            margin: 0 auto 15px;
            border-radius: 50%;
            background: #fce7e7;
            color: #7f1d1d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }

        .empty h3 {
            margin: 0;
            color: #450a0a;
        }

        .empty p {
            color: #888;
            margin-top: 8px;
        }

        /* RESPONSIVE */

        @media (max-width: 650px) {

            .header {
                padding: 18px 20px;
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }

            .back {
                text-align: center;
            }

            .container {
                width: 92%;
                margin: 30px auto;
            }

            .page-title h1 {
                font-size: 25px;
            }

            .cards {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<!-- HEADER -->

<div class="header">

    <div class="header-left">

        <div class="header-icon">
            🪑
        </div>

        <h2>Seating Layout</h2>

    </div>

    <a href="admin_dashboard.php" class="back">
        ← Back to Dashboard
    </a>

</div>


<!-- MAIN -->

<div class="container">

    <div class="page-title">

        <h1>Select Seating Layout</h1>

        <p>
            Choose a hall, examination date and session to view the seating arrangement.
        </p>

    </div>


    <?php if ($result && mysqli_num_rows($result) > 0) { ?>

        <div class="cards">

            <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                <div class="card">

                    <!-- HALL -->

                    <div class="hall-title">

                        <div class="hall-icon">
                            🏫
                        </div>

                        <div>

                            <h3>
                                Hall <?php echo htmlspecialchars($row['room_no']); ?>
                            </h3>

                            <span>
                                Seating Allocation
                            </span>

                        </div>

                    </div>


                    <!-- DATE -->

                    <div class="info-box">

                        <div class="info-label">
                            EXAMINATION DATE
                        </div>

                        <div class="info-value">
                            <?php
                            echo date(
                                "d-m-Y",
                                strtotime($row['allocation_date'])
                            );
                            ?>
                        </div>

                    </div>


                    <!-- SESSION -->

                    <div class="info-box">

                        <div class="info-label">
                            SESSION
                        </div>

                        <div class="info-value">

                            <span class="session <?php echo strtolower($row['session']); ?>">

                                <?php if ($row['session'] === 'FN') { ?>
                                    ☀ FN — Forenoon
                                <?php } else { ?>
                                    🌙 AN — Afternoon
                                <?php } ?>

                            </span>

                        </div>

                    </div>


                    <!-- VIEW -->

                    <a
                        class="view-btn"
                        href="seating_layout.php?room_id=<?php echo (int)$row['room_id']; ?>&date=<?php echo urlencode($row['allocation_date']); ?>&session=<?php echo urlencode($row['session']); ?>"
                    >

                        🪑 View Seating Layout

                    </a>

                </div>

            <?php } ?>

        </div>

    <?php } else { ?>

        <div class="empty">

            <div class="empty-icon">
                🪑
            </div>

            <h3>No Seating Layout Available</h3>

            <p>
                Seat allocations have not been created yet.
            </p>

        </div>

    <?php } ?>

</div>

</body>
</html>