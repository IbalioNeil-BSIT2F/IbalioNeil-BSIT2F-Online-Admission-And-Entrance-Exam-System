<?php
include('..\php\connection.php');
session_start();

$user = $_SESSION['user']; // assuming username is stored in session

$msg = '';
$edit_mode = false;
$entry = $type = $strand = $lrn = $program = '';

// Check if the user already has admission info
$res = mysqli_query($conn, "SELECT * FROM admission_info WHERE username = '$user'");
if (mysqli_num_rows($res) > 0) {
    $edit_mode = true;
    $row = mysqli_fetch_assoc($res);
    $entry = $row['entry'];
    $type = $row['type'];
    $strand = $row['strand'];
    $lrn = $row['lrn'];
    $program = $row['program'];
}

if (isset($_POST['submit'])) {
    $entry = $_POST['entry'];
    $type = $_POST['type'];
    $strand = $_POST['strand'];
    $lrn = $_POST['lrn'];
    $program = $_POST['program'];

    if ($edit_mode) {
        // Update existing record
        $update = "UPDATE admission_info SET entry='$entry', type='$type', strand='$strand', lrn='$lrn', program='$program' WHERE username='$user'";
        mysqli_query($conn, $update) or die(mysqli_error($conn));
        $msg = "Updated successfully";
    } else {
        // Insert new record
        $insert = "INSERT INTO admission_info (username, entry, type, strand, lrn, program) VALUES ('$user', '$entry', '$type', '$strand', '$lrn', '$program')";
        mysqli_query($conn, $insert) or die(mysqli_error($conn));
        $msg = "Submitted successfully";
    }

    // ✅ Update or insert into check_status
    $check_status_sql = "INSERT INTO check_status (username, admission_info_completed)
                         VALUES ('$user', 1)
                         ON DUPLICATE KEY UPDATE admission_info_completed = 1";
    mysqli_query($conn, $check_status_sql) or die(mysqli_error($conn));

    // ✅ Include helper and check if control number needs to be created
    include_once('..\php\check_status_helper.php');
    checkAndGenerateControlNumber($conn, $user);

    echo "<script>window.location='useradmission.php';</script>";
}
?>
<?php include('../php/userformheader.php'); ?>



        <!-- Main content container -->
        <div class="content">
            <div class="maincontainer">
                <div class="admission-form">
                <h2>Admission Information</h2>

                <form method="POST" action="">
                    <div class="form-group">
                        <div class="form-control">
                        <label for="entry">ENTRY</label>
                        <select name="entry" required>
                          <option value="">Select Item</option>
                          <option value="New" <?php if($entry=='New') echo 'selected'; ?>>New</option>
                          <option value="Transferee" <?php if($entry=='Transferee') echo 'selected'; ?>>Transferee</option>
                          <option value="2nd Courser" <?php if($entry=='2nd Courser') echo 'selected'; ?>>2nd Courser</option>
                        </select>
                        </div>

                        <div class="form-control">
                            <label for="type">TYPE OF NEW STUDENT</label>
                            <select name="type" required>
                                <option value="">Select Item</option>
                                <option value="Grade 12 student" <?php if($type == 'Grade 12 student') echo 'selected'; ?>>Grade 12 student</option>
                                <option value="SHS Graduate" <?php if($type == 'SHS Graduate') echo 'selected'; ?>>SHS Graduate</option>
                            </select>
                        </div>

                        <div class="form-control">
                            <label for="strand">SHS STRAND</label>
                            <select name="strand" required>
                                <option value="">Select Item</option>
                                <option value="STEM" <?php if($strand == 'STEM') echo 'selected'; ?>>STEM</option>
                                <option value="TVL" <?php if($strand == 'TVL') echo 'selected'; ?>>TVL</option>
                                <option value="HUMMS" <?php if($strand == 'HUMMS') echo 'selected'; ?>>HUMMS</option>
                                <option value="ABM" <?php if($strand == 'ABM') echo 'selected'; ?>>ABM</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                      <div class="form-control" style="flex: 1;">
                          <label for="lrn">LEARNER’S REFERENCE NUMBER</label>
                          <input type="text" name="lrn" placeholder="Input Text Field" value="<?php echo htmlspecialchars($lrn); ?>" required>
                      </div>
                  </div>
                    <div class="divider"></div>

                    <div class="submit-section">
                        <label for="program">CHOOSE YOUR PROGRAM</label>
                        <select name="program" required>
                            <option value="">Select Course</option>
                            <option value="BSBM" <?php if($program == 'BSBM') echo 'selected'; ?>>BSBM</option>
                            <option value="BSCS" <?php if($program == 'BSCS') echo 'selected'; ?>>BSCS</option>
                            <option value="BSCE" <?php if($program == 'BSCE') echo 'selected'; ?>>BSCE</option>
                            <option value="BSIT" <?php if($program == 'BSIT') echo 'selected'; ?>>BSIT</option>
                        </select>
                        <button type="submit" name="submit" class="confirm-btn">Confirm</button>
                        <p class="msg"><?php echo $msg; ?></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

  </div>

</body>
</html>