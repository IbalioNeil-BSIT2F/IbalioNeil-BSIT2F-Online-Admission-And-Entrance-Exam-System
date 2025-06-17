<?php
    include('..\php\connection.php');
    session_start();
?>
<?php include('../php/useradmissionheader.php'); ?>

<!-- Link the new dashboard CSS -->
<link rel="stylesheet" href="../css/prodprog.css">


<div class="content">
    <div class="maincontainer">

        <!-- Admission Steps -->
        <div class="card">
            <h2>Freshmen Admission Requirements</h2>
            <div class="card-content">
                <ol>
                    <li>Fill up the application form and upload requirements.</li>
                    <li>Validation and Evaluation of documents.</li>
                    <li>Entrance Examination.</li>
                    <li>Issuance of Notice of Admission (NOA).</li>
                    <li>Secure medical referral slip and proceed to medical exam.</li>
                    <li>Claim medical clearance.</li>
                    <li>Submit to Requirements:
                        <ul>
                            <li>Grade 12 Report Card (or Certificate of Rating for ALS)</li>
                            <li>Good Moral Certificate</li>
                            <li>Notice of Admission (NOA)</li>
                            <li>Medical Clearance</li>
                        </ul>
                    </li>
                </ol>
            </div>
        </div>

        <div class="card">
            <h2>Transferee Admission Requirements</h2>
            <div class="card-content">
                <ol>
                    <li>Fill up the application form and upload requirements.</li>
                    <li>Validation and Evaluation of documents.</li>
                    <li>Issuance of Notice of Admission (NOA).</li>
                    <li>Secure medical referral slip and proceed to medical exam.</li>
                    <li>Claim medical clearance.</li>
                    <li>Submit to Requirements:
                        <ul>
                            <li>Transcript of Records</li>
                            <li>Good Moral Certificate</li>
                            <li>Honorable Dismissal</li>
                            <li>Notice of Admission (NOA)</li>
                            <li>Medical Clearance</li>
                            <li>NBI or Police Clearance</li>
                        </ul>
                    </li>
                </ol>
            </div>
        </div>

        <div class="card">
            <h2>Second Courser Admission Requirements</h2>
            <div class="card-content">
                <ol>
                    <li>Fill up the application form and upload requirements.</li>
                    <li>Validation and Evaluation of documents.</li>
                    <li>Issuance of Notice of Admission (NOA).</li>
                    <li>Secure medical referral slip and proceed to medical exam.</li>
                    <li>Claim medical clearance.</li>
                    <li>Submit to Requirements:
                        <ul>
                            <li>Transcript of Records</li>
                            <li>Notice of Admission (NOA)</li>
                            <li>Medical Clearance</li>
                        </ul>
                    </li>
                </ol>
            </div>
        </div>

        <!-- Programs Section -->
        <div class="card">
            <h2>Program Offerings</h2>
            <div class="card-content">
                <div class="program-grid">
                    <div class="program-box">
                        <strong>BSBM - Bachelor of Science in Business Management</strong>
                        <p>Prepares students for leadership roles in business, finance, and entrepreneurship. Aligned Strand: ABM</p>
                    </div>
                    <div class="program-box">
                        <strong>BSCS - Bachelor of Science in Computer Science</strong>
                        <p>Focuses on algorithms, programming, and computing theory. Aligned Strand: STEM</p>
                    </div>
                    <div class="program-box">
                        <strong>BSCE - Bachelor of Science in Civil Engineering</strong>
                        <p>Covers structural design, construction, and infrastructure systems. Aligned Strand: STEM</p>
                    </div>
                    <div class="program-box">
                        <strong>BSIT - Bachelor of Science in Information Technology</strong>
                        <p>Emphasizes software development, networking, and IT systems management. Aligned Strand: ICT or STEM</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Collapse logic -->
<script>
document.querySelectorAll('.card h2').forEach(header => {
    header.addEventListener('click', () => {
        const card = header.parentElement;
        card.classList.toggle('active');
    });
});
</script>

</body>
</html>
