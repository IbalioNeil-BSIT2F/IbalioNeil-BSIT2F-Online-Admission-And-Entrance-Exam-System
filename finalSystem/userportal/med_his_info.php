<?php
include('..\php\connection.php');
session_start();
$user = $_SESSION['user'];

// Initialize form variables
$edit_mode = false;
$medications = '';
$conditions = [];
$allergies = '';

// Fetch existing data
$res = mysqli_query($conn, "SELECT * FROM med_his_info WHERE username = '$user'");
if (mysqli_num_rows($res) > 0) {
    $edit_mode = true;
    $row = mysqli_fetch_assoc($res);
    $medications = $row['medications'];
    $conditions = explode(",", $row['medical_conditions']); // convert string to array
    $allergies = $row['allergies'];
}

if (isset($_POST['submit'])) {
    $medications = $_POST['medications'];
    $conditions_array = isset($_POST['medical_conditions']) ? $_POST['medical_conditions'] : [];
    $conditions_str = implode(",", $conditions_array);
    $allergies = $_POST['allergies'];

    if ($edit_mode) {
        mysqli_query($conn, "UPDATE med_his_info SET medications='$medications', medical_conditions='$conditions_str', allergies='$allergies' WHERE username='$user'");
    } else {
        mysqli_query($conn, "INSERT INTO med_his_info (username, medications, medical_conditions, allergies) VALUES ('$user', '$medications', '$conditions_str', '$allergies')");
    }

    mysqli_query($conn, "INSERT INTO check_status (username) VALUES ('$user') ON DUPLICATE KEY UPDATE med_his_info_completed = 1");

    include_once('..\php\check_status_helper.php');
    checkAndGenerateControlNumber($conn, $user);

    echo "<script>window.location='useradmission.php';</script>";
}

function isChecked($conditions, $value) {
    return in_array($value, $conditions) ? 'checked' : '';
}
?>

<script>
    // Uncheck all other checkboxes if "None" is selected
    function handleCheckboxClick(clicked) {
      if (clicked.value === "None" && clicked.checked) {
        document.querySelectorAll('input[name="medical_conditions[]"]').forEach(cb => {
          if (cb !== clicked) cb.checked = false;
        });
      } else if (clicked.value !== "None") {
        document.querySelector('input[value="None"]').checked = false;
      }
    }
  </script>

<?php include('../php/userformheader.php'); ?>


    <!-- Main content container -->
    <div class="content">
      <div class="maincontainer">
        <form method="POST" class="admission-form" style="margin-top: 40px;">
          <h2>Medical History Information</h2>

          <div class="form-group">
            <div class="form-control" style="flex: 1;">
              <label for="medications">MEDICATIONS THAT ARE BEING TAKEN:</label>
              <input type="text" id="medications" name="medications" placeholder="List any medications or N/A" value="<?php echo htmlspecialchars($medications); ?>">
            </div>
          </div>

          <div class="form-group" style="flex-direction: column;">
            <label>Do you have any of the following sickness or injury?</label>
            <div class="form-control" style="flex-direction: column; gap: 10px;">
              <label><input type="checkbox" name="medical_conditions[]" value="Scoliosis" <?php echo isChecked($conditions, "Scoliosis"); ?> onclick="handleCheckboxClick(this)"> Scoliosis</label>
              <label><input type="checkbox" name="medical_conditions[]" value="Diabetes" <?php echo isChecked($conditions, "Diabetes"); ?> onclick="handleCheckboxClick(this)"> Diabetes</label>
              <label><input type="checkbox" name="medical_conditions[]" value="Asthma" <?php echo isChecked($conditions, "Asthma"); ?> onclick="handleCheckboxClick(this)"> Asthma</label>
              <label><input type="checkbox" name="medical_conditions[]" value="Heart Disease" <?php echo isChecked($conditions, "Heart Disease"); ?> onclick="handleCheckboxClick(this)"> Heart Disease</label>
              <label><input type="checkbox" name="medical_conditions[]" value="None" <?php echo isChecked($conditions, "None"); ?> onclick="handleCheckboxClick(this)"> None</label>
            </div>
          </div>

          <div class="form-group">
            <div class="form-control" style="flex: 1;">
              <label for="allergies">OTHER OR MORE SPECIFIC (I.E. KINDS OF ALLERGIES):</label>
              <input type="text" id="allergies" name="allergies" placeholder="e.g., Peanuts, Penicillin or N/A" value="<?php echo htmlspecialchars($allergies); ?>">
            </div>
          </div>

          <div class="submit-section">
            <button type="submit" name="submit" class="confirm-btn">Confirm</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</body>
</html>
