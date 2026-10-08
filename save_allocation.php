<?php

session_start();

require_once "db_connect.php";


/* =========================================================
   ADMIN LOGIN CHECK
========================================================= */

if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true
) {

    header("Location: admin_login.php");
    exit;
}


/* =========================================================
   HELPER FUNCTIONS
========================================================= */

function normalizeGender($gender)
{
    $gender = strtoupper(trim($gender));

    if (
        $gender === "BOYS" ||
        $gender === "BOY" ||
        $gender === "MALE"
    ) {
        return "MALE";
    }

    if (
        $gender === "GIRLS" ||
        $gender === "GIRL" ||
        $gender === "FEMALE"
    ) {
        return "FEMALE";
    }

    return $gender;
}


function registerNumberValue($registerNo)
{
    if (
        preg_match(
            '/(\d+)$/',
            $registerNo,
            $matches
        )
    ) {

        return intval($matches[1]);
    }

    return 0;
}


function sortStudentsByRegister(&$students)
{
    usort(
        $students,
        function ($a, $b) {

            $numberA =
                registerNumberValue(
                    $a["register_no"]
                );

            $numberB =
                registerNumberValue(
                    $b["register_no"]
                );


            if ($numberA == $numberB) {

                return strcmp(
                    $a["register_no"],
                    $b["register_no"]
                );
            }


            return $numberA <=> $numberB;
        }
    );
}


/* =========================================================
   CHECK STUDENT SEAT
========================================================= */

function canPlaceStudent(
    $student,
    $seatNo,
    $placedSeats
) {

    $deptId =
        intval(
            $student["dept_id"]
        );


    $rowLetter =
        substr(
            $seatNo,
            0,
            1
        );


    $columnNumber =
        intval(
            substr(
                $seatNo,
                1
            )
        );


    /* --------------------------------
       LEFT CHECK
    --------------------------------- */

    if ($columnNumber > 1) {

        $leftSeat =
            $rowLetter .
            ($columnNumber - 1);


        if (
            isset(
                $placedSeats[$leftSeat]
            )
        ) {

            if (
                intval(
                    $placedSeats[
                        $leftSeat
                    ]["dept_id"]
                )
                ===
                $deptId
            ) {

                return false;
            }
        }
    }


    /* --------------------------------
       ABOVE CHECK
    --------------------------------- */

    $rowNumber =
        ord($rowLetter) -
        ord("A") +
        1;


    if ($rowNumber > 1) {

        $aboveLetter =
            chr(
                ord($rowLetter) - 1
            );


        $aboveSeat =
            $aboveLetter .
            $columnNumber;


        if (
            isset(
                $placedSeats[$aboveSeat]
            )
        ) {

            if (
                intval(
                    $placedSeats[
                        $aboveSeat
                    ]["dept_id"]
                )
                ===
                $deptId
            ) {

                return false;
            }
        }
    }


    return true;
}


/* =========================================================
   GET BENCH PARTNER
========================================================= */

function getBenchPartner($seatNo)
{
    $rowLetter =
        substr(
            $seatNo,
            0,
            1
        );


    $columnNumber =
        intval(
            substr(
                $seatNo,
                1
            )
        );


    if (
        $columnNumber % 2 === 1
    ) {

        return $rowLetter .
               ($columnNumber + 1);
    }


    return $rowLetter .
           ($columnNumber - 1);
}


/* =========================================================
   REGULAR EXAM BACKTRACKING
========================================================= */

function fillRegularSeats(
    $students,
    $positions,
    &$result,
    &$used
) {

    if (
        count($students) === 0
    ) {

        return true;
    }


    $student =
        array_shift(
            $students
        );


    foreach (
        $positions as $seatNo
    ) {

        if (
            isset(
                $used[$seatNo]
            )
        ) {

            continue;
        }


        $partnerSeat =
            getBenchPartner(
                $seatNo
            );


        if (
            isset(
                $used[$partnerSeat]
            )
        ) {

            continue;
        }


        foreach (
            $students
            as $partnerIndex =>
            $partnerStudent
        ) {


            /* Different departments */

            if (
                intval(
                    $student["dept_id"]
                )
                ===
                intval(
                    $partnerStudent["dept_id"]
                )
            ) {

                continue;
            }


            /* Same gender */

            if (
                normalizeGender(
                    $student["gender"]
                )
                !==
                normalizeGender(
                    $partnerStudent["gender"]
                )
            ) {

                continue;
            }


            /* Check first student */

            if (
                !canPlaceStudent(
                    $student,
                    $seatNo,
                    $result
                )
            ) {

                continue;
            }


            /* Check partner */

            if (
                !canPlaceStudent(
                    $partnerStudent,
                    $partnerSeat,
                    $result
                )
            ) {

                continue;
            }


            /* Place first student */

            $result[$seatNo] =
                $student;

            $used[$seatNo] =
                true;


            /* Place partner */

            $result[$partnerSeat] =
                $partnerStudent;

            $used[$partnerSeat] =
                true;


            /* Remove partner */

            $remainingStudents =
                $students;


            array_splice(
                $remainingStudents,
                $partnerIndex,
                1
            );


            /* Remaining positions */

            $remainingPositions = [];


            foreach (
                $positions as $position
            ) {

                if (
                    $position !== $seatNo &&
                    $position !== $partnerSeat
                ) {

                    $remainingPositions[] =
                        $position;
                }
            }


            /* Continue */

            if (
                fillRegularSeats(
                    $remainingStudents,
                    $remainingPositions,
                    $result,
                    $used
                )
            ) {

                return true;
            }


            /* Backtrack */

            unset(
                $result[$seatNo]
            );

            unset(
                $result[$partnerSeat]
            );

            unset(
                $used[$seatNo]
            );

            unset(
                $used[$partnerSeat]
            );
        }
    }


    return false;
}


/* =========================================================
   SEMESTER EXAM BACKTRACKING
========================================================= */

function fillSemesterSeats(
    $students,
    $positions,
    &$result,
    &$used
) {

    if (
        count($students) === 0
    ) {

        return true;
    }


    $student =
        array_shift(
            $students
        );


    foreach (
        $positions as $seatNo
    ) {

        if (
            isset(
                $used[$seatNo]
            )
        ) {

            continue;
        }


        if (
            !canPlaceStudent(
                $student,
                $seatNo,
                $result
            )
        ) {

            continue;
        }


        $result[$seatNo] =
            $student;

        $used[$seatNo] =
            true;


        $remainingPositions = [];


        foreach (
            $positions as $position
        ) {

            if (
                $position !== $seatNo
            ) {

                $remainingPositions[] =
                    $position;
            }
        }


        if (
            fillSemesterSeats(
                $students,
                $remainingPositions,
                $result,
                $used
            )
        ) {

            return true;
        }


        unset(
            $result[$seatNo]
        );

        unset(
            $used[$seatNo]
        );
    }


    return false;
}


/* =========================================================
   POST CHECK
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] !== "POST"
) {

    header(
        "Location: seat_allocation.php"
    );

    exit;
}


/* =========================================================
   READ FORM VALUES
========================================================= */

/*
   IMPORTANT:

   These names MUST match seat_allocation.php:

   room_id

   exam_id[department_id]

   student_count[department_id]

   selected_departments[]
*/

$room_id =
    isset($_POST["room_id"])
    ? intval($_POST["room_id"])
    : 0;


$exam_ids =
    isset($_POST["exam_id"])
    ? $_POST["exam_id"]
    : [];


$student_counts =
    isset($_POST["student_count"])
    ? $_POST["student_count"]
    : [];


$postedDepartments =
    isset($_POST["selected_departments"])
    ? $_POST["selected_departments"]
    : [];


/* =========================================================
   FIND SELECTED DEPARTMENTS
========================================================= */

$selectedDepartments = [];


foreach (
    $postedDepartments as $deptId
) {

    $deptId =
        intval($deptId);


    if (
        $deptId <= 0
    ) {

        continue;
    }


    if (
        !isset(
            $exam_ids[$deptId]
        )
        ||
        intval(
            $exam_ids[$deptId]
        ) <= 0
    ) {

        die(
            "Please select an exam for every selected department."
        );
    }


    if (
        !isset(
            $student_counts[$deptId]
        )
        ||
        intval(
            $student_counts[$deptId]
        ) <= 0
    ) {

        die(
            "Please enter the student count for every selected department."
        );
    }


    $selectedDepartments[] =
        $deptId;
}


/* Remove duplicates */

$selectedDepartments =
    array_values(
        array_unique(
            $selectedDepartments
        )
    );


/* =========================================================
   BASIC VALIDATION
========================================================= */

if (
    $room_id <= 0
) {

    die(
        "Please select a hall."
    );
}


if (
    empty($selectedDepartments)
) {

    die(
        "Please select at least one department and enter the student count."
    );
}


/* =========================================================
   GET ROOM
========================================================= */

$stmt =
    $conn->prepare("
        SELECT
            room_id,
            room_no,
            capacity
        FROM room
        WHERE room_id = ?
    ");


$stmt->bind_param(
    "i",
    $room_id
);


$stmt->execute();


$roomResult =
    $stmt->get_result();


if (
    $roomResult->num_rows === 0
) {

    die(
        "Selected hall was not found."
    );
}


$room =
    $roomResult->fetch_assoc();


$stmt->close();


/* Physical capacity */

if (
    intval(
        $room["capacity"]
    ) < 60
) {

    die(
        "Selected hall must have a capacity of at least 60."
    );
}


/* =========================================================
   GET EXAM DETAILS
========================================================= */

$examDetails = [];

$commonDate = null;
$commonSession = null;
$commonExamType = null;


foreach (
    $selectedDepartments as $deptId
) {

    $examId =
        intval(
            $exam_ids[$deptId]
        );


    if (
        $examId <= 0
    ) {

        die(
            "Please select an exam for every department."
        );
    }


    $stmt =
        $conn->prepare("
            SELECT
                exam_id,
                subject,
                exam_date,
                session,
                exam_type
            FROM exam
            WHERE exam_id = ?
        ");


    $stmt->bind_param(
        "i",
        $examId
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    if (
        $result->num_rows === 0
    ) {

        die(
            "Selected exam was not found."
        );
    }


    $exam =
        $result->fetch_assoc();


    $stmt->close();


    $examDetails[$deptId] =
        $exam;


    /* Same date */

    if (
        $commonDate === null
    ) {

        $commonDate =
            $exam["exam_date"];

    } elseif (
        $commonDate !==
        $exam["exam_date"]
    ) {

        die(
            "All selected exams must have the same exam date."
        );
    }


    /* Same session */

    if (
        $commonSession === null
    ) {

        $commonSession =
            $exam["session"];

    } elseif (
        $commonSession !==
        $exam["session"]
    ) {

        die(
            "All selected exams must have the same session (FN or AN)."
        );
    }


    /* Same exam type */

    if (
        $commonExamType === null
    ) {

        $commonExamType =
            $exam["exam_type"];

    } elseif (
        $commonExamType !==
        $exam["exam_type"]
    ) {

        die(
            "All selected exams must have the same exam type."
        );
    }
}


/* =========================================================
   TOTAL STUDENTS
========================================================= */

$totalStudents = 0;


foreach (
    $selectedDepartments as $deptId
) {

    $count =
        intval(
            $student_counts[$deptId]
        );


    if (
        $count <= 0
    ) {

        die(
            "Student count must be greater than zero."
        );
    }


    $totalStudents +=
        $count;
}


/* =========================================================
   EXAM TYPE LIMIT
========================================================= */

if (
    $commonExamType === "REGULAR"
) {

    if (
        $totalStudents > 60
    ) {

        die(
            "Regular Exam allows a maximum of 60 students per hall."
        );
    }


    if (
        $totalStudents % 2 !== 0
    ) {

        die(
            "Regular Exam requires an even number of students."
        );
    }


} elseif (
    $commonExamType === "SEMESTER"
) {

    if (
        $totalStudents > 20
    ) {

        die(
            "Semester Exam allows a maximum of 20 students per hall."
        );
    }

} else {

    die(
        "Invalid exam type."
    );
}


/* =========================================================
   FETCH AVAILABLE STUDENTS
========================================================= */

$studentsByDept = [];


foreach (
    $selectedDepartments as $deptId
) {

    $requiredCount =
        intval(
            $student_counts[$deptId]
        );


    /*
       Students already allocated for the
       same date + session are excluded.
    */

    $stmt =
        $conn->prepare("
            SELECT
                s.student_id,
                s.register_no,
                s.student_name,
                s.dept_id,
                s.year,
                s.gender
            FROM student s
            WHERE s.dept_id = ?

            AND NOT EXISTS (
                SELECT 1
                FROM seat_allocation sa
                WHERE sa.student_id = s.student_id
                  AND sa.allocation_date = ?
                  AND sa.session = ?
            )
        ");


    $stmt->bind_param(
        "iss",
        $deptId,
        $commonDate,
        $commonSession
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    $students = [];


    while (
        $row =
        $result->fetch_assoc()
    ) {

        $row["gender"] =
            normalizeGender(
                $row["gender"]
            );


        $students[] =
            $row;
    }


    $stmt->close();


    sortStudentsByRegister(
        $students
    );


    if (
        count($students) <
        $requiredCount
    ) {

        die(
            "Not enough available students in department ID "
            . $deptId
            . " for "
            . $commonDate
            . " "
            . $commonSession
            . "."
        );
    }


    /*
       Take first available students.

       Example:

       First hall:
       CSE001-CSE020

       Next hall:
       CSE021-CSE040
    */

    $studentsByDept[$deptId] =
        array_slice(
            $students,
            0,
            $requiredCount
        );
}


/* =========================================================
   COMBINE STUDENTS
========================================================= */

$allStudents = [];


foreach (
    $studentsByDept as $deptStudents
) {

    foreach (
        $deptStudents as $student
    ) {

        $allStudents[] =
            $student;
    }
}


if (
    count($allStudents) !==
    $totalStudents
) {

    die(
        "Student count mismatch."
    );
}


/* =========================================================
   CREATE 60 PHYSICAL SEATS
========================================================= */

$positions = [];


for (
    $row = 0;
    $row < 10;
    $row++
) {

    $letter =
        chr(
            ord("A") + $row
        );


    for (
        $column = 1;
        $column <= 6;
        $column++
    ) {

        $positions[] =
            $letter . $column;
    }
}


/* =========================================================
   SEMESTER POSITIONS
========================================================= */

$semesterPositions = [];


for (
    $row = 0;
    $row < 10;
    $row++
) {

    $letter =
        chr(
            ord("A") + $row
        );


    /*
       One student per bench:

       A1
       A3
       A5

       B1
       B3
       B5
       ...
    */

    for (
        $column = 1;
        $column <= 6;
        $column += 2
    ) {

        $semesterPositions[] =
            $letter . $column;
    }
}


/* =========================================================
   GROUP STUDENTS
========================================================= */

$groups = [];


foreach (
    $allStudents as $student
) {

    $key =
        intval(
            $student["dept_id"]
        )
        . "_"
        .
        normalizeGender(
            $student["gender"]
        );


    if (
        !isset(
            $groups[$key]
        )
    ) {

        $groups[$key] = [];
    }


    $groups[$key][] =
        $student;
}


/* =========================================================
   FINAL SEATING
========================================================= */

$finalSeats = [];
$usedSeats = [];


/* =========================================================
   REGULAR EXAM
========================================================= */

if (
    $commonExamType ===
    "REGULAR"
) {

    $studentsForArrangement =
        $allStudents;


    $success =
        fillRegularSeats(
            $studentsForArrangement,
            $positions,
            $finalSeats,
            $usedSeats
        );


    /*
       If first attempt fails,
       interleave departments/genders.
    */

    if (
        !$success
    ) {

        $finalSeats = [];
        $usedSeats = [];


        $balancedStudents = [];


        $maxGroupSize = 0;


        foreach (
            $groups as $group
        ) {

            $maxGroupSize =
                max(
                    $maxGroupSize,
                    count($group)
                );
        }


        for (
            $i = 0;
            $i < $maxGroupSize;
            $i++
        ) {

            foreach (
                $groups as $group
            ) {

                if (
                    isset(
                        $group[$i]
                    )
                ) {

                    $balancedStudents[] =
                        $group[$i];
                }
            }
        }


        $success =
            fillRegularSeats(
                $balancedStudents,
                $positions,
                $finalSeats,
                $usedSeats
            );
    }


    if (
        !$success
    ) {

        die(
            "A valid seating arrangement could not be created with the selected departments, genders and student counts. Please change the student combination."
        );
    }


/* =========================================================
   SEMESTER EXAM
========================================================= */

} else {

    $studentsForArrangement =
        $allStudents;


    $success =
        fillSemesterSeats(
            $studentsForArrangement,
            $semesterPositions,
            $finalSeats,
            $usedSeats
        );


    if (
        !$success
    ) {

        die(
            "A valid semester seating arrangement could not be created. Please change the selected student counts."
        );
    }
}


/* =========================================================
   VERIFY SEAT COUNT
========================================================= */

if (
    count($finalSeats) !==
    $totalStudents
) {

    die(
        "Seat allocation failed because the generated seat count does not match the student count."
    );
}


/* =========================================================
   START TRANSACTION
========================================================= */

$conn->begin_transaction();


try {


    /* =====================================================
       CHECK DUPLICATE STUDENT
    ===================================================== */

    $checkStudentStmt =
        $conn->prepare("
            SELECT allocation_id
            FROM seat_allocation
            WHERE student_id = ?
              AND allocation_date = ?
              AND session = ?
            LIMIT 1
        ");


    /* =====================================================
       CHECK DUPLICATE SEAT
    ===================================================== */

    $checkSeatStmt =
        $conn->prepare("
            SELECT allocation_id
            FROM seat_allocation
            WHERE room_id = ?
              AND allocation_date = ?
              AND session = ?
              AND seat_no = ?
            LIMIT 1
        ");


    /* =====================================================
       INSERT
    ===================================================== */

    $insertStmt =
        $conn->prepare("
            INSERT INTO seat_allocation
            (
                student_id,
                exam_id,
                allocation_date,
                session,
                room_id,
                seat_no
            )
            VALUES
            (?, ?, ?, ?, ?, ?)
        ");


    /* =====================================================
       INSERT EACH STUDENT
    ===================================================== */

    foreach (
        $finalSeats as $seatNo => $student
    ) {

        $studentId =
            intval(
                $student["student_id"]
            );


        $studentDeptId =
            intval(
                $student["dept_id"]
            );


        /* Get department exam */

        if (
            !isset(
                $exam_ids[
                    $studentDeptId
                ]
            )
        ) {

            throw new Exception(
                "Exam information is missing for department."
            );
        }


        $studentExamId =
            intval(
                $exam_ids[
                    $studentDeptId
                ]
            );


        /* =================================================
           CHECK STUDENT DUPLICATE
        ================================================= */

        $checkStudentStmt->bind_param(
            "iss",
            $studentId,
            $commonDate,
            $commonSession
        );


        $checkStudentStmt->execute();


        $duplicateStudentResult =
            $checkStudentStmt->get_result();


        if (
            $duplicateStudentResult->num_rows > 0
        ) {

            throw new Exception(
                "Student "
                . $student["register_no"]
                . " is already allocated for "
                . $commonDate
                . " "
                . $commonSession
                . "."
            );
        }


        /* =================================================
           CHECK SEAT DUPLICATE
        ================================================= */

        $checkSeatStmt->bind_param(
            "isss",
            $room_id,
            $commonDate,
            $commonSession,
            $seatNo
        );


        $checkSeatStmt->execute();


        $duplicateSeatResult =
            $checkSeatStmt->get_result();


        if (
            $duplicateSeatResult->num_rows > 0
        ) {

            throw new Exception(
                "Seat "
                . $seatNo
                . " is already allocated in this hall."
            );
        }


        /* =================================================
           INSERT
        ================================================= */

        $insertStmt->bind_param(
            "iissis",
            $studentId,
            $studentExamId,
            $commonDate,
            $commonSession,
            $room_id,
            $seatNo
        );


        if (
            !$insertStmt->execute()
        ) {

            throw new Exception(
                "Failed to save seat "
                . $seatNo
                . "."
            );
        }
    }


    /* CLOSE */

    $checkStudentStmt->close();
    $checkSeatStmt->close();
    $insertStmt->close();


    /* COMMIT */

    $conn->commit();


    /* =====================================================
       SUCCESS PAGE
    ===================================================== */

    ?>

    <!DOCTYPE html>

    <html>

    <head>

        <title>Allocation Successful</title>

        <style>

        body {
            margin: 0;
            padding: 0;

            font-family: Arial, sans-serif;

            background: #f5f1f2;

            display: flex;
            justify-content: center;
            align-items: center;

            min-height: 100vh;
        }

        .box {
            background: white;

            width: 500px;
            max-width: 90%;

            padding: 40px;

            border-radius: 18px;

            text-align: center;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,0.12);
        }

        .icon {
            width: 70px;
            height: 70px;

            background: #198754;
            color: white;

            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            margin: 0 auto 20px;

            font-size: 38px;
            font-weight: bold;
        }

        h1 {
            color: #6d1f3c;
            margin-bottom: 10px;
        }

        p {
            color: #555;
            line-height: 1.6;
        }

        .details {
            background: #f8f3f5;

            padding: 18px;

            border-radius: 10px;

            margin: 20px 0;

            text-align: left;

            line-height: 1.6;
        }

        .btn {
            display: inline-block;

            padding: 12px 22px;

            background: #6d1f3c;

            color: white;

            text-decoration: none;

            border-radius: 8px;

            margin: 5px;
        }

        .btn:hover {
            background: #54172f;
        }

        </style>

    </head>


    <body>

        <div class="box">

            <div class="icon">
                ✓
            </div>


            <h1>
                Seat Allocation Successful
            </h1>


            <p>
                The seats have been allocated successfully.
            </p>


            <div class="details">

                <strong>Hall:</strong>

                <?php
                echo htmlspecialchars(
                    $room["room_no"]
                );
                ?>

                <br><br>


                <strong>Date:</strong>

                <?php
                echo htmlspecialchars(
                    $commonDate
                );
                ?>

                <br><br>


                <strong>Session:</strong>

                <?php
                echo htmlspecialchars(
                    $commonSession
                );
                ?>

                <br><br>


                <strong>Exam Type:</strong>

                <?php
                echo htmlspecialchars(
                    $commonExamType
                );
                ?>

                <br><br>


                <strong>Total Students:</strong>

                <?php
                echo $totalStudents;
                ?>

            </div>


            <a
                class="btn"
                href="seat_allocation.php"
            >
                Allocate Another Hall
            </a>


            <a
                class="btn"
                href="view.php"
            >
                View Allocations
            </a>

        </div>

    </body>

    </html>

    <?php


/* =========================================================
   ERROR
========================================================= */

} catch (Exception $e) {


    $conn->rollback();


    ?>

    <!DOCTYPE html>

    <html>

    <head>

        <title>Allocation Failed</title>

        <style>

        body {
            margin: 0;
            padding: 0;

            font-family: Arial, sans-serif;

            background: #f8f1f1;

            display: flex;
            justify-content: center;
            align-items: center;

            min-height: 100vh;
        }

        .box {
            background: white;

            width: 550px;
            max-width: 90%;

            padding: 40px;

            border-radius: 18px;

            text-align: center;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,0.12);
        }

        .icon {
            width: 70px;
            height: 70px;

            background: #dc3545;
            color: white;

            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            margin: 0 auto 20px;

            font-size: 38px;
            font-weight: bold;
        }

        h1 {
            color: #842029;
            margin-bottom: 15px;
        }

        .error {
            background: #f8d7da;

            color: #842029;

            padding: 15px;

            border-radius: 8px;

            margin: 20px 0;

            line-height: 1.5;
        }

        .btn {
            display: inline-block;

            padding: 12px 22px;

            background: #6d1f3c;

            color: white;

            text-decoration: none;

            border-radius: 8px;

            margin: 5px;
        }

        </style>

    </head>


    <body>

        <div class="box">

            <div class="icon">
                !
            </div>


            <h1>
                Allocation Failed
            </h1>


            <div class="error">

                <?php
                echo htmlspecialchars(
                    $e->getMessage()
                );
                ?>

            </div>


            <a
                class="btn"
                href="seat_allocation.php"
            >
                Back to Allocation
            </a>

        </div>

    </body>

    </html>

    <?php
}

?>