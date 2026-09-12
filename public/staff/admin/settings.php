<?php 
require_once('../../../private/config.php'); 
require_password_reset();

$role = $_SESSION['staff_role'] ?? '';
if (!in_array($role, ['admin', 'super_admin'])) {
    set_session_message("Access denied. System settings is restricted to administrators.", "error");
    redirect_to(url_wrap('/staff/dashboard.php'));
}

$settings = get_settings();
$errors = [];

if (is_post_request()) {
    $hospital_name = trim($_POST['hospital_name'] ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');
    $contact_phone = trim($_POST['contact_phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $consultation_hours = trim($_POST['consultation_hours'] ?? '');
    $system_notice = trim($_POST['system_notice'] ?? '');

    if (empty($hospital_name)) {
        $errors[] = "Hospital / System Name cannot be blank.";
    }

    if (!empty($contact_email) && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid contact email address.";
    }

    // Handle Logo Upload
    $uploaded_logo = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['logo']['tmp_name'];
        $file_name = $_FILES['logo']['name'];
        $file_size = $_FILES['logo']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_exts = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if (!in_array($file_ext, $allowed_exts)) {
            $errors[] = "Invalid logo file type. Allowed formats: PNG, JPG, JPEG, WEBP, SVG.";
        } elseif ($file_size > $max_size) {
            $errors[] = "Logo file size exceeds the 2MB limit.";
        } else {
            $new_logo_name = 'logo_' . time() . '.' . $file_ext;
            $destination = PROJECT_PATH . '/public/assets/images/' . $new_logo_name;

            if (move_uploaded_file($file_tmp, $destination)) {
                $uploaded_logo = $new_logo_name;
            } else {
                $errors[] = "Failed to upload new logo. Please check server directory permissions.";
            }
        }
    }

    if (empty($errors)) {
        if ($uploaded_logo) {
            $sql = "UPDATE settings SET hospital_name = ?, contact_email = ?, contact_phone = ?, address = ?, consultation_hours = ?, system_notice = ?, logo = ? WHERE id = ?";
            $stmt = $db_1->prepare($sql);
            $stmt->bind_param("sssssssi", $hospital_name, $contact_email, $contact_phone, $address, $consultation_hours, $system_notice, $uploaded_logo, $settings['id']);
        } else {
            $sql = "UPDATE settings SET hospital_name = ?, contact_email = ?, contact_phone = ?, address = ?, consultation_hours = ?, system_notice = ? WHERE id = ?";
            $stmt = $db_1->prepare($sql);
            $stmt->bind_param("ssssssi", $hospital_name, $contact_email, $contact_phone, $address, $consultation_hours, $system_notice, $settings['id']);
        }

        if ($stmt->execute()) {
            $stmt->close();
            logAction($_SESSION['staff_id'] ?? null, 'Updated System Settings', 'settings', $settings['id']);
            set_session_message("System settings updated successfully.");
            redirect_to(url_wrap('/staff/admin/settings.php'));
        } else {
            $errors[] = "Database update failed: " . $db_1->error;
            if ($stmt) $stmt->close();
        }
    }
}

$page_title = 'System Settings';
$specificCss = '/assets/css/add_staff.css';

include(SHARED_PATH . '/header.php'); 
?>

<div id="content">
  <?php include(SHARED_PATH . '/navigation.php'); ?>
  
  <main class="main-content">
    <div class="top">
        <p class="top-head">System Settings & Branding</p> 
        <p class="top-description">Manage health centre identity, contact details, consultation schedule, and announcements.</p>
    </div>

    <div><?php echo display_session_message(); ?></div>
    <?php if (!empty($errors)): ?>
        <div class="errors" style="margin-bottom: 20px;">
            <?php echo display_errors($errors); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo url_wrap('/staff/admin/settings.php'); ?>" method="POST" enctype="multipart/form-data" style="margin-top: 15px;">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px; align-items: start;">
            
            <!-- Left Column: Core Settings -->
            <div style="background: white; border-radius: 12px; border: 1px solid #e1e8ed; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
                <h3 style="margin: 0 0 20px 0; color: #0F4E74; font-size: 1.2rem; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px;">
                    <i class="bi bi-sliders"></i> General Information
                </h3>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; color: #333; display: block; margin-bottom: 6px;">Hospital / Health Centre Name *</label>
                    <input type="text" name="hospital_name" value="<?php echo htmlspecialchars($settings['hospital_name'] ?? ''); ?>" required 
                           style="width: 100%; padding: 10px 14px; border: 1px solid #ccd0d5; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box;">
                    <small style="color: #666; font-size: 0.8rem;">Displayed across portal headers, student cards, and official reports.</small>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label style="font-weight: 600; color: #333; display: block; margin-bottom: 6px;">Official Contact Email</label>
                        <input type="email" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" 
                               style="width: 100%; padding: 10px 14px; border: 1px solid #ccd0d5; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; color: #333; display: block; margin-bottom: 6px;">Contact Phone Number</label>
                        <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>" 
                               style="width: 100%; padding: 10px 14px; border: 1px solid #ccd0d5; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; color: #333; display: block; margin-bottom: 6px;">Physical Campus Address</label>
                    <textarea name="address" rows="2" 
                              style="width: 100%; padding: 10px 14px; border: 1px solid #ccd0d5; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box; resize: vertical;"><?php echo htmlspecialchars($settings['address'] ?? ''); ?></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; color: #333; display: block; margin-bottom: 6px;">Consultation & Operating Hours</label>
                    <input type="text" name="consultation_hours" value="<?php echo htmlspecialchars($settings['consultation_hours'] ?? ''); ?>" 
                           placeholder="e.g. Mon - Fri: 8:00 AM - 8:00 PM | Emergency: 24/7"
                           style="width: 100%; padding: 10px 14px; border: 1px solid #ccd0d5; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; color: #333; display: block; margin-bottom: 6px;">System Broadcast Notice / Announcement</label>
                    <textarea name="system_notice" rows="3" placeholder="Optional notice displayed to staff and patients..."
                              style="width: 100%; padding: 10px 14px; border: 1px solid #ccd0d5; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box; resize: vertical;"><?php echo htmlspecialchars($settings['system_notice'] ?? ''); ?></textarea>
                    <small style="color: #666; font-size: 0.8rem;">Leave blank if there is no active system-wide broadcast.</small>
                </div>

                <div style="margin-top: 25px; padding-top: 15px; border-top: 1px solid #f0f0f0; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary" style="background: #0F4E74; color: white; border: none; padding: 12px 28px; border-radius: 6px; font-weight: 600; font-size: 0.95rem; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-check-circle-fill"></i> Save Settings
                    </button>
                </div>
            </div>

            <!-- Right Column: Logo & Branding -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div style="background: white; border-radius: 12px; border: 1px solid #e1e8ed; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); text-align: center;">
                    <h3 style="margin: 0 0 15px 0; color: #0F4E74; font-size: 1.1rem; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px;">
                        <i class="bi bi-image"></i> Official Logo
                    </h3>

                    <?php 
                    $logo_file = (!empty($settings['logo']) && file_exists(PROJECT_PATH . '/public/assets/images/' . $settings['logo'])) 
                        ? '/assets/images/' . $settings['logo'] 
                        : '/assets/images/futa_logo.png';
                    ?>
                    <div style="margin: 15px auto; width: 140px; height: 140px; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; display: flex; align-items: center; justify-content: center; padding: 10px;">
                        <img src="<?php echo url_wrap($logo_file); ?>" alt="Current Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>

                    <div style="margin-top: 15px; text-align: left;">
                        <label style="font-weight: 600; color: #333; font-size: 0.85rem; display: block; margin-bottom: 6px;">Upload New Logo</label>
                        <input type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml" style="font-size: 0.85rem; width: 100%;">
                        <small style="color: #666; font-size: 0.75rem; display: block; margin-top: 5px;">
                            Supported: PNG, JPG, WEBP, SVG (Max 2MB). Recommended square transparent PNG (250x250).
                        </small>
                    </div>
                </div>

                <!-- Server & System Status Card -->
                <div style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px;">
                    <h4 style="margin: 0 0 12px 0; color: #334155; font-size: 0.95rem; font-weight: 600;">
                        <i class="bi bi-info-circle"></i> System Environment
                    </h4>
                    <div style="font-size: 0.82rem; color: #64748b; line-height: 1.8;">
                        <div><strong>PHP Version:</strong> <?php echo phpversion(); ?></div>
                        <div><strong>Database:</strong> Connected (MySQL)</div>
                        <div><strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s T'); ?></div>
                        <div><strong>Last Updated:</strong> <?php echo htmlspecialchars($settings['updated_at'] ?? 'Initial setup'); ?></div>
                    </div>
                </div>
            </div>

        </div>
    </form>
  </main>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
