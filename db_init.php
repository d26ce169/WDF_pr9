<?php
// db_init.php - Optional seeder function for Student Hub (degree database)
// Only seeds when explicitly called or requested via ?seed=demo

function seed_demo_data($conn) {
    if (!isset($conn) || !($conn instanceof mysqli)) {
        return;
    }

    // 1. Seed events
    $eventCheck = $conn->query("SELECT COUNT(*) as cnt FROM `events`");
    if ($eventCheck && (int)$eventCheck->fetch_assoc()['cnt'] === 0) {
        $sampleEvents = [
            ['name' => 'HackElite 2026 - 24hr Hackathon', 'venue' => 'Tech Lab Complex 4', 'start' => '09:00:00', 'end' => '21:00:00', 'date' => '2026-10-18'],
            ['name' => 'AI & Machine Learning Symposium', 'venue' => 'Main University Auditorium', 'start' => '10:30:00', 'end' => '15:30:00', 'date' => '2026-10-22'],
            ['name' => 'Tarang - Annual Cultural Fest', 'venue' => 'Open Air Amphitheatre', 'start' => '17:00:00', 'end' => '22:00:00', 'date' => '2026-10-28']
        ];
        $stmtEv = $conn->prepare("INSERT INTO `events` (`Name`, `Venue`, `Start_Time`, `End_Time`, `Date`) VALUES (?, ?, ?, ?, ?)");
        if ($stmtEv) {
            foreach ($sampleEvents as $ev) {
                $stmtEv->bind_param("sssss", $ev['name'], $ev['venue'], $ev['start'], $ev['end'], $ev['date']);
                $stmtEv->execute();
            }
            $stmtEv->close();
        }
    }

    // 2. Seed student if empty
    $stuCheck = $conn->query("SELECT COUNT(*) as cnt FROM `student`");
    if ($stuCheck && (int)$stuCheck->fetch_assoc()['cnt'] === 0) {
        $defaultPass = password_hash("Student@123", PASSWORD_DEFAULT);
        $stmtStu = $conn->prepare("INSERT INTO `student` (`STUDENT_NAME`, `USERNAME`, `EMAIL`, `MOBILE_NUMBER`, `GENDER`, `PASSWORD`) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmtStu) {
            $name = "Heet Doshi";
            $uname = "D26CE169";
            $email = "heet07@gmail.com";
            $mobile = "9876543210";
            $gender = "Male";
            $stmtStu->bind_param("ssssss", $name, $uname, $email, $mobile, $gender, $defaultPass);
            $stmtStu->execute();
            $stmtStu->close();
        }
    }

    // 3. Seed registration if empty
    $regCheck = $conn->query("SELECT COUNT(*) as cnt FROM `registration`");
    if ($regCheck && (int)$regCheck->fetch_assoc()['cnt'] === 0) {
        $stuRes = $conn->query("SELECT ID, STUDENT_NAME FROM `student` ORDER BY ID ASC LIMIT 1");
        $evRes = $conn->query("SELECT ID, Name, Date, Venue FROM `events` ORDER BY ID ASC LIMIT 1");
        if ($stuRes && $evRes && $stuRes->num_rows > 0 && $evRes->num_rows > 0) {
            $stu = $stuRes->fetch_assoc();
            $ev = $evRes->fetch_assoc();
            $stmtReg = $conn->prepare("INSERT INTO `registration` (`Student_Name`, `Event_Name`, `Date`, `Venue`, `student_id`, `event_id`) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmtReg) {
                $stmtReg->bind_param("ssssii", $stu['STUDENT_NAME'], $ev['Name'], $ev['Date'], $ev['Venue'], $stu['ID'], $ev['ID']);
                $stmtReg->execute();
                $stmtReg->close();
            }
        }
    }
}

// Only seed if explicitly requested in URL query string
if (isset($_GET['seed']) && $_GET['seed'] === 'demo') {
    seed_demo_data($conn);
}
?>
