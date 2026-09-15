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
    $action = $_POST['action'] ?? 'save_settings';

    if ($action === 'advance_academic_session') {
        $confirm = strtoupper(trim($_POST['confirm_progression'] ?? ''));
        if ($confirm !== 'ADVANCE') {
            $errors[] = "Confirmation keyword mismatch. Please type ADVANCE exactly in uppercase to advance the session.";
        } else {
            $result = advance_academic_session($_SESSION['staff_id'] ?? null);
            if (!empty($result['success'])) {
                set_session_message("Academic Session successfully advanced from " . htmlspecialchars($result['old_session']) . " to " . htmlspecialchars($result['new_session']) . ". Undergraduates promoted: {$result['promoted_count']}, Final-years graduated to Inactive: {$result['graduated_count']}, Inactive records auto-archived: {$result['archived_count']}.", "success");
                redirect_to(url_wrap('/staff/admin/settings.php'));
            } else {
                $errors[] = "Failed to advance academic session: " . ($result['error'] ?? 'Unknown error');
            }
        }
    } else {
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
}

$settings = get_settings();
$progression = get_session_progression_preview();

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

    <!-- Academic Session & Student Lifecycle Management Card -->
    <div style="margin-top: 30px; background: white; border-radius: 12px; border: 1px solid #e1e8ed; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 20px;">
            <div>
                <h3 style="margin: 0 0 6px 0; color: #0F4E74; font-size: 1.25rem; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-mortarboard-fill"></i> Academic Session & Student Lifecycle Engine
                </h3>
                <p style="margin: 0; color: #64748b; font-size: 0.88rem;">
                    Manage annual academic session promotions, department-specific graduation rules, and inactive student archive cycles.
                </p>
            </div>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <span style="background: #e0f2fe; color: #0369a1; padding: 6px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="bi bi-clock-history"></i> Sessions Completed: <?php echo (int)($settings['session_progression_count'] ?? 0); ?>
                </span>
                <button type="button" onclick="openSessionModal()" class="btn" style="background: #0F4E74; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-arrow-up-right-circle-fill"></i> Advance Academic Session
                </button>
            </div>
        </div>

        <!-- Session Badges & Pipeline Summary -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 25px;">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px;">
                <div style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600;">Current Session</div>
                <div style="font-size: 1.4rem; font-weight: 700; color: #0F4E74; margin-top: 4px;">
                    <?php echo htmlspecialchars($progression['current_session'] ?? '2025/2026'); ?>
                </div>
                <div style="font-size: 0.8rem; color: #0284c7; margin-top: 4px;">
                    <i class="bi bi-arrow-right-short"></i> Next: <strong><?php echo htmlspecialchars($progression['next_session'] ?? ''); ?></strong>
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px;">
                <div style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600;">Active Undergrads</div>
                <div style="font-size: 1.4rem; font-weight: 700; color: #1e293b; margin-top: 4px;">
                    <?php echo $progression['total_active_students']; ?>
                </div>
                <div style="font-size: 0.8rem; color: #16a34a; margin-top: 4px;">
                    <i class="bi bi-arrow-up-circle"></i> <?php echo $progression['promoted_count']; ?> will promote (+100L)
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px;">
                <div style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600;">Graduating This Cycle</div>
                <div style="font-size: 1.4rem; font-weight: 700; color: #d97706; margin-top: 4px;">
                    <?php echo $progression['graduating_count']; ?>
                </div>
                <div style="font-size: 0.8rem; color: #b45309; margin-top: 4px;">
                    <i class="bi bi-person-check"></i> Move to Inactive (Graduated)
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px;">
                <div style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600;">Archived Records</div>
                <div style="font-size: 1.4rem; font-weight: 700; color: #64748b; margin-top: 4px;">
                    <?php echo $progression['total_archived']; ?>
                </div>
                <div style="font-size: 0.8rem; color: #dc2626; margin-top: 4px;">
                    <?php echo $progression['will_archive_count']; ?> reaching 5-yr archive threshold
                </div>
            </div>
        </div>

        <!-- Safeguard Notice Banner -->
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px 18px; display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
            <i class="bi bi-shield-check" style="font-size: 1.4rem; color: #16a34a; flex-shrink: 0;"></i>
            <div style="font-size: 0.88rem; color: #166534; line-height: 1.5;">
                <strong>Test Account Safeguard Active:</strong> Student record <strong>Agho John Esosa (ICT/20/5785)</strong> is permanently protected by system rule and will never be automatically graduated or inactivated during session progression.
            </div>
        </div>

        <!-- Lifecycle Rules Explanation Box -->
        <div style="background: #f8fafc; border-radius: 8px; padding: 18px; border: 1px solid #e2e8f0; font-size: 0.85rem; color: #475569; line-height: 1.6;">
            <div style="font-weight: 600; color: #1e293b; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                <i class="bi bi-diagram-3"></i> FUTA Lifecycle Automation Rules:
            </div>
            <ul style="margin: 0; padding-left: 20px;">
                <li><strong>Undergraduate Progression:</strong> Active students in 100L, 200L, 300L, etc., automatically advance by +100 Level.</li>
                <li><strong>Department-Specific Terminal Year:</strong> Based on the 59 FUTA degree programs (4-Year B.Tech &rarr; 400L; 5-Year B.Tech/B.Eng &rarr; 500L; 6-Year MBBS &rarr; 600L), final-year students graduate to <code>Inactive</code>.</li>
                <li><strong>Access Control:</strong> Inactive and Archived accounts cannot log in to the Student Portal or request subsidized campus appointments.</li>
                <li><strong>Cold Storage Auto-Archiving:</strong> Any patient record remaining <code>Inactive</code> across 5 consecutive academic sessions is automatically moved to <code>Archived</code>.</li>
                <li><strong>Alumni / Re-registration:</strong> If a graduated student or alumnus seeks healthcare at the health centre, receptionists can locate the record and seamlessly transition them to <code>External</code> or <code>Staff Dependant</code> with 1 click, preserving their full clinical history.</li>
            </ul>
        </div>
    </div>

    <!-- Advance Session Confirmation Modal -->
    <div id="advanceSessionModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 12px; max-width: 540px; width: 92%; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 18px;">
                <h3 style="margin: 0; color: #0F4E74; font-size: 1.2rem; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-mortarboard-fill"></i> Advance to Next Academic Session
                </h3>
                <button type="button" onclick="closeSessionModal()" style="background: none; border: none; font-size: 1.4rem; color: #94a3b8; cursor: pointer;">&times;</button>
            </div>

            <form action="<?php echo url_wrap('/staff/admin/settings.php'); ?>" method="POST">
                <input type="hidden" name="action" value="advance_academic_session">

                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 14px; margin-bottom: 18px;">
                    <div style="display: flex; align-items: center; gap: 8px; color: #92400e; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">
                        <i class="bi bi-exclamation-triangle-fill"></i> Bulk Progression Confirmation
                    </div>
                    <p style="margin: 0; font-size: 0.85rem; color: #78350f; line-height: 1.5;">
                        You are about to transition the health centre system from academic session <strong><?php echo htmlspecialchars($progression['current_session'] ?? '2025/2026'); ?></strong> to <strong><?php echo htmlspecialchars($progression['next_session'] ?? ''); ?></strong>.
                    </p>
                </div>

                <div style="font-size: 0.88rem; color: #334155; margin-bottom: 16px; line-height: 1.6;">
                    <strong>Effects of this operation:</strong>
                    <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                        <li><strong><?php echo $progression['promoted_count']; ?></strong> active undergraduate students will advance to the next level (+100L).</li>
                        <li><strong><?php echo $progression['graduating_count']; ?></strong> final-year students will be graduated and status updated to <code>Inactive</code>.</li>
                        <li><strong><?php echo $progression['will_archive_count']; ?></strong> inactive accounts will be transitioned to <code>Archived</code> cold storage (5th session threshold).</li>
                        <li>Account <strong>Agho John Esosa</strong> (ICT/20/5785) is fully preserved.</li>
                    </ul>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; font-size: 0.88rem; color: #0F4E74; margin-bottom: 6px;">
                        Type <strong>ADVANCE</strong> below to confirm:
                    </label>
                    <input type="text" name="confirm_progression" id="confirmProgressionInput" required placeholder="ADVANCE" 
                           style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem; font-weight: 600; letter-spacing: 1px; box-sizing: border-box;">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeSessionModal()" style="padding: 10px 18px; border: 1px solid #cbd5e1; background: #f8fafc; border-radius: 6px; font-weight: 600; cursor: pointer; color: #475569;">
                        Cancel
                    </button>
                    <button type="submit" style="padding: 10px 20px; border: none; background: #0F4E74; color: white; border-radius: 6px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-check-circle-fill"></i> Execute Session Advancement
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openSessionModal() {
        var modal = document.getElementById('advanceSessionModal');
        if (modal) {
            modal.style.display = 'flex';
            var input = document.getElementById('confirmProgressionInput');
            if (input) {
                input.value = '';
                input.focus();
            }
        }
    }
    function closeSessionModal() {
        var modal = document.getElementById('advanceSessionModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
    window.addEventListener('click', function(e) {
        var modal = document.getElementById('advanceSessionModal');
        if (e.target === modal) {
            closeSessionModal();
        }
    });
    </script>
  </main>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
