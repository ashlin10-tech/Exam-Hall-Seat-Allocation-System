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


$message = "";
$message_type = "";


/* =========================================================
   DELETE HALL
========================================================= */

if (isset($_GET['delete_id'])) {

    $room_id = intval($_GET['delete_id']);


    if ($room_id <= 0) {

        $message = "Invalid hall selected.";
        $message_type = "error";

    } else {


        /* =================================================
           GET HALL DETAILS
        ================================================= */

        $room_stmt = $conn->prepare("
            SELECT room_no, capacity
            FROM room
            WHERE room_id = ?
            LIMIT 1
        ");

        $room_stmt->bind_param("i", $room_id);
        $room_stmt->execute();

        $room_result = $room_stmt->get_result();
        $room = $room_result->fetch_assoc();

        $room_stmt->close();


        if (!$room) {

            $message = "The selected hall does not exist.";
            $message_type = "error";

        } else {


            /* =============================================
               CHECK SEAT ALLOCATIONS
            ============================================= */

            $check = $conn->prepare("
                SELECT COUNT(*) AS total
                FROM seat_allocation
                WHERE room_id = ?
            ");

            $check->bind_param("i", $room_id);
            $check->execute();

            $result = $check->get_result();
            $row = $result->fetch_assoc();

            $check->close();


            /* =============================================
               CHECK FACULTY ASSIGNMENT
            ============================================= */

            $check2 = $conn->prepare("
                SELECT COUNT(*) AS total
                FROM exam_invigilator
                WHERE room_id = ?
            ");

            $check2->bind_param("i", $room_id);
            $check2->execute();

            $result2 = $check2->get_result();
            $row2 = $result2->fetch_assoc();

            $check2->close();


            /* =============================================
               CANNOT DELETE IF ALLOCATIONS EXIST
            ============================================= */

            if ($row['total'] > 0) {

                $message =
                    "Hall <strong>"
                    . htmlspecialchars(
                        $room['room_no'],
                        ENT_QUOTES,
                        'UTF-8'
                    )
                    . "</strong> is already used in seat allocation. "
                    . "Delete its seating allocation first.";

                $message_type = "error";

            }


            /* =============================================
               CANNOT DELETE IF FACULTY ASSIGNED
            ============================================= */

            elseif ($row2['total'] > 0) {

                $message =
                    "Hall <strong>"
                    . htmlspecialchars(
                        $room['room_no'],
                        ENT_QUOTES,
                        'UTF-8'
                    )
                    . "</strong> is assigned to a faculty member. "
                    . "Remove the faculty assignment first.";

                $message_type = "error";

            }


            /* =============================================
               DELETE HALL
            ============================================= */

            else {

                $delete = $conn->prepare("
                    DELETE FROM room
                    WHERE room_id = ?
                ");

                $delete->bind_param("i", $room_id);


                if ($delete->execute()) {

                    if ($delete->affected_rows > 0) {

                        $message =
                            "Hall <strong>"
                            . htmlspecialchars(
                                $room['room_no'],
                                ENT_QUOTES,
                                'UTF-8'
                            )
                            . "</strong> was deleted successfully.";

                        $message_type = "success";

                    } else {

                        $message =
                            "The hall could not be deleted.";

                        $message_type = "error";

                    }

                } else {

                    $message =
                        "Unable to delete the hall.";

                    $message_type = "error";

                }

                $delete->close();

            }

        }

    }

}


/* =========================================================
   GET ALL HALLS
========================================================= */

$rooms = $conn->query("
    SELECT
        room_id,
        room_no,
        capacity
    FROM room
    ORDER BY room_no ASC
");

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Delete Hall | Admin</title>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:
        linear-gradient(
            135deg,
            #fff7f7,
            #fef2f2
        );

    min-height: 100vh;

    color: #1f2937;

}


/* =========================================================
   HEADER
========================================================= */

.header {

    background:
        linear-gradient(
            135deg,
            #450a0a,
            #7f1d1d,
            #991b1b
        );

    color: white;

    padding: 22px 40px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    box-shadow:
        0 5px 20px
        rgba(69,10,10,0.25);

}


.header-left {

    display: flex;

    align-items: center;

    gap: 14px;

}


.header-icon {

    width: 46px;

    height: 46px;

    border-radius: 12px;

    background:
        rgba(255,255,255,0.15);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 24px;

}


.header h1 {

    margin: 0;

    font-size: 25px;

    font-weight: 700;

}


.header p {

    margin: 4px 0 0;

    color: #fecaca;

    font-size: 13px;

}


/* =========================================================
   BACK BUTTON
========================================================= */

.back-btn {

    background: white;

    color: #7f1d1d;

    padding: 10px 18px;

    border-radius: 8px;

    text-decoration: none;

    font-weight: 600;

    transition: 0.2s;

}


.back-btn:hover {

    background: #fee2e2;

    transform:
        translateY(-1px);

}


/* =========================================================
   MAIN CONTAINER
========================================================= */

.container {

    width: 90%;

    max-width: 1100px;

    margin: 35px auto 60px;

}


/* =========================================================
   PAGE HEADING
========================================================= */

.page-heading {

    margin-bottom: 22px;

}


.page-heading h2 {

    margin: 0;

    color: #450a0a;

    font-size: 24px;

}


.page-heading p {

    margin: 7px 0 0;

    color: #64748b;

    font-size: 14px;

}


/* =========================================================
   MESSAGE
========================================================= */

.message {

    padding: 15px 18px;

    border-radius: 10px;

    margin-bottom: 22px;

    font-weight: 600;

    display: flex;

    align-items: center;

    gap: 10px;

}


.success {

    background: #dcfce7;

    color: #166534;

    border: 1px solid #bbf7d0;

}


.error {

    background: #fee2e2;

    color: #991b1b;

    border: 1px solid #fecaca;

}


/* =========================================================
   HALL LIST
========================================================= */

.room-list {

    display: flex;

    flex-direction: column;

    gap: 15px;

}


/* =========================================================
   HALL CARD
========================================================= */

.room-card {

    background: white;

    border: 1px solid #f1d4d4;

    border-radius: 14px;

    padding: 20px 22px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    box-shadow:
        0 5px 18px
        rgba(69,10,10,0.07);

    transition: 0.2s;

}


.room-card:hover {

    transform:
        translateY(-2px);

    box-shadow:
        0 8px 25px
        rgba(69,10,10,0.12);

    border-color: #e5baba;

}


/* =========================================================
   HALL INFORMATION
========================================================= */

.room-left {

    display: flex;

    align-items: center;

    gap: 16px;

}


.room-icon {

    width: 52px;

    height: 52px;

    border-radius: 12px;

    background: #fef2f2;

    color: #991b1b;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 25px;

}


.room-info h3 {

    margin: 0 0 7px;

    color: #450a0a;

    font-size: 18px;

}


.room-info p {

    margin: 0;

    color: #64748b;

    font-size: 14px;

}


/* =========================================================
   CAPACITY BADGE
========================================================= */

.capacity {

    display: inline-block;

    margin-left: 8px;

    padding: 4px 9px;

    border-radius: 20px;

    background: #fff1f2;

    color: #9f1239;

    font-size: 12px;

    font-weight: 600;

}


/* =========================================================
   DELETE BUTTON
========================================================= */

.delete-btn {

    background: #991b1b;

    color: white;

    border: none;

    padding: 11px 18px;

    border-radius: 8px;

    text-decoration: none;

    font-weight: 600;

    cursor: pointer;

    transition: 0.2s;

    display: inline-flex;

    align-items: center;

    gap: 7px;

}


.delete-btn:hover {

    background: #7f1d1d;

    transform:
        translateY(-1px);

    box-shadow:
        0 5px 12px
        rgba(127,29,29,0.2);

}


/* =========================================================
   EMPTY STATE
========================================================= */

.no-rooms {

    background: white;

    padding: 55px 30px;

    text-align: center;

    border-radius: 14px;

    border: 1px solid #f1d4d4;

    box-shadow:
        0 5px 18px
        rgba(69,10,10,0.06);

}


.no-icon {

    font-size: 45px;

    margin-bottom: 12px;

}


.no-rooms h3 {

    margin: 0 0 8px;

    color: #450a0a;

}


.no-rooms p {

    margin: 0;

    color: #64748b;

    font-size: 14px;

}


/* =========================================================
   INFORMATION BOX
========================================================= */

.info-box {

    margin-top: 25px;

    background: #fff7ed;

    border: 1px solid #fed7aa;

    border-radius: 10px;

    padding: 16px 18px;

    color: #9a3412;

    font-size: 13px;

    line-height: 1.6;

}


.info-box strong {

    color: #7c2d12;

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 700px) {

    .header {

        padding: 18px 20px;

        gap: 15px;

    }


    .header h1 {

        font-size: 20px;

    }


    .header p {

        display: none;

    }


    .back-btn {

        padding: 9px 12px;

        font-size: 13px;

    }


    .container {

        width: 92%;

        margin-top: 25px;

    }


    .room-card {

        flex-direction: column;

        align-items: stretch;

        gap: 18px;

    }


    .room-left {

        align-items: flex-start;

    }


    .delete-btn {

        justify-content: center;

        width: 100%;

    }

}

</style>

</head>


<body>


<!-- =======================================================
     HEADER
======================================================= -->

<div class="header">

    <div class="header-left">

        <div class="header-icon">
            🏫
        </div>


        <div>

            <h1>
                Delete Hall
            </h1>

            <p>
                Remove unused examination halls
            </p>

        </div>

    </div>


    <a
        href="admin_dashboard.php"
        class="back-btn"
    >

        ← Dashboard

    </a>

</div>


<!-- =======================================================
     MAIN
======================================================= -->

<div class="container">


    <div class="page-heading">

        <h2>
            Examination Halls
        </h2>

        <p>
            Select a hall below to remove it from the system.
        </p>

    </div>


    <!-- ===================================================
         MESSAGE
    =================================================== -->

    <?php if ($message != ""): ?>

        <div class="message <?php echo $message_type; ?>">

            <?php if ($message_type == "success"): ?>

                <span>✅</span>

            <?php else: ?>

                <span>⚠️</span>

            <?php endif; ?>


            <span>

                <?php
                echo $message;
                ?>

            </span>

        </div>

    <?php endif; ?>


    <!-- ===================================================
         HALL LIST
    =================================================== -->

    <?php if ($rooms && $rooms->num_rows > 0): ?>


        <div class="room-list">


            <?php while ($room = $rooms->fetch_assoc()): ?>


                <div class="room-card">


                    <div class="room-left">


                        <div class="room-icon">
                            🏫
                        </div>


                        <div class="room-info">


                            <h3>

                                Hall
                                <?php
                                echo htmlspecialchars(
                                    $room['room_no'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>

                            </h3>


                            <p>

                                Seating Capacity:

                                <span class="capacity">

                                    <?php
                                    echo htmlspecialchars(
                                        $room['capacity'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                    ?>

                                    students

                                </span>

                            </p>


                        </div>


                    </div>


                    <a
                        href="delete_room.php?delete_id=<?php echo intval($room['room_id']); ?>"
                        class="delete-btn"

                        onclick="return confirm(
                            'Are you sure you want to delete Hall <?php echo htmlspecialchars($room['room_no'], ENT_QUOTES, 'UTF-8'); ?>?'
                        );"
                    >

                        🗑️ Delete

                    </a>


                </div>


            <?php endwhile; ?>


        </div>


        <!-- =================================================
             INFORMATION
        ================================================= -->

        <div class="info-box">

            <strong>Important:</strong>

            A hall cannot be deleted if it already contains
            seat allocations or is assigned to a faculty member.
            Remove those records first, then try deleting the
            hall again.

        </div>


    <?php else: ?>


        <!-- =================================================
             EMPTY STATE
        ================================================= -->

        <div class="no-rooms">


            <div class="no-icon">
                🏫
            </div>


            <h3>
                No Halls Available
            </h3>


            <p>
                There are currently no examination halls
                in the system.
            </p>


        </div>


    <?php endif; ?>


</div>


</body>

</html>