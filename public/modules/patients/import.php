<?php
require_once('../../../private/config.php');
require_password_reset();

if (!hasPermission('create_patient')) {
    redirect_to(url_wrap('/staff/dashboard.php'));
}

$page_title = 'Import Patients';
include(SHARED_PATH . '/header.php'); 
?>

<div id="content">
    <?php include(SHARED_PATH . '/navigation.php'); ?>

    <main class="main-content">
        <a href="javascript:history.back()" class="btn btn-primary" id="link_layout"><i class="bi bi-arrow-left"></i> Back</a>

        <div class="top">
            <p class="top-head">Import Patients</p>
            <p class="top-description">upload a CSV file to batch register patients</p>
        </div>

        <div><?php echo display_session_message(); ?></div>

        <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 600px;">
            <h4 style="margin-top: 0;">Upload CSV File</h4>
            <p style="color: #666; margin-bottom: 20px;">
                Please ensure your CSV file has the following headers in the exact order or names:<br>
                <code>surname, first_name, middle_name, gender, phone, email, category</code>
            </p>
            
            <form action="import_processor.php" method="post" enctype="multipart/form-data">
                <div style="margin-bottom: 20px;">
                    <input type="file" name="csv_file" accept=".csv" required style="padding: 10px; border: 1px dashed #ccc; width: 100%; border-radius: 5px; cursor: pointer;">
                </div>
                
                <button type="submit" class="btn" style="background: #0F4E74; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: 600;">
                    <i class="bi bi-cloud-upload"></i> Upload & Import
                </button>
            </form>
        </div>
    </main>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
