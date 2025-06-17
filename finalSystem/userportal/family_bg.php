<?php
include('..\php\connection.php');
session_start();

$user = $_SESSION['user'];

$edit_mode = false;
$msg = '';

// Initialize variables
$gurdianname = $gnumber = $goccupation = '';
$fathername = $fnumber = $foccupation = '';
$mothername = $mnumber = $moccupation = '';
$fam_month_inc = $numsibling = $birthorder = $soloparent = $fam_work_abroad = '';

// Fetch data if it exists
$res = mysqli_query($conn, "SELECT * FROM family_bg WHERE username = '$user'");
if (mysqli_num_rows($res) > 0) {
    $edit_mode = true;
    $row = mysqli_fetch_assoc($res);
    $gurdianname = $row['gurdianname'];
    $gnumber = $row['gnumber'];
    $goccupation = $row['goccupation'];
    $fathername = $row['fathername'];
    $fnumber = $row['fnumber'];
    $foccupation = $row['foccupation'];
    $mothername = $row['mothername'];
    $mnumber = $row['mnumber'];
    $moccupation = $row['moccupation'];
    $fam_month_inc = $row['fam_month_inc'];
    $numsibling = $row['numsibling'];
    $birthorder = $row['birthorder'];
    $soloparent = $row['soloparent'];
    $fam_work_abroad = $row['fam_work_abroad'];
}

// Handle form submission
if (isset($_POST['submit'])) {
    $gurdianname = $_POST['guardian_name'];
    $gnumber = $_POST['guardian_contact'];
    $goccupation = $_POST['guardian_occupation'];
    $fathername = $_POST['father_name'];
    $fnumber = $_POST['father_contact'];
    $foccupation = $_POST['father_occupation'];
    $mothername = $_POST['mother_name'];
    $mnumber = $_POST['mother_contact'];
    $moccupation = $_POST['mother_occupation'];
    $fam_month_inc = $_POST['income'];
    $numsibling = $_POST['siblings'];
    $birthorder = $_POST['birth_order'];
    $soloparent = $_POST['solo_parent'];
    $fam_work_abroad = $_POST['abroad'];

    if ($edit_mode) {
        $update = "UPDATE family_bg SET 
            gurdianname='$gurdianname', gnumber='$gnumber', goccupation='$goccupation',
            fathername='$fathername', fnumber='$fnumber', foccupation='$foccupation',
            mothername='$mothername', mnumber='$mnumber', moccupation='$moccupation',
            fam_month_inc='$fam_month_inc', numsibling='$numsibling', birthorder='$birthorder',
            soloparent='$soloparent', fam_work_abroad='$fam_work_abroad'
            WHERE username='$user'";
        mysqli_query($conn, $update) or die(mysqli_error($conn));

        // Mark as completed in check_status
        mysqli_query($conn, "UPDATE check_status SET family_bg_completed = 1 WHERE username = '$user'");
    } else {
        $insert = "INSERT INTO family_bg (username, gurdianname, gnumber, goccupation,
            fathername, fnumber, foccupation,
            mothername, mnumber, moccupation,
            fam_month_inc, numsibling, birthorder,
            soloparent, fam_work_abroad)
            VALUES (
                '$user', '$gurdianname', '$gnumber', '$goccupation',
                '$fathername', '$fnumber', '$foccupation',
                '$mothername', '$mnumber', '$moccupation',
                '$fam_month_inc', '$numsibling', '$birthorder',
                '$soloparent', '$fam_work_abroad')";
        mysqli_query($conn, $insert) or die(mysqli_error($conn));

        // Update check_status for this form
        mysqli_query($conn, "INSERT INTO check_status (username) VALUES ('$user') ON DUPLICATE KEY UPDATE family_bg_completed = 1");

        // Include helper and check if control number needs to be created
        include_once('..\php\check_status_helper.php');
        checkAndGenerateControlNumber($conn, $user);
    }

    echo "<script>window.location='useradmission.php';</script>";
}
?>


<?php include('../php/userformheader.php'); ?>


    <div class="content">
      <div class="maincontainer">
        <div class="admission-form">
          <h2>Family Background</h2>

          <form method="POST">
            <div class="form-group">
              <div class="form-control">
                <label for="guardian_name">GUARDIAN'S NAME</label>
                <input type="text" name="guardian_name" id="guardian_name" value="<?php echo $gurdianname; ?>" placeholder="Input Text Field" required>
              </div>
              <div class="form-control">
                <label for="guardian_contact">CONTACT NUMBER</label>
                <input type="text" name="guardian_contact" id="guardian_contact" value="<?php echo $gnumber; ?>" placeholder="Input Text Field" required>
              </div>
              <div class="form-control">
                <label for="guardian_occupation">OCCUPATION</label>
                <input type="text" name="guardian_occupation" id="guardian_occupation" value="<?php echo $goccupation; ?>" placeholder="Input Text Field" required>
              </div>
            </div>

            <div class="form-group">
              <div class="form-control">
                <label for="father_name">FATHER'S NAME</label>
                <input type="text" name="father_name" id="father_name" value="<?php echo $fathername; ?>" placeholder="Input Text Field" required>
              </div>
              <div class="form-control">
                <label for="father_contact">CONTACT NUMBER</label>
                <input type="text" name="father_contact" id="father_contact" value="<?php echo $fnumber; ?>" placeholder="Input Text Field" required>
              </div>
              <div class="form-control">
                <label for="father_occupation">OCCUPATION</label>
                <input type="text" name="father_occupation" id="father_occupation" value="<?php echo $foccupation; ?>" placeholder="Input Text Field" required>
              </div>
            </div>

            <div class="form-group">
              <div class="form-control">
                <label for="mother_name">MOTHER'S NAME</label>
                <input type="text" name="mother_name" id="mother_name" value="<?php echo $mothername; ?>" placeholder="Input Text Field" required>
              </div>
              <div class="form-control">
                <label for="mother_contact">CONTACT NUMBER</label>
                <input type="text" name="mother_contact" id="mother_contact" value="<?php echo $mnumber; ?>" placeholder="Input Text Field" required>
              </div>
              <div class="form-control">
                <label for="mother_occupation">OCCUPATION</label>
                <input type="text" name="mother_occupation" id="mother_occupation" value="<?php echo $moccupation; ?>" placeholder="Input Text Field" required>
              </div>
            </div>

            <div class="form-group">
              <div class="form-control">
                <label for="income">FAMILY MONTHLY INCOME</label>
                <input type="text" name="income" id="income" value="<?php echo $fam_month_inc; ?>" placeholder="₱10000, ₱10000-₱15000, etc." required>
              </div>
              <div class="form-control">
                <label for="siblings">NUMBER OF SIBLING</label>
                <input type="text" name="siblings" id="siblings" value="<?php echo $numsibling; ?>" placeholder="Input Text Field" required>
              </div>
              <div class="form-control">
                <label for="birth_order">BIRTH ORDER</label>
                <input type="text" name="birth_order" id="birth_order" value="<?php echo $birthorder; ?>" placeholder="First, second, etc." required>
              </div>
            </div>

            <div class="form-group">
              <div class="form-control">
                <label for="solo_parent">ARE YOU A SOLO PARENT?</label>
                <select name="solo_parent" id="solo_parent">
                  <option value="">Select Item</option>
                  <option value="Yes" <?php if ($soloparent == "Yes") echo "selected"; ?>>Yes</option>
                  <option value="No" <?php if ($soloparent == "No") echo "selected"; ?>>No</option>
                </select>
              </div>
              <div class="form-control">
                <label for="abroad">FAMILY MEMBER WORKING ABROAD?</label>
                <select name="abroad" id="abroad">
                  <option value="">Select Item</option>
                  <option value="Yes" <?php if ($fam_work_abroad == "Yes") echo "selected"; ?>>Yes</option>
                  <option value="No" <?php if ($fam_work_abroad == "No") echo "selected"; ?>>No</option>
                </select>
              </div>
            </div>

            <div class="submit-section">
              <button type="submit" name="submit" class="confirm-btn">Confirm</button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>

</body>
</html>
