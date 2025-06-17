<?php
include('..\php\connection.php');
session_start();

$user = $_SESSION['user'];
$msg = '';
$edit_mode = false;

// Initialize variables
$elemname = $elemaddress = $elemyear = $elemtype = '';
$midname = $midaddress = $midyear = $midtype = '';
$seniorname = $senioraddress = $senioryear = $seniortype = '';
$vocname = $vocaddress = $vocyear = $voctype = '';

// Check for existing entry
$res = mysqli_query($conn, "SELECT * FROM education_bg WHERE username = '$user'");
if (mysqli_num_rows($res) > 0) {
    $edit_mode = true;
    $row = mysqli_fetch_assoc($res);
    extract($row);
}

if (isset($_POST['submit'])) {
    $elemname = $_POST['elemname'];
    $elemaddress = $_POST['elemaddress'];
    $elemyear = $_POST['elemyear'];
    $elemtype = $_POST['elemtype'];
    $midname = $_POST['midname'];
    $midaddress = $_POST['midaddress'];
    $midyear = $_POST['midyear'];
    $midtype = $_POST['midtype'];
    $seniorname = $_POST['seniorname'];
    $senioraddress = $_POST['senioraddress'];
    $senioryear = $_POST['senioryear'];
    $seniortype = $_POST['seniortype'];
    $vocname = $_POST['vocname'];
    $vocaddress = $_POST['vocaddress'];
    $vocyear = $_POST['vocyear'];
    $voctype = $_POST['voctype'];

    if ($edit_mode) {
        $stmt = $conn->prepare("UPDATE education_bg SET 
            elemname=?, elemaddress=?, elemyear=?, elemtype=?,
            midname=?, midaddress=?, midyear=?, midtype=?,
            seniorname=?, senioraddress=?, senioryear=?, seniortype=?,
            vocname=?, vocaddress=?, vocyear=?, voctype=?
            WHERE username=?");
        $stmt->bind_param("sssssssssssssssss", 
            $elemname, $elemaddress, $elemyear, $elemtype,
            $midname, $midaddress, $midyear, $midtype,
            $seniorname, $senioraddress, $senioryear, $seniortype,
            $vocname, $vocaddress, $vocyear, $voctype,
            $user
        );
        $stmt->execute();
        $msg = "Updated successfully";
    } else {
        $stmt = $conn->prepare("INSERT INTO education_bg (
            username, elemname, elemaddress, elemyear, elemtype,
            midname, midaddress, midyear, midtype,
            seniorname, senioraddress, senioryear, seniortype,
            vocname, vocaddress, vocyear, voctype
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssssssssss",
            $user,
            $elemname, $elemaddress, $elemyear, $elemtype,
            $midname, $midaddress, $midyear, $midtype,
            $seniorname, $senioraddress, $senioryear, $seniortype,
            $vocname, $vocaddress, $vocyear, $voctype
        );
        $stmt->execute();
        $msg = "Submitted successfully";
    }

    // ✅ Mark education_bg as completed in check_status
    mysqli_query($conn, "INSERT INTO check_status (username) VALUES ('$user') 
        ON DUPLICATE KEY UPDATE education_bg_completed = 1");

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
                <h2>Educational Background</h2>
                <form method="POST">

                <!-- Elementary -->
                <div class="form-group">
                    <div class="form-control">
                    <label for="elem_name">ELEMENTARY SCHOOL NAME</label>
                    <input type="text" name="elemname" id="elem_name" value="<?php echo $elemname; ?>" placeholder="Input Text Field" required>
                    </div>
                    <div class="form-control">
                    <label for="elem_address">SCHOOL ADDRESS</label>
                    <input type="text" name="elemaddress" id="elem_address" value="<?php echo $elemaddress; ?>" placeholder="Input Text Field" required>
                    </div>
                    <div class="form-control">
                    <label for="elem_year">YEAR GRADUATED</label>
                    <input type="number" name="elemyear" value="<?php echo $elemyear; ?>" min="1900" max="2099" step="1" placeholder="Year" required/>
                    </div>
                    <div class="form-control">
                    <label for="elem_type">TYPE</label>
                    <select name="elemtype" id="elem_type" required>
                        <option <?php if ($elemtype == '') echo 'selected'; ?>>Select Item</option>
                        <option <?php if ($elemtype == 'Public') echo 'selected'; ?>>Public</option>
                        <option <?php if ($elemtype == 'Private') echo 'selected'; ?>>Private</option>
                    </select>
                    </div>
                </div>

                <!-- Middle School -->
                <div class="form-group">
                    <div class="form-control">
                    <label for="middle_name">MIDDLE SCHOOL NAME</label>
                    <input type="text" name="midname" id="middle_name" value="<?php echo $midname; ?>" placeholder="Input Text Field" required>
                    </div>
                    <div class="form-control">
                    <label for="middle_address">SCHOOL ADDRESS</label>
                    <input type="text" name="midaddress" id="middle_address" value="<?php echo $midaddress; ?>" placeholder="Input Text Field" required>
                    </div>
                    <div class="form-control">
                    <label for="middle_year">YEAR GRADUATED</label>
                    <input type="number" name="midyear" value="<?php echo $midyear; ?>" min="1900" max="2099" step="1" placeholder="Year" required/>
                    </div>
                    <div class="form-control">
                    <label for="mid_type">TYPE</label>
                    <select name="midtype" id="mid_type" required>
                        <option <?php if ($midtype == '') echo 'selected'; ?>>Select Item</option>
                        <option <?php if ($midtype == 'Public') echo 'selected'; ?>>Public</option>
                        <option <?php if ($midtype == 'Private') echo 'selected'; ?>>Private</option>
                    </select>
                    </div>
                </div>

                <!-- Senior High -->
                <div class="form-group">
                    <div class="form-control">
                    <label for="shs_name">SENIOR-HIGH SCHOOL NAME</label>
                    <input type="text" name="seniorname" id="shs_name" value="<?php echo $seniorname; ?>" placeholder="Input Text Field" required>
                    </div>
                    <div class="form-control">
                    <label for="shs_address">SCHOOL ADDRESS</label>
                    <input type="text" name="senioraddress" id="shs_address" value="<?php echo $senioraddress; ?>" placeholder="Input Text Field" required>
                    </div>
                    <div class="form-control">
                    <label for="shs_year">YEAR GRADUATED</label>
                    <input type="number" name="senioryear" value="<?php echo $senioryear; ?>" min="1900" max="2099" step="1" placeholder="Year" required/>
                    </div>
                    <div class="form-control">
                    <label for="senior_type">TYPE</label>
                    <select name="seniortype" id="senior_type" required>
                        <option <?php if ($seniortype == '') echo 'selected'; ?>>Select Item</option>
                        <option <?php if ($seniortype == 'Public') echo 'selected'; ?>>Public</option>
                        <option <?php if ($seniortype == 'Private') echo 'selected'; ?>>Private</option>
                    </select>
                    </div>
                </div>

                <!-- Optional: Vocational -->
                <div class="divider"></div>
                <h3 style="margin-top: -10px;">Optional</h3>

                <div class="form-group">
                    <div class="form-control">
                    <label for="vocational_name">VOCATIONAL</label>
                    <input type="text" name="vocname" id="vocational_name" value="<?php echo $vocname; ?>" placeholder="Input Text Field">
                    </div>
                    <div class="form-control">
                    <label for="vocational_address">SCHOOL ADDRESS</label>
                    <input type="text" name="vocaddress" id="vocational_address" value="<?php echo $vocaddress; ?>" placeholder="Input Text Field">
                    </div>
                    <div class="form-control">
                    <label for="vocational_year">YEAR GRADUATED</label>
                    <input type="number" name="vocyear" value="<?php echo $vocyear; ?>" min="1900" max="2099" step="1" placeholder="Year"/>
                    </div>
                    <div class="form-control">
                    <label for="voc_type">TYPE</label>
                    <select name="voctype" id="voc_type">
                        <option <?php if ($voctype == '') echo 'selected'; ?>>Select Item</option>
                        <option <?php if ($voctype == 'Public') echo 'selected'; ?>>Public</option>
                        <option <?php if ($voctype == 'Private') echo 'selected'; ?>>Private</option>
                    </select>
                    </div>
                </div>

                <!-- Submit -->
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