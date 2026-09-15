<?php
require_once('../../private/config.php');
require_once(PRIVATE_PATH . '/data/nigerian_lgas.php');
require_student_login();

$student = find_student_by_matric($_SESSION['student_matric']);
if (!$student) {
    redirect_to(url_wrap('/student/login.php'));
}

$states = array_keys($lgas);

if (is_post_request()) {
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $errors = [];

    if (empty($phone)) {
        $errors[] = "Primary phone number is required.";
    }

    if (!empty($email) && !has_valid_email_format($email)) {
        $errors[] = "Invalid email address format.";
    }

    if ($password !== '' && $password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    // Handle Profile Image Upload
    $new_profile_image = null;
    if (!empty($_FILES['profile_image']['name']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadResult = upload_patient_image($_FILES['profile_image']);
        if (!$uploadResult['success']) {
            $errors[] = $uploadResult['error'];
        } else {
            $new_profile_image = $uploadResult['filename'];
            if (!empty($student['profile_image']) && $student['profile_image'] !== 'default_profile_pic.png') {
                delete_patient_image($student['profile_image']);
            }
        }
    }

    if (!empty($errors)) {
        $_SESSION['error'] = implode(" ", $errors);
    } else {
        $data = [
            'first_name' => trim($_POST['first_name'] ?? $student['first_name']),
            'middle_name' => trim($_POST['middle_name'] ?? $student['middle_name']),
            'surname' => trim($_POST['surname'] ?? $student['surname']),
            'gender' => $_POST['gender'] ?? '',
            'date_of_birth' => $_POST['date_of_birth'] ?? '',
            'nationality' => $_POST['nationality'] ?? 'Nigeria',
            'state_of_origin' => $_POST['state_of_origin'] ?? '',
            'lga' => $_POST['lga'] ?? '',
            'marital_status' => $_POST['marital_status'] ?? '',
            'phone' => $phone,
            'alternate_phone' => trim($_POST['alternate_phone'] ?? ''),
            'email' => $email,
            'address' => trim($_POST['address'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'residential_state' => trim($_POST['residential_state'] ?? ''),
            'faculty' => trim($_POST['faculty'] ?? ''),
            'department' => trim($_POST['department'] ?? ''),
            'level' => trim($_POST['level'] ?? ''),
            'blood_group' => trim($_POST['blood_group'] ?? ''),
            'genotype' => trim($_POST['genotype'] ?? ''),
            'allergies' => trim($_POST['allergies'] ?? ''),
            'chronic_conditions' => trim($_POST['chronic_conditions'] ?? ''),
            'disabilities' => trim($_POST['disabilities'] ?? ''),
            'next_of_kin_name' => trim($_POST['next_of_kin_name'] ?? ''),
            'next_of_kin_relationship' => trim($_POST['next_of_kin_relationship'] ?? ''),
            'next_of_kin_phone' => trim($_POST['next_of_kin_phone'] ?? ''),
            'emergency_contact_name' => trim($_POST['emergency_contact_name'] ?? ''),
            'emergency_contact_relationship' => trim($_POST['emergency_contact_relationship'] ?? ''),
            'emergency_contact_phone' => trim($_POST['emergency_contact_phone'] ?? '')
        ];

        $update_pwd = ($password !== '') ? $password : null;
        $res = update_student_profile($student['id'], $data, $update_pwd, $new_profile_image);
        if ($res) {
            $_SESSION['message'] = "Your profile has been successfully updated!";
            unset($_SESSION['skip_profile_prompt']);
        } else {
            $_SESSION['error'] = "Failed to update profile. Please check the fields and try again.";
        }
    }
    redirect_to(url_wrap('/student/profile.php'));
}

$page_title = 'Update Profile & Clearance Details';
$defaultImage = 'default_profile_pic.png';
$primary_emergency = find_primary_emergency_contact($student['id']) ?? [];

$isIncomplete = empty($student['gender']) 
    || empty($student['date_of_birth']) 
    || empty($student['next_of_kin_name']) 
    || empty($student['department'])
    || $student['date_of_birth'] === '2000-01-01';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo v_wrap($page_title); ?> - FUTA HCMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/student_portal.css?v=<?php echo time(); ?>">
    <style>
        .section-header {
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #eef2f5;
            color: #0F4E74;
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        @media (max-width: 768px) {
            .form-row-2, .form-row-3 {
                grid-template-columns: 1fr;
            }
            .profile-hero {
                flex-direction: column;
                text-align: center;
                gap: 15px;
                padding: 15px;
            }
        }
        .profile-hero {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            background: #fdfefe;
            border: 1px solid #e1e8ed;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        .profile-avatar-large {
            width: 85px;
            height: 85px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #0F4E74;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <header class="portal-header">
        <div class="brand">
            <img src="../assets/images/futa_logo.png" width="40" height="40" alt="FUTA Logo">
            FUTA HCMS Student Portal
        </div>
        <div class="user-menu">
            <a href="dashboard.php" style="margin-right:15px;">Dashboard</a>
            <span>
                <?php if (!empty($student['profile_image'])) { ?>
                    <img src="<?php echo url_wrap('/modules/patients/images/patient_pictures/' . v_wrap($student['profile_image'])); ?>" onerror="this.onerror=null; this.src='<?php echo url_wrap('/assets/images/' . v_wrap($defaultImage)); ?>';" alt="Profile" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover; vertical-align: middle; margin-right: 5px;">
                <?php } else { ?>
                    <i class="bi bi-person-circle" style="vertical-align: middle; margin-right: 5px;"></i>
                <?php } ?>
                <?php echo v_wrap($student['first_name']); ?>
            </span>
            <a href="logout.php" class="logout-btn" style="margin-left:15px;">Logout</a>
        </div>
    </header>

    <div class="portal-container">
        <div class="dashboard-grid">
            <div class="main-column">
                <div class="card">
                    <h3><i class="bi bi-person-lines-fill"></i> My Medical Profile & Clearance Details</h3>
                    <div><?php echo display_session_message(); ?></div>

                    <div class="profile-hero">
                        <?php 
                        $avatarUrl = !empty($student['profile_image']) 
                            ? url_wrap('/modules/patients/images/patient_pictures/' . v_wrap($student['profile_image'])) 
                            : url_wrap('/assets/images/' . v_wrap($defaultImage)); 
                        ?>
                        <img src="<?php echo $avatarUrl; ?>" onerror="this.onerror=null; this.src='<?php echo url_wrap('/assets/images/' . v_wrap($defaultImage)); ?>';" class="profile-avatar-large" alt="Student Photo">
                        <div>
                            <h2 style="margin: 0 0 5px 0; color: #0F4E74; font-size: 1.4rem;">
                                <?php echo v_wrap($student['surname'] . ' ' . $student['first_name'] . ' ' . ($student['middle_name'] ?? '')); ?>
                            </h2>
                            <div style="color: #666; font-size: 0.9rem; margin-bottom: 6px;">
                                <strong>Matric No:</strong> <?php echo v_wrap($student['matric_number']); ?> &bull; 
                                <strong>Patient ID:</strong> <?php echo v_wrap($student['patient_id']); ?>
                            </div>
                            <div>
                                <?php if ($isIncomplete) { ?>
                                    <span class="badge" style="background: #fff3cd; color: #856404;"><i class="bi bi-exclamation-circle"></i> Incomplete Profile</span>
                                <?php } else { ?>
                                    <span class="badge" style="background: #d4edda; color: #155724;"><i class="bi bi-check-circle-fill"></i> Profile Completed</span>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <form action="profile.php" method="post" enctype="multipart/form-data" id="studentProfileForm">
                        
                        <!-- Upload Photo -->
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label><i class="bi bi-camera"></i> Change Profile Picture (Optional)</label>
                            <input type="file" name="profile_image" accept="image/*">
                            <small style="color:#777;">Formats supported: JPEG, PNG, WebP (Max 2MB)</small>
                        </div>

                        <!-- 1. Personal Information -->
                        <div class="section-header">
                            <i class="bi bi-person-badge"></i> 1. Personal & Bio-data
                        </div>

                        <div class="form-row-3">
                            <div class="form-group">
                                <label>Surname *</label>
                                <input type="text" name="surname" value="<?php echo v_wrap($student['surname'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>First Name *</label>
                                <input type="text" name="first_name" value="<?php echo v_wrap($student['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" name="middle_name" value="<?php echo v_wrap($student['middle_name'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-row-3">
                            <div class="form-group">
                                <label>Gender / Sex *</label>
                                <select name="gender" required>
                                    <option value="Male" <?php echo ($student['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($student['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Date of Birth *</label>
                                <input type="date" name="date_of_birth" value="<?php echo v_wrap($student['date_of_birth'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Marital Status</label>
                                <select name="marital_status">
                                    <option value="Single" <?php echo ($student['marital_status'] ?? 'Single') === 'Single' ? 'selected' : ''; ?>>Single</option>
                                    <option value="Married" <?php echo ($student['marital_status'] ?? '') === 'Married' ? 'selected' : ''; ?>>Married</option>
                                    <option value="Divorced" <?php echo ($student['marital_status'] ?? '') === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                                    <option value="Widowed" <?php echo ($student['marital_status'] ?? '') === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row-3">
                            <div class="form-group">
                                <label>Nationality</label>
                                <select name="nationality" id="nationalitySelect">
                                    <option value="Nigeria" <?php echo ($student['nationality'] ?? 'Nigeria') === 'Nigeria' ? 'selected' : ''; ?>>Nigeria</option>
                                    <option value="Ghana" <?php echo ($student['nationality'] ?? '') === 'Ghana' ? 'selected' : ''; ?>>Ghana</option>
                                    <option value="Cameroon" <?php echo ($student['nationality'] ?? '') === 'Cameroon' ? 'selected' : ''; ?>>Cameroon</option>
                                    <option value="Benin" <?php echo ($student['nationality'] ?? '') === 'Benin' ? 'selected' : ''; ?>>Benin</option>
                                    <option value="Other" <?php echo (!empty($student['nationality']) && !in_array($student['nationality'], ['Nigeria','Ghana','Cameroon','Benin'])) ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="form-group" id="stateOriginGroup">
                                <label>State of Origin</label>
                                <select name="state_of_origin" id="stateOriginSelect">
                                    <option value="">-- Select State --</option>
                                    <?php foreach($states as $st) { ?>
                                        <option value="<?php echo v_wrap($st); ?>" <?php echo ($student['state_of_origin'] ?? '') === $st ? 'selected' : ''; ?>><?php echo v_wrap($st); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group" id="lgaOriginGroup">
                                <label>LGA of Origin</label>
                                <select name="lga" id="lgaOriginSelect">
                                    <option value="">-- Select LGA --</option>
                                </select>
                            </div>
                        </div>

                        <!-- 2. Academic Information -->
                        <div class="section-header">
                            <i class="bi bi-mortarboard"></i> 2. Academic Details
                        </div>

                        <div class="form-row-3">
                            <div class="form-group">
                                <label>School / Faculty</label>
                                <input type="text" name="faculty" id="student_faculty" value="<?php echo v_wrap($student['faculty'] ?? ''); ?>" placeholder="Auto-selected from department" readonly style="background:#f8f9fa;">
                            </div>
                            <div class="form-group">
                                <label>Department *</label>
                                <select name="department" id="student_department" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                                    <?php echo render_futa_department_options($student['department'] ?? ''); ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Level</label>
                                <select name="level">
                                    <option value="">-- Select Level --</option>
                                    <option value="100" <?php echo ($student['level'] ?? '') === '100' ? 'selected' : ''; ?>>100 Level</option>
                                    <option value="200" <?php echo ($student['level'] ?? '') === '200' ? 'selected' : ''; ?>>200 Level</option>
                                    <option value="300" <?php echo ($student['level'] ?? '') === '300' ? 'selected' : ''; ?>>300 Level</option>
                                    <option value="400" <?php echo ($student['level'] ?? '') === '400' ? 'selected' : ''; ?>>400 Level</option>
                                    <option value="500" <?php echo ($student['level'] ?? '') === '500' ? 'selected' : ''; ?>>500 Level</option>
                                    <option value="600" <?php echo ($student['level'] ?? '') === '600' ? 'selected' : ''; ?>>600 Level (MBBS)</option>
                                    <option value="Postgraduate" <?php echo ($student['level'] ?? '') === 'Postgraduate' ? 'selected' : ''; ?>>Postgraduate</option>
                                </select>
                            </div>
                        </div>

                        <!-- 3. Contact & Residential Information -->
                        <div class="section-header">
                            <i class="bi bi-geo-alt"></i> 3. Contact & Address
                        </div>

                        <div class="form-row-3">
                            <div class="form-group">
                                <label>Primary Phone *</label>
                                <input type="text" name="phone" value="<?php echo v_wrap($student['phone'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Alternate Phone</label>
                                <input type="text" name="alternate_phone" value="<?php echo v_wrap($student['alternate_phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Email Address *</label>
                                <input type="email" name="email" value="<?php echo v_wrap($student['email'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="form-row-3">
                            <div class="form-group" style="grid-column: span 2;">
                                <label>Residential Address / Hostel</label>
                                <input type="text" name="address" value="<?php echo v_wrap($student['address'] ?? ''); ?>" placeholder="Hostel or off-campus address">
                            </div>
                            <div class="form-group">
                                <label>City / Town</label>
                                <input type="text" name="city" value="<?php echo v_wrap($student['city'] ?? ''); ?>" placeholder="e.g. Akure">
                            </div>
                        </div>

                        <!-- 4. Medical Information -->
                        <div class="section-header">
                            <i class="bi bi-heart-pulse"></i> 4. Medical Information
                        </div>

                        <div class="form-row-2">
                            <div class="form-group">
                                <label>Blood Group</label>
                                <select name="blood_group">
                                    <option value="">-- Select Blood Group --</option>
                                    <?php 
                                    $bgList = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                    foreach ($bgList as $bg) {
                                        $sel = ($student['blood_group'] ?? '') === $bg ? 'selected' : '';
                                        echo "<option value=\"$bg\" $sel>$bg</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Genotype</label>
                                <select name="genotype">
                                    <option value="">-- Select Genotype --</option>
                                    <?php 
                                    $gtList = ['AA', 'AS', 'SS', 'AC', 'SC'];
                                    foreach ($gtList as $gt) {
                                        $sel = ($student['genotype'] ?? '') === $gt ? 'selected' : '';
                                        echo "<option value=\"$gt\" $sel>$gt</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Known Allergies (Food, Drugs, Environmental)</label>
                            <input type="text" name="allergies" value="<?php echo v_wrap($student['allergies'] ?? ''); ?>" placeholder="e.g. Penicillin, Peanuts, None">
                        </div>

                        <div class="form-group">
                            <label>Chronic Medical Conditions (Asthma, Hypertension, Diabetes, etc.)</label>
                            <input type="text" name="chronic_conditions" value="<?php echo v_wrap($student['chronic_conditions'] ?? ''); ?>" placeholder="e.g. Asthma, Sickle Cell, None">
                        </div>

                        <div class="form-group">
                            <label>Physical Disabilities / Special Needs</label>
                            <input type="text" name="disabilities" value="<?php echo v_wrap($student['disabilities'] ?? ''); ?>" placeholder="e.g. Visual impairment, None">
                        </div>

                        <!-- 5. Next of Kin -->
                        <div class="section-header">
                            <i class="bi bi-person-heart"></i> 5. Next of Kin
                        </div>

                        <div class="form-row-3">
                            <div class="form-group">
                                <label>Next of Kin Full Name *</label>
                                <input type="text" name="next_of_kin_name" value="<?php echo v_wrap($student['next_of_kin_name'] ?? ''); ?>" required placeholder="e.g. Mr. John Doe">
                            </div>
                            <div class="form-group">
                                <label>Relationship *</label>
                                <input type="text" name="next_of_kin_relationship" value="<?php echo v_wrap($student['next_of_kin_relationship'] ?? ''); ?>" required placeholder="e.g. Father, Mother, Sibling">
                            </div>
                            <div class="form-group">
                                <label>Next of Kin Phone Number *</label>
                                <input type="text" name="next_of_kin_phone" value="<?php echo v_wrap($student['next_of_kin_phone'] ?? ''); ?>" required placeholder="e.g. 08012345678">
                            </div>
                        </div>

                        <!-- 6. Emergency Contact Information -->
                        <div class="section-header" style="justify-content: space-between;">
                            <span><i class="bi bi-telephone-inbound"></i> 6. Emergency Contact Information</span>
                            <button type="button" id="copyKinToEmergencyBtn" style="background:#e8f4fd; color:#0F4E74; border:1px solid #0F4E74; padding:5px 12px; border-radius:4px; font-size:0.8rem; cursor:pointer; font-weight:600;">
                                <i class="bi bi-copy"></i> Same as Next of Kin
                            </button>
                        </div>

                        <div class="form-row-3">
                            <div class="form-group">
                                <label>Emergency Contact Name *</label>
                                <input type="text" name="emergency_contact_name" value="<?php echo v_wrap($primary_emergency['contact_name'] ?? $student['next_of_kin_name'] ?? ''); ?>" required placeholder="e.g. Mrs. Mary Doe">
                            </div>
                            <div class="form-group">
                                <label>Relationship *</label>
                                <input type="text" name="emergency_contact_relationship" value="<?php echo v_wrap($primary_emergency['relationship'] ?? $student['next_of_kin_relationship'] ?? ''); ?>" required placeholder="e.g. Mother, Guardian">
                            </div>
                            <div class="form-group">
                                <label>Emergency Phone Number *</label>
                                <input type="text" name="emergency_contact_phone" value="<?php echo v_wrap($primary_emergency['phone'] ?? $student['next_of_kin_phone'] ?? ''); ?>" required placeholder="e.g. 08098765432">
                            </div>
                        </div>

                        <!-- 7. Password Change -->
                        <div class="section-header">
                            <i class="bi bi-shield-lock"></i> 7. Security (Change Password)
                        </div>
                        <p style="color:#777; font-size:0.9rem; margin-top:0;">Leave password fields blank if you do not wish to change your login password.</p>

                        <div class="form-row-2">
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="password" placeholder="Enter new password">
                            </div>
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" placeholder="Re-enter new password">
                            </div>
                        </div>

                        <div style="margin-top: 30px; text-align: right;">
                            <button type="submit" class="action-btn primary" style="font-size:1.05rem; padding: 14px 28px; display:inline-flex; width:auto;">
                                <i class="bi bi-check-circle"></i> Save Profile Details
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php include(SHARED_PATH . '/student_sidebar.php'); ?>

        </div>
    </div>

    <script>
    const nigeriaLGAs = <?php echo json_encode($lgas); ?>;
    const currentStudentLga = <?php echo json_encode($student['lga'] ?? ''); ?>;

    document.addEventListener("DOMContentLoaded", function() {
        const countrySelect = document.getElementById('nationalitySelect');
        const stateSelect = document.getElementById('stateOriginSelect');
        const lgaSelect = document.getElementById('lgaOriginSelect');
        const stateGroup = document.getElementById('stateOriginGroup');
        const lgaGroup = document.getElementById('lgaOriginGroup');

        function populateLGAs(stateName, selectedLga = '') {
            if (!lgaSelect) return;
            lgaSelect.innerHTML = '<option value="">-- Select LGA --</option>';
            if (stateName && nigeriaLGAs[stateName]) {
                nigeriaLGAs[stateName].forEach(function(lga) {
                    const opt = document.createElement('option');
                    opt.value = lga;
                    opt.textContent = lga;
                    if (lga === selectedLga) opt.selected = true;
                    lgaSelect.appendChild(opt);
                });
            }
        }

        function updateNationality() {
            const isNigeria = countrySelect.value === 'Nigeria';
            if (stateGroup) stateGroup.style.display = isNigeria ? 'block' : 'none';
            if (lgaGroup) lgaGroup.style.display = isNigeria ? 'block' : 'none';

            if (!isNigeria && stateSelect && lgaSelect) {
                stateSelect.value = '';
                lgaSelect.innerHTML = '<option value="">-- Select LGA --</option>';
            }
        }

        if (stateSelect) {
            stateSelect.addEventListener('change', function() {
                populateLGAs(this.value);
            });
            if (stateSelect.value) {
                populateLGAs(stateSelect.value, currentStudentLga);
            }
        }

        if (countrySelect) {
            countrySelect.addEventListener('change', updateNationality);
            updateNationality();
        }

        const copyKinBtn = document.getElementById('copyKinToEmergencyBtn');
        if (copyKinBtn) {
            copyKinBtn.addEventListener('click', function() {
                const form = document.getElementById('studentProfileForm');
                const nokName = form.querySelector('[name="next_of_kin_name"]')?.value || '';
                const nokRel = form.querySelector('[name="next_of_kin_relationship"]')?.value || '';
                const nokPhone = form.querySelector('[name="next_of_kin_phone"]')?.value || '';

                const emName = form.querySelector('[name="emergency_contact_name"]');
                const emRel = form.querySelector('[name="emergency_contact_relationship"]');
                const emPhone = form.querySelector('[name="emergency_contact_phone"]');

                if (emName) emName.value = nokName;
                if (emRel) emRel.value = nokRel;
                if (emPhone) emPhone.value = nokPhone;
            });
        }

        const deptSelect = document.getElementById('student_department');
        const facultyInput = document.getElementById('student_faculty');
        if (deptSelect && facultyInput) {
            deptSelect.addEventListener('change', function() {
                const opt = deptSelect.options[deptSelect.selectedIndex];
                const fac = opt.getAttribute('data-faculty') || '';
                if (fac) {
                    facultyInput.value = fac;
                }
            });
            if (deptSelect.value && !facultyInput.value) {
                const opt = deptSelect.options[deptSelect.selectedIndex];
                const fac = opt.getAttribute('data-faculty') || '';
                if (fac) facultyInput.value = fac;
            }
        }
    });
    </script>
    <script src="../assets/js/modal.js?v=<?php echo time(); ?>"></script>
</body>
</html>
