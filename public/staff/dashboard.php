<?php 
require_once('../../private/config.php'); 

require_password_reset();

?>


<?php 
$page_title = 'Dashboard';

$staff = find_staff_by_id($_SESSION['staff_id']);
$staff_type = ucfirst($staff['role']);
  
include(SHARED_PATH . '/header.php'); ?>

<div id="content">
  
  <?php include(SHARED_PATH . '/navigation.php'); ?>
  
  <main class="main-content">
    
    <div class="top">
    <p class="top-head">My Dashboard </p> 
    <p class="top-description">profile info, staff management and more</p>
  </div>

  <div class="role-head"><?php echo v_wrap($staff_type); ?>'s Portal</div>
  
  <div style="border-bottom: 2px solid #0F4E74; padding-bottom: 5px; margin-bottom: 20px;">
    <h3 style="color: #0F4E74; margin: 0;">Staff Account Details</h3>
  </div>
  
  <div class="dashboard-details-card" style="background: white; border-radius: 12px; padding: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; position: relative;">
    
    <!-- Left Column: Details -->
    <div style="flex: 1;">
      <table style="width: 100%; border-collapse: separate; border-spacing: 0 20px;">
        <tr>
          <td style="font-weight: 600; color: #555; width: 30%;">Full Name:</td>
          <td style="color: #777;"><?php echo v_wrap($staff['full_name']); ?></td>
        </tr>
        <tr>
          <td style="font-weight: 600; color: #555;">Staff ID:</td>
          <td style="color: #777;"><?php echo v_wrap($staff['system_staff_id']); ?></td>
        </tr>
        <tr>
          <td style="font-weight: 600; color: #555;">Email:</td>
          <td style="color: #777;"><?php echo v_wrap($staff['email']); ?></td>
        </tr>
        <tr>
          <td style="font-weight: 600; color: #555;">Role:</td>
          <td style="color: #777;"><?php echo v_wrap($staff_type); ?></td>
        </tr>
        <tr>
          <td style="font-weight: 600; color: #555;">Department:</td>
          <td style="color: #777;"><?php echo v_wrap($staff['department']); ?></td>
        </tr>
        <tr>
          <td style="font-weight: 600; color: #555;">Professional Headshot:</td>
          <td style="color: #777;"><?php echo !empty($staff['profile_image']) ? v_wrap($staff['profile_image']) : 'No photo uploaded'; ?></td>
        </tr>
        <tr>
          <td style="font-weight: 600; color: #555;">Status:</td>
          <td style="color: #777;"><?php echo ucfirst(v_wrap($staff['status'])); ?></td>
        </tr>
      </table>
      
      <button data-modal-target="editProfileModal" style="margin-top: 20px; background: #0F4E74; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: 600;">
        <i class="bi bi-pencil-square"></i> Edit Profile
      </button>
    </div>
    
    <!-- Right Column: Profile Picture -->
    <div style="flex: 1; display: flex; justify-content: center; align-items: center;">
      <?php if (!empty($staff['profile_image']) && file_exists(__DIR__ . '/images/staff_pictures/' . $staff['profile_image'])) { ?>
        <img src="<?php echo url_wrap('/staff/images/staff_pictures/' . v_wrap($staff['profile_image'])); ?>" alt="Profile Picture" style="width: 250px; height: 250px; border-radius: 50%; object-fit: cover; border: 5px solid white; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
      <?php } else { ?>
        <img src="<?php echo url_wrap('/assets/images/default_profile_pic.png'); ?>" alt="Default Profile Picture" style="width: 250px; height: 250px; border-radius: 50%; object-fit: cover; border: 5px solid white; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
      <?php } ?>
    </div>
    
  </div>

  <!-- Edit Profile Modal -->
  <div id="editProfileModal" class="modal-overlay">
      <div class="modal-content">
          <button class="modal-close" data-modal-close>&times;</button>
          <h3 class="modal-title"><i class="bi bi-person-lines-fill"></i> Edit Profile</h3>
          
          <form action="<?php echo url_wrap('/staff/edit_processor.php'); ?>" method="post" enctype="multipart/form-data">
              <div class="form-group" style="margin-bottom: 15px;">
                  <label>Full Name *</label>
                  <input type="text" name="full_name" value="<?php echo v_wrap($staff['full_name']); ?>" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
              </div>
              
              <div class="form-group" style="margin-bottom: 15px;">
                  <label>Email *</label>
                  <input type="email" name="email" value="<?php echo v_wrap($staff['email']); ?>" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
              </div>
              
              <div class="form-group" style="margin-bottom: 15px;">
                  <label>Department *</label>
                  <input type="text" name="department" value="<?php echo v_wrap($staff['department']); ?>" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
              </div>
              
              <div class="form-group" style="margin-bottom: 15px;">
                  <label>Update Professional Headshot (Optional)</label>
                  <input type="file" name="profile_image" accept="image/*" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
              </div>
              
              <button type="submit" class="btn btn-primary" style="margin-top:15px; background:#0F4E74; color:white; border:none; padding:10px; border-radius:5px; width: 100%;">Save Changes</button>
          </form>
      </div>
  </div>

  <?php
  $role = $_SESSION['staff_role'] ?? '';
  ?>

  <!-- Role-Based Quick Access Panel -->
  <div style="margin-top: 30px; border-top: 2px solid #eee; padding-top: 25px;">
    <h3 style="color: #0F4E74; margin-bottom: 20px; font-weight: 500;">
      <i class="bi bi-lightning-charge-fill" style="color: #f0a500;"></i> Quick Access
    </h3>

    <?php if($role === 'receptionist'): ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
      <a href="<?php echo url_wrap('/modules/patients/index.php'); ?>" style="text-decoration:none; background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #eee; transition: 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
        <i class="bi bi-people" style="font-size: 2rem; color: #0F4E74;"></i>
        <p style="margin: 8px 0 0; font-weight: 600; color: #333;">Patients</p>
        <p style="margin: 4px 0 0; font-size: 0.8rem; color: #888;">View all patients</p>
      </a>
      <a href="<?php echo url_wrap('/modules/reception/check_in.php'); ?>" style="text-decoration:none; background: #0F4E74; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 15px rgba(15,78,116,0.3); transition: 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
        <i class="bi bi-person-bounding-box" style="font-size: 2rem; color: white;"></i>
        <p style="margin: 8px 0 0; font-weight: 600; color: white;">Check-In</p>
        <p style="margin: 4px 0 0; font-size: 0.8rem; color: rgba(255,255,255,0.8);">Check in a patient</p>
      </a>
    </div>

    <?php elseif($role === 'nurse'): ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
      <a href="<?php echo url_wrap('/modules/nursing/index.php'); ?>" style="text-decoration:none; background: #0F4E74; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 15px rgba(15,78,116,0.3); transition: 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
        <i class="bi bi-clipboard2-pulse" style="font-size: 2rem; color: white;"></i>
        <p style="margin: 8px 0 0; font-weight: 600; color: white;">Nursing Station</p>
        <p style="margin: 4px 0 0; font-size: 0.8rem; color: rgba(255,255,255,0.8);">Active patients</p>
      </a>
      <a href="<?php echo url_wrap('/modules/encounters/index.php'); ?>" style="text-decoration:none; background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #eee; transition: 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
        <i class="bi bi-heart-pulse" style="font-size: 2rem; color: #0F4E74;"></i>
        <p style="margin: 8px 0 0; font-weight: 600; color: #333;">Encounters</p>
        <p style="margin: 4px 0 0; font-size: 0.8rem; color: #888;">View active encounters</p>
      </a>
      <a href="<?php echo url_wrap('/modules/patients/index.php'); ?>" style="text-decoration:none; background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #eee; transition: 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
        <i class="bi bi-people" style="font-size: 2rem; color: #0F4E74;"></i>
        <p style="margin: 8px 0 0; font-weight: 600; color: #333;">All Patients</p>
        <p style="margin: 4px 0 0; font-size: 0.8rem; color: #888;">Patient directory</p>
      </a>
    </div>

    <?php elseif($role === 'doctor'): ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
      <a href="<?php echo url_wrap('/modules/encounters/index.php'); ?>" style="text-decoration:none; background: #0F4E74; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 15px rgba(15,78,116,0.3); transition: 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
        <i class="bi bi-heart-pulse" style="font-size: 2rem; color: white;"></i>
        <p style="margin: 8px 0 0; font-weight: 600; color: white;">My Encounters</p>
        <p style="margin: 4px 0 0; font-size: 0.8rem; color: rgba(255,255,255,0.8);">Active consultations</p>
      </a>
      <a href="<?php echo url_wrap('/modules/patients/index.php'); ?>" style="text-decoration:none; background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #eee; transition: 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
        <i class="bi bi-people" style="font-size: 2rem; color: #0F4E74;"></i>
        <p style="margin: 8px 0 0; font-weight: 600; color: #333;">Patient Records</p>
        <p style="margin: 4px 0 0; font-size: 0.8rem; color: #888;">Search & view patients</p>
      </a>
      <a href="<?php echo url_wrap('/modules/pharmacy/index.php'); ?>" style="text-decoration:none; background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #eee; transition: 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
        <i class="bi bi-capsule" style="font-size: 2rem; color: #0F4E74;"></i>
        <p style="margin: 8px 0 0; font-weight: 600; color: #333;">Prescriptions</p>
        <p style="margin: 4px 0 0; font-size: 0.8rem; color: #888;">Issue prescriptions</p>
      </a>
    </div>

    <?php elseif($role === 'pharmacist'): ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
      <a href="<?php echo url_wrap('/modules/pharmacy/index.php'); ?>" style="text-decoration:none; background: #0F4E74; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 15px rgba(15,78,116,0.3); transition: 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
        <i class="bi bi-capsule" style="font-size: 2rem; color: white;"></i>
        <p style="margin: 8px 0 0; font-weight: 600; color: white;">Prescriptions Queue</p>
        <p style="margin: 4px 0 0; font-size: 0.8rem; color: rgba(255,255,255,0.8);">Pending dispensing</p>
      </a>
      <a href="<?php echo url_wrap('/modules/pharmacy/inventory.php'); ?>" style="text-decoration:none; background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #eee; transition: 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
        <i class="bi bi-box-seam" style="font-size: 2rem; color: #0F4E74;"></i>
        <p style="margin: 8px 0 0; font-weight: 600; color: #333;">Drug Inventory</p>
        <p style="margin: 4px 0 0; font-size: 0.8rem; color: #888;">Stock levels</p>
      </a>
      <a href="<?php echo url_wrap('/modules/pharmacy/dispense.php'); ?>" style="text-decoration:none; background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #eee; transition: 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
        <i class="bi bi-bag-check" style="font-size: 2rem; color: #0F4E74;"></i>
        <p style="margin: 8px 0 0; font-weight: 600; color: #333;">Dispense</p>
        <p style="margin: 4px 0 0; font-size: 0.8rem; color: #888;">Dispense medication</p>
      </a>
    </div>

    <?php elseif(in_array($role, ['admin', 'super_admin'])): ?>
    <?php
    $total_staff_q = $db_1->query("SELECT COUNT(*) as cnt FROM staff");
    $total_staff = $total_staff_q->fetch_assoc()['cnt'];
    $total_patients_q = $db_1->query("SELECT COUNT(*) as cnt FROM patients");
    $total_patients = $total_patients_q->fetch_assoc()['cnt'];
    $total_logs_q = $db_1->query("SELECT COUNT(*) as cnt FROM audit_logs WHERE DATE(created_at) = CURDATE()");
    $total_logs = $total_logs_q->fetch_assoc()['cnt'];
    ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 15px; margin-bottom: 20px;">
      <div style="background: #0F4E74; border-radius: 12px; padding: 20px; text-align: center; color: white; box-shadow: 0 4px 15px rgba(15,78,116,0.3);">
        <i class="bi bi-person-badge" style="font-size: 2rem; opacity:0.9;"></i>
        <h3 style="margin: 8px 0 4px; font-size: 2rem; font-weight: 300;"><?php echo $total_staff; ?></h3>
        <p style="margin: 0; font-size: 0.8rem; opacity: 0.8;">Total Staff</p>
      </div>
      <div style="background: #1570A6; border-radius: 12px; padding: 20px; text-align: center; color: white; box-shadow: 0 4px 15px rgba(21,112,166,0.3);">
        <i class="bi bi-people" style="font-size: 2rem; opacity:0.9;"></i>
        <h3 style="margin: 8px 0 4px; font-size: 2rem; font-weight: 300;"><?php echo $total_patients; ?></h3>
        <p style="margin: 0; font-size: 0.8rem; opacity: 0.8;">Total Patients</p>
      </div>
      <div style="background: white; border-radius: 12px; padding: 20px; text-align: center; border: 1px solid #eee; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <i class="bi bi-journal-text" style="font-size: 2rem; color: #0F4E74;"></i>
        <h3 style="margin: 8px 0 4px; font-size: 2rem; font-weight: 300; color: #0F4E74;"><?php echo $total_logs; ?></h3>
        <p style="margin: 0; font-size: 0.8rem; color: #888;">Today's Activity</p>
      </div>
    </div>
    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
      <a href="<?php echo url_wrap('/staff/index.php'); ?>" style="text-decoration:none; background: white; border-radius: 10px; padding: 15px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #eee; display: flex; align-items: center; gap: 10px; color: #333; transition: 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
        <i class="bi bi-person-plus" style="font-size: 1.4rem; color: #0F4E74;"></i> <span style="font-weight:600;">Manage Staff</span>
      </a>
      <a href="<?php echo url_wrap('/modules/patients/index.php'); ?>" style="text-decoration:none; background: white; border-radius: 10px; padding: 15px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #eee; display: flex; align-items: center; gap: 10px; color: #333; transition: 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
        <i class="bi bi-people" style="font-size: 1.4rem; color: #0F4E74;"></i> <span style="font-weight:600;">Patient Records</span>
      </a>
      <a href="<?php echo url_wrap('/staff/admin/activity_logs.php'); ?>" style="text-decoration:none; background: white; border-radius: 10px; padding: 15px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #eee; display: flex; align-items: center; gap: 10px; color: #333; transition: 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
        <i class="bi bi-journal-check" style="font-size: 1.4rem; color: #0F4E74;"></i> <span style="font-weight:600;">Activity Logs</span>
      </a>
    </div>
    <?php endif; ?>
  </div>

  </main>
  
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>

