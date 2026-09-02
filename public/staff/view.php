<?php 
require_once('../../private/config.php'); 

require_password_reset();

// Capture and clear temp password immediately
$temp_password = $_SESSION['temp_password'] ?? null;
//unset($_SESSION['temp_password']);
?>
<?php 
$id = $_GET['id'] ?? 'This id is unavailable';
$staff = find_staff_by_id($id);
$page_title = v_wrap($staff['full_name']);
$defaultStaffImage = 'default_profile_pic.png'; ?>

<?php if ($temp_password): ?>
<!-- Temp Password Modal - auto-opens on page load -->
<div id="tempPasswordModal" class="modal-overlay" style="display:flex;">
    <div class="modal-content" style="max-width: 480px; text-align: center;">
        <div style="background: linear-gradient(135deg, #0F4E74 0%, #1a7bb5 100%); border-radius: 10px 10px 0 0; margin: -30px -30px 25px; padding: 30px;">
            <i class="bi bi-shield-lock" style="font-size: 3rem; color: white; opacity: 0.9;"></i>
            <h3 style="color: white; margin: 10px 0 0; font-weight: 400;">Staff Created Successfully!</h3>
        </div>
        
        <p style="color: #555; margin-bottom: 8px;">The temporary password for <strong><?php echo v_wrap($staff['full_name']); ?></strong> is:</p>
        
        <div style="display: flex; align-items: center; background: #f8f9fa; border: 2px dashed #0F4E74; border-radius: 8px; padding: 15px 20px; margin: 15px 0; gap: 10px;">
            <code id="tempPwDisplay" style="flex: 1; font-size: 1.2rem; font-weight: 700; color: #0F4E74; letter-spacing: 2px; word-break: break-all;"><?php echo htmlspecialchars($temp_password); ?></code>
            <button onclick="copyTempPassword()" id="copyBtn" style="background: #0F4E74; color: white; border: none; border-radius: 6px; padding: 8px 14px; cursor: pointer; font-size: 0.85rem; white-space: nowrap; transition: 0.2s;" title="Copy to clipboard">
                <i class="bi bi-clipboard" id="copyIcon"></i> Copy
            </button>
        </div>
        
        <p style="color: #e53935; font-size: 0.85rem; margin-bottom: 20px;">
            <i class="bi bi-exclamation-triangle-fill"></i> 
            Store this immediately and send it to the staff member. It will not be shown again.
        </p>
        
        <button onclick="document.getElementById('tempPasswordModal').style.display='none'" style="background: #0F4E74; color: white; border: none; border-radius: 8px; padding: 12px 40px; cursor: pointer; font-size: 1rem; font-weight: 600; width: 100%;">
            I've Saved the Password
        </button>
    </div>
</div>

<script>
function copyTempPassword() {
    const pw = document.getElementById('tempPwDisplay').innerText;
    navigator.clipboard.writeText(pw).then(() => {
        const btn = document.getElementById('copyBtn');
        const icon = document.getElementById('copyIcon');
        btn.style.background = '#28a745';
        btn.innerHTML = '<i class="bi bi-clipboard-check"></i> Copied!';
        setTimeout(() => {
            btn.style.background = '#0F4E74';
            btn.innerHTML = '<i class="bi bi-clipboard" id="copyIcon"></i> Copy';
        }, 2500);
    }).catch(() => {
        // Fallback for older browsers
        const el = document.createElement('textarea');
        el.value = pw;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        document.getElementById('copyBtn').innerHTML = '<i class="bi bi-clipboard-check"></i> Copied!';
    });
}
</script>
<?php endif; ?>
<?php
include(SHARED_PATH . '/header.php'); ?>

<div id="content">
  
  <?php include(SHARED_PATH . '/navigation.php'); ?>
    
  <main class="main-content">
      
  <div class="top">
        <p class="top-head">Account Review </p> 
    <p class="top-description">view of account details for existing health centre staff</p>
  </div>
<div><?php echo display_session_message(); ?></div>
    
 <div class="role-head"><?php echo v_wrap($staff['full_name']); echo"'s Details"; ?></div>
      
<div class="tabs" role="tablist">
  <button role="tab" class="tab-btn active" aria-selected="true" aria-controls="staff-view" data-tab="staff-view">
    Staff Account Details
      </button>
 </div>
  
    <div id="staff-view" class="tab-content active" role="tabpanel" aria-labelledby="staff-view-tab">
      <div class = "staff-name-and-picture">
    <dl>
    <dt>Full Name:</dt>
    <dd><?php echo v_wrap($staff['full_name']); ?></dd>
    </dl>
    
    <dl>
    <dt>Staff ID:</dt>
    <dd><?php echo v_wrap($staff['system_staff_id']); ?></dd>
    </dl>
    
    <dl>
    <dt>Email:</dt>
    <dd><?php echo v_wrap($staff['email']); ?></dd>
    </dl>
    <dl>
    <dt>Role:</dt>
    <dd><?php echo v_wrap($staff['role']); ?></dd>
    </dl>
    
    <dl>
    <dt>Department:</dt>
    <dd><?php echo v_wrap($staff['department']); ?></dd>
    </dl>
    
    <dl>
    <dt>Professional Headshot:</dt>
    <dd><?php echo v_wrap($staff['profile_image']); ?></dd>
    </dl>
    </div>  
        <?php if (!empty($staff['profile_image'])) { ?>
            <img src="<?php echo url_wrap('staff/images/staff_pictures/' . v_wrap(ru_wrap($staff['profile_image']))); ?>" alt="Staff profile photo" class="staff-profile-header">
        <?php } else { ?>
            <img src="<?php echo url_wrap('/assets/images/' . v_wrap($defaultStaffImage));?>" alt="No Staff photo uploaded" class="staff-profile-header">
        <?php } ?>
        </div>
 </main>
  
</div>




<?php include(SHARED_PATH . '/footer.php'); ?>