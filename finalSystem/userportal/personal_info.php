<?php
include('..\php\connection.php');
session_start();

$user = $_SESSION['user']; // assuming username is stored in session

$msg = '';
$edit_mode = false;

// Initialize variables
$firstname = $middlename = $lastname = $region = $province = $town = '';
$phonenumber = $civilstatus = $sex = $birthday = $birthplace = $religion = '';

// Check if the user already has personal info
$res = mysqli_query($conn, "SELECT * FROM personal_info WHERE username = '$user'");
if (mysqli_num_rows($res) > 0) {
    $edit_mode = true;
    $row = mysqli_fetch_assoc($res);
    $firstname = $row['firstname'];
    $middlename = $row['middlename'];
    $lastname = $row['lastname'];
    $region = $row['region'];
    $province = $row['province'];
    $town = $row['town'];
    $phonenumber = $row['phonenumber'];
    $civilstatus = $row['civilstatus'];
    $sex = $row['sex'];
    $birthday = $row['birthday'];
    $birthplace = $row['birthplace'];
    $religion = $row['religion'];
}

if (isset($_POST['submit'])) {
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $lastname = $_POST['lastname'];
    $region = $_POST['region'];
    $province = $_POST['province'];
    $town = $_POST['town'];
    $phonenumber = $_POST['phone'];
    $civilstatus = $_POST['civilstatus'];
    $sex = $_POST['sex'];
    $birthday = $_POST['birthday'];
    $birthplace = $_POST['birthplace'];
    $religion = $_POST['religion'];

    if ($edit_mode) {
        // Update existing record
        $update = "UPDATE personal_info SET 
                   firstname='$firstname', middlename='$middlename', lastname='$lastname',
                   region='$region', province='$province', town='$town', phonenumber='$phonenumber',
                   civilstatus='$civilstatus', sex='$sex', birthday='$birthday', birthplace='$birthplace',
                   religion='$religion' 
                   WHERE username='$user'";
        mysqli_query($conn, $update) or die(mysqli_error($conn));
        $msg = "Updated successfully";
    } else {
        // Insert new record
        $insert = "INSERT INTO personal_info 
                  (username, firstname, middlename, lastname, region, province, town, phonenumber, civilstatus, sex, birthday, birthplace, religion)
                   VALUES 
                  ('$user', '$firstname', '$middlename', '$lastname', '$region', '$province', '$town', '$phonenumber', '$civilstatus', '$sex', '$birthday', '$birthplace', '$religion')";
        mysqli_query($conn, $insert) or die(mysqli_error($conn));
        $msg = "Submitted successfully";
    }

    // ✅ Update or insert into check_status
    $check_status_sql = "INSERT INTO check_status (username, personal_info_completed)
                         VALUES ('$user', 1)
                         ON DUPLICATE KEY UPDATE personal_info_completed = 1";
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
                <h2>Personal Information</h2>
                <form method="POST" action="">
                    <div class="form-group">
                    <div class="form-control">
                        <label for="firstname">FIRSTNAME</label>
                        <input type="text" id="firstname" name="firstname" value="<?php echo $firstname; ?>" placeholder="Input Text Field" required>
                    </div>
                    <div class="form-control">
                        <label for="middlename">MIDDLENAME</label>
                        <input type="text" id="middlename" name="middlename" value="<?php echo $middlename; ?>" placeholder="Input Text Field" required>
                    </div>
                    <div class="form-control">
                        <label for="lastname">LASTNAME</label>
                        <input type="text" id="lastname" name="lastname" value="<?php echo $lastname; ?>" placeholder="Input Text Field" required>
                    </div>
                    </div>

                    <div class="form-group">
                    <div class="form-control">
                        <label for="region">REGION</label>
                        <select id="region" name="region" required>
                        <option value="">Select Item</option>
                        <option value="NCR" <?php if($region == "NCR") echo "selected"; ?>>National Capital Region (NCR)</option>
                        <option value="Region IV-A" <?php if($region == "Region IV-A") echo "selected"; ?>>CALABARZON (Region IV-A)</option>
                        <option value="Region III" <?php if($region == "Region III") echo "selected"; ?>>Central Luzon (Region III)</option>
                        <option value="Region VII" <?php if($region == "Region VII") echo "selected"; ?>>Central Visayas (Region VII)</option>
                        <option value="Region XI" <?php if($region == "Region XI") echo "selected"; ?>>Davao Region (Region XI)</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label for="province">PROVINCE</label>
                        <select id="province" name="province" required>
                        <option value="">Select Item</option>
                        <option value="Metro Manila" <?php if($province == "Metro Manila") echo "selected"; ?>>Metro Manila</option>
                        <option value="Cavite" <?php if($province == "Cavite") echo "selected"; ?>>Cavite</option>
                        <option value="Laguna" <?php if($province == "Laguna") echo "selected"; ?>>Laguna</option>
                        <option value="Bulacan" <?php if($province == "Bulacan") echo "selected"; ?>>Bulacan</option>
                        <option value="Cebu" <?php if($province == "Cebu") echo "selected"; ?>>Cebu</option>
                        <option value="Davao del Sur" <?php if($province == "Davao del Sur") echo "selected"; ?>>Davao del Sur</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label for="town">TOWN</label>
                        <select id="town" name="town" required>
                        <option value="">Select Item</option>
                        <option value="Quezon City" <?php if($town == "Quezon City") echo "selected"; ?>>Quezon City</option>
                        <option value="Dasmariñas" <?php if($town == "Dasmariñas") echo "selected"; ?>>Dasmariñas</option>
                        <option value="Calamba" <?php if($town == "Calamba") echo "selected"; ?>>Calamba</option>
                        <option value="Malolos" <?php if($town == "Malolos") echo "selected"; ?>>Malolos</option>
                        <option value="Cebu City" <?php if($town == "Cebu City") echo "selected"; ?>>Cebu City</option>
                        <option value="Davao City" <?php if($town == "Davao City") echo "selected"; ?>>Davao City</option>
                        </select>
                    </div>
                    </div>

                    <div class="form-group">
                    <div class="form-control">
                        <label for="phone">PHONENUMBER</label>
                        <input type="text" id="phone" name="phone" value="<?php echo $phonenumber; ?>" placeholder="Input Text Field" required>
                    </div>
                    <div class="form-control">
                        <label for="civilstatus">Civil Status</label>
                        <select id="civilstatus" name="civilstatus" required>
                        <option value="">Select Item</option>
                        <option value="Single" <?php if($civilstatus == "Single") echo "selected"; ?>>Single</option>
                        <option value="Married" <?php if($civilstatus == "Married") echo "selected"; ?>>Married</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <label for="sex">SEX</label>
                        <select id="sex" name="sex" required>
                        <option value="">Select Item</option>
                        <option value="Male" <?php if($sex == "Male") echo "selected"; ?>>Male</option>
                        <option value="Female" <?php if($sex == "Female") echo "selected"; ?>>Female</option>
                        </select>
                    </div>
                    </div>

                    <div class="form-group">
                    <div class="form-control">
                        <label for="birthday">BIRTHDAY</label>
                        <input type="date" id="birthday" name="birthday" value="<?php echo $birthday; ?>" required>
                    </div>
                    <div class="form-control">
                        <label for="birthplace">BIRTHPLACE</label>
                        <input type="text" id="birthplace" name="birthplace" value="<?php echo $birthplace; ?>" placeholder="Input Text Field" required>
                    </div>
                    <div class="form-control">
                        <label for="religion">RELIGION</label>
                        <input type="text" id="religion" name="religion" value="<?php echo $religion; ?>" placeholder="Input Text Field" required>
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

  </div>

</body>
</html>