<?php 
require_once('../../private/config.php'); 

require_password_reset();

// Capture and clear temp password immediately
$temp_password = $_SESSION['temp_password'] ?? null;
//unset($_SESSION['temp_password']);
?>
<?php 
$id = $_GET['id'] ?? null;
if (!$id) {
    redirect_to(url_wrap('/staff/index.php'));
    exit();
}
$staff = find_staff_by_id($id);
if (!$staff) {
    $_SESSION['error'] = "Staff account not found.";
    redirect_to(url_wrap('/staff/index.php'));
    exit();
}

// Protect super_admin account: only super_admin can view
if (($staff['role'] === 'super_admin' || $staff['role_id'] == 6) && ($_SESSION['staff_role'] ?? '') !== 'super_admin') {
    $_SESSION['error'] = "Access Denied: Only Super Admin can view Super Admin accounts.";
    redirect_to(url_wrap('/staff/index.php'));
    exit();
}

$page_title = v_wrap($staff['full_name']);
$defaultStaffImage = 'default_profile_pic.png'; ?>

<?php
include(SHARED_PATH . '/header.php'); ?>

<?php if ($temp_password): ?>
<!-- Temp Password Modal - auto-opens on page load -->
<div id="tempPasswordModal" class="modal-overlay active">
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
        
        <button onclick="document.getElementById('tempPasswordModal').classList.remove('active')" style="background: #0F4E74; color: white; border: none; border-radius: 8px; padding: 12px 40px; cursor: pointer; font-size: 1rem; font-weight: 600; width: 100%;">
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
<?php
// unset the password now that we have displayed it once
unset($_SESSION['temp_password']);
endif; ?>

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
    <dt>Account Status:</dt>
    <dd>
      <span class="badge" style="<?php echo strtolower($staff['status'] ?? 'active') === 'active' ? 'background:#e8f4fd; color:#0F4E74; border:1px solid #0F4E74; padding:3px 10px; border-radius:4px; font-weight:600;' : 'background:#fce8e6; color:#d93025; border:1px solid #d93025; padding:3px 10px; border-radius:4px; font-weight:600;'; ?>">
        <?php echo ucfirst(v_wrap($staff['status'] ?? 'active')); ?>
      </span>
    </dd>
    </dl>

    <dl>
    <dt>Assigned Shift:</dt>
    <dd>
      <?php
      $shifts_def = function_exists('get_shift_definitions') ? get_shift_definitions() : [];
      $sKey = $staff['current_shift'] ?? 'Morning';
      $sMeta = $shifts_def[$sKey] ?? ['name' => $sKey, 'time' => '', 'bg' => '#f1f5f9', 'color' => '#475569'];
      ?>
      <span class="badge" style="background:<?php echo $sMeta['bg']; ?>; color:<?php echo $sMeta['color']; ?>; border:1px solid <?php echo $sMeta['color']; ?>; padding:3px 10px; border-radius:4px; font-weight:600;">
        <?php echo v_wrap($sMeta['name']); ?> <?php if(!empty($sMeta['time'])) echo '(' . v_wrap($sMeta['time']) . ')'; ?>
      </span>
    </dd>
    </dl>
    </div>  
        <?php if (!empty($staff['profile_image']) && file_exists(__DIR__ . '/images/staff_pictures/' . $staff['profile_image'])) { ?>
            <img src="<?php echo url_wrap('/staff/images/staff_pictures/' . v_wrap(ru_wrap($staff['profile_image']))); ?>" alt="" class="staff-profile-header" onerror="this.onerror=null; this.src='<?php echo url_wrap('/assets/images/' . v_wrap($defaultStaffImage)); ?>';">
        <?php } else { ?>
            <img src="<?php echo url_wrap('/assets/images/' . v_wrap($defaultStaffImage));?>" alt="" class="staff-profile-header" onerror="this.onerror=null; this.src='<?php echo url_wrap('/assets/images/' . v_wrap($defaultStaffImage)); ?>';">
        <?php } ?>
        </div>
 </main>
  
</div>




<?php include(SHARED_PATH . '/footer.php'); ?>