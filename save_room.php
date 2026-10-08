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
   CHECK FORM SUBMISSION
========================================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: room_form.php");
    exit();

}


/* =========================================================
   GET FORM DATA
========================================================= */

$room_no = trim($_POST['room_no'] ?? '');

$capacity_input = trim($_POST['capacity'] ?? '');


/* =========================================================
   VALIDATION
========================================================= */

if ($room_no === '') {

    showMessage(
        "Error",
        "Please enter the hall number.",
        "error"
    );

    exit();

}


/* Hall number length */

if (strlen($room_no) > 50) {

    showMessage(
        "Invalid Hall Number",
        "Hall number must not exceed 50 characters.",
        "error"
    );

    exit();

}


/* Capacity must be a valid number */

if (
    $capacity_input === '' ||
    !ctype_digit($capacity_input)
) {

    showMessage(
        "Invalid Capacity",
        "Please enter a valid numeric hall capacity.",
        "error"
    );

    exit();

}


$capacity = intval($capacity_input);


/* Minimum capacity */

if ($capacity < 30) {

    showMessage(
        "Invalid Capacity",
        "Hall capacity must be at least <strong>30 seats</strong>.",
        "error"
    );

    exit();

}


/* =========================================================
   CHECK DUPLICATE HALL NUMBER
========================================================= */

$check_sql = "
    SELECT room_id
    FROM room
    WHERE room_no = ?
    LIMIT 1
";


$check_stmt = mysqli_prepare(
    $conn,
    $check_sql
);


if (!$check_stmt) {

    showMessage(
        "Database Error",
        "Unable to check the hall number.",
        "error"
    );

    mysqli_close($conn);

    exit();

}


mysqli_stmt_bind_param(
    $check_stmt,
    "s",
    $room_no
);


mysqli_stmt_execute(
    $check_stmt
);


$check_result =
    mysqli_stmt_get_result(
        $check_stmt
    );


if (mysqli_num_rows($check_result) > 0) {

    mysqli_stmt_close($check_stmt);

    mysqli_close($conn);

    showMessage(
        "Hall Already Exists",
        "The hall number <strong>"
        . htmlspecialchars(
            $room_no,
            ENT_QUOTES,
            'UTF-8'
        )
        . "</strong> already exists.",
        "error"
    );

    exit();

}


mysqli_stmt_close($check_stmt);


/* =========================================================
   INSERT HALL
========================================================= */

$sql = "
    INSERT INTO room
    (
        room_no,
        capacity
    )
    VALUES
    (
        ?,
        ?
    )
";


$stmt = mysqli_prepare(
    $conn,
    $sql
);


if (!$stmt) {

    $error = mysqli_error($conn);

    mysqli_close($conn);

    showMessage(
        "Unable to Save Hall",
        htmlspecialchars(
            $error,
            ENT_QUOTES,
            'UTF-8'
        ),
        "error"
    );

    exit();

}


mysqli_stmt_bind_param(
    $stmt,
    "si",
    $room_no,
    $capacity
);


/* =========================================================
   SAVE HALL
========================================================= */

if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    mysqli_close($conn);

    showMessage(
        "Hall Saved Successfully!",
        "
        <div class='details'>

            <div class='detail-box'>

                <span>🏫 Hall Number</span>

                <strong>"
                . htmlspecialchars(
                    $room_no,
                    ENT_QUOTES,
                    'UTF-8'
                )
                . "</strong>

            </div>


            <div class='detail-box'>

                <span>💺 Capacity</span>

                <strong>"
                . htmlspecialchars(
                    $capacity,
                    ENT_QUOTES,
                    'UTF-8'
                )
                . " Seats</strong>

            </div>

        </div>
        ",
        "success"
    );

    exit();

}


/* =========================================================
   DATABASE ERROR
========================================================= */

$error = mysqli_error($conn);

mysqli_stmt_close($stmt);

mysqli_close($conn);


showMessage(
    "Unable to Save Hall",
    htmlspecialchars(
        $error,
        ENT_QUOTES,
        'UTF-8'
    ),
    "error"
);


/* =========================================================
   MESSAGE FUNCTION
========================================================= */

function showMessage(
    $title,
    $message,
    $type
) {

    $isSuccess =
        ($type === "success");

    $icon =
        $isSuccess ? "✓" : "⚠";

    $titleColor =
        $isSuccess ? "#166534" : "#991b1b";

    $iconBackground =
        $isSuccess ? "#16a34a" : "#991b1b";

    $boxBackground =
        $isSuccess ? "#f0fdf4" : "#fef2f2";

    $borderColor =
        $isSuccess ? "#22c55e" : "#ef4444";

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
        <?= htmlspecialchars(
            $title,
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </title>


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

            padding: 25px;

        }


        .container {

            width: 520px;

            max-width: 100%;

            background: white;

            padding: 40px;

            border-radius: 18px;

            text-align: center;

            box-shadow:
                0 20px 50px
                rgba(0,0,0,0.25);

        }


        .icon {

            width: 70px;

            height: 70px;

            margin: 0 auto 18px;

            border-radius: 50%;

            background:
                <?= $iconBackground ?>;

            color: white;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 35px;

            font-weight: bold;

            box-shadow:
                0 8px 20px
                rgba(0,0,0,0.15);

        }


        h1 {

            margin: 0 0 25px;

            color:
                <?= $titleColor ?>;

            font-size: 27px;

        }


        .message {

            background:
                <?= $boxBackground ?>;

            border-left:
                4px solid
                <?= $borderColor ?>;

            padding: 18px;

            border-radius: 8px;

            color: #374151;

            font-size: 14px;

            line-height: 1.6;

            margin-bottom: 25px;

        }


        .details {

            display: flex;

            gap: 15px;

            margin-bottom: 25px;

        }


        .detail-box {

            flex: 1;

            background: #f9fafb;

            border: 1px solid #e5e7eb;

            border-radius: 10px;

            padding: 18px 10px;

        }


        .detail-box span {

            display: block;

            color: #6b7280;

            font-size: 13px;

            margin-bottom: 8px;

        }


        .detail-box strong {

            display: block;

            color: #7f1d1d;

            font-size: 18px;

        }


        .buttons {

            display: flex;

            gap: 12px;

        }


        .btn {

            flex: 1;

            padding: 13px;

            border-radius: 8px;

            text-decoration: none;

            font-size: 14px;

            font-weight: bold;

            transition: 0.2s;

        }


        .primary {

            background:
                linear-gradient(
                    135deg,
                    #7f1d1d,
                    #991b1b
                );

            color: white;

        }


        .primary:hover {

            transform:
                translateY(-1px);

        }


        .secondary {

            background: #f3f4f6;

            color: #374151;

        }


        .secondary:hover {

            background: #e5e7eb;

        }


        .dashboard {

            display: block;

            margin-top: 20px;

            color: #7f1d1d;

            text-decoration: none;

            font-size: 14px;

            font-weight: bold;

        }


        .dashboard:hover {

            text-decoration: underline;

        }


        @media(max-width: 550px) {

            .container {

                padding: 30px 22px;

            }


            .details {

                flex-direction: column;

            }


            .buttons {

                flex-direction: column;

            }

        }

    </style>

</head>


<body>


<div class="container">


    <div class="icon">

        <?= $icon ?>

    </div>


    <h1>

        <?= htmlspecialchars(
            $title,
            ENT_QUOTES,
            'UTF-8'
        ) ?>

    </h1>


    <div class="message">

        <?= $message ?>

    </div>


    <?php if ($isSuccess): ?>

        <div class="buttons">

            <a
                href="room_form.php"
                class="btn primary"
            >

                + Add Another Hall

            </a>


            <a
                href="seat_allocation.php"
                class="btn secondary"
            >

                Allocate Seats

            </a>

        </div>


        <a
            href="admin_dashboard.php"
            class="dashboard"
        >

            ← Back to Admin Dashboard

        </a>

    <?php else: ?>

        <div class="buttons">

            <a
                href="room_form.php"
                class="btn primary"
            >

                ← Try Again

            </a>


            <a
                href="admin_dashboard.php"
                class="btn secondary"
            >

                Dashboard

            </a>

        </div>

    <?php endif; ?>


</div>


</body>

</html>

<?php

}

?>