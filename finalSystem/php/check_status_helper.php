<?php
function checkAndGenerateControlNumber($conn, $user) {
    // Fetch user record from check_status
    $sql = "SELECT * FROM check_status WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "s", $user);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $completed = $row['admission_info_completed'] &&
                     $row['personal_info_completed'] &&
                     $row['family_bg_completed'] &&
                     $row['education_bg_completed'] &&
                     $row['med_his_info_completed'];

        // Check if control number should be generated
        if ($completed && empty($row['control_number']) && $row['control_number_click'] == 1) {
            // Count how many users already have a control number
            $count_sql = "SELECT COUNT(*) as total FROM check_status WHERE control_number IS NOT NULL";
            $count_result = mysqli_query($conn, $count_sql);
            $count_row = mysqli_fetch_assoc($count_result);
            $nextNumber = $count_row['total'] + 1;

            // Format the control number: CN-00001
            $newControlNumber = "CN-" . str_pad($nextNumber, 5, "0", STR_PAD_LEFT);

            // Update control number and set current_stage to 2
            $update = "UPDATE check_status SET control_number = ?, current_stage = 2 WHERE username = ?";
            $updateStmt = mysqli_prepare($conn, $update);
            if (!$updateStmt) {
                die("Prepare failed: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($updateStmt, "ss", $newControlNumber, $user);
            mysqli_stmt_execute($updateStmt);
            mysqli_stmt_close($updateStmt);
        }
    }

    mysqli_stmt_close($stmt);
}
?>
