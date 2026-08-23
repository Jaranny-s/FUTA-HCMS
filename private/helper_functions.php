<?php

function url_wrap($script_path) { // wraps around a url to secure it from SQL injections
  // add the leading '/' if not present
  if($script_path[0] != '/') {
    $script_path = "/" . $script_path;
  }
  return WWW_ROOT . $script_path;
}

function u_wrap($string="") { // wraps around a url encode to secure it from SQL injections
    return urlencode($string);
}

function ru_wrap($string="") { // wraps around a raw url encode to secure it from SQL injections
    return rawurlencode($string);
}

function v_wrap($string="") { // wraps around a variable to secure it from SQL injections
    return htmlspecialchars($string);
}

function error_404() {
    header($_SERVER["SERVER_PROTOCOL"] . "404 Not Found");
    exit();
}

function error_500() {
    header($_SERVER["SERVER_PROTOCOL"] . "500 Internal Server Error");
    exit();
}

function redirect_to($location) {
    header("Location: " . $location);
    exit();
}

function is_post_request() {
  return $_SERVER['REQUEST_METHOD'] == 'POST';
}

function is_get_request() {
  return $_SERVER['REQUEST_METHOD'] == 'GET';
}

function display_errors($errors=array()) {
     $output = '';
     if(!empty($errors)) {
         $output .= "<div class=\"errors\">";
         $output .= "Please fix the following errors:";
         $output .= "<ul>";
         foreach($errors as $error) {
             $output .= "<li>" . v_wrap($error) . "</li>";
         }
         $output .= "</ul>";
         $output .= "</div>";
     }
     return $output;
 }

function display_session_message() {

    if (
        isset($_SESSION['message']) &&
        $_SESSION['message'] !== ''
    ) {

        $msg = $_SESSION['message'];

        unset($_SESSION['message']);

        return "
            <div id=\"status_message\" style=\"padding: 10px; background: white; color: #615cff; border: 2px solid #615cff; margin: 5px; border-radius: 6px; text-align: center; width: 500px; margin: 0 auto; \">
                " . v_wrap($msg) . "
            </div>
        ";
    }

    return '';
}

function display_temp_password() {
    if(isset($_SESSION['temp_password']) && $_SESSION['temp_password'] !== '') {
    $tempPassword = $_SESSION['temp_password'];
    unset($_SESSION['temp_password']);
    return "<div id=\"status_message\" style=\"padding: 10px; background: white; color: #1bc03d; border: 2px solid #615cff; margin: 5px; border-radius: 6px; text-align: center; width: 500px;\">
        Your temporary password is: {$tempPassword}. Please store this immediately and send it to the staff.
        </div>";
        
  }
    return '';
}

function compress_and_save($source, $destination, $ext) {

    if ($ext === 'jpg' || $ext === 'jpeg') {
        $image = imagecreatefromjpeg($source);
        imagejpeg($image, $destination, 75); // 75% quality
    } 
    elseif ($ext === 'png') {
        $image = imagecreatefrompng($source);
        imagepng($image, $destination, 6); // compression level
    } 
    elseif ($ext === 'webp') {
        $image = imagecreatefromwebp($source);
        imagewebp($image, $destination, 75);
    }

    imagedestroy($image);
}

//if (!isset($_SESSION['ROLE_REFRESH_TIME']) ||
  //  time() - $_SESSION['ROLE_REFRESH_TIME'] > 3600) {
    
    //function refresh_role($role) {
        
    //global $db_1;
    //$staff_id = $_SESSION['staff_id'];
    //$role = "";
//
    //$sql = "SELECT role FROM staff ";
    //$sql .= "WHERE id = ?";
    //    
    //$query = $db_1->prepare($sql);
    //
    //$query->bind_param("i", $staff_id);
    //
    //$query->execute();
    //
    //$query->bind_result($role);
    //
    //$query->fetch();
    //
    //$query->close();
//
    //}
    //
    //$_SESSION['role'] = $role;
    //$_SESSION['ROLE_REFRESH_TIME'] = time();
//}

function upload_patient_image($file)
{
    if (
        !isset($file) ||
        !isset($file['error']) ||
        $file['error'] === UPLOAD_ERR_NO_FILE
    ) {
        return [
            'success' => true,
            'filename' => null
        ];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'error' => 'There was a problem uploading the patient image.'
        ];
    }

    // Maximum file size: 5 MB
    if ($file['size'] > 5 * 1024 * 1024) {
        return [
            'success' => false,
            'error' => 'Patient image must not exceed 5 MB.'
        ];
    }

    // Verify the actual MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);

    $mime = $finfo->file($file['tmp_name']);

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowedTypes[$mime])) {
        return [
            'success' => false,
            'error' => 'Only JPG, PNG and WebP images are allowed.'
        ];
    }

    $extension = $allowedTypes[$mime];

    /*
    |--------------------------------------------------------------------------
    | Generate unique filename
    |--------------------------------------------------------------------------
    */

    $imageName = 'patient_' . bin2hex(random_bytes(16)) . '.' . $extension;

    /*
    |--------------------------------------------------------------------------
    | Upload directory
    |--------------------------------------------------------------------------
    |
    | Adjust this path to wherever you want patient images stored.
    |
    */

    $uploadDirectory = __DIR__ . '/../public/modules/patients/images/patient_pictures/';

    if (!is_dir($uploadDirectory)) {
        if (!mkdir($uploadDirectory, 0755, true)) {
            return [
                'success' => false,
                'error' => 'Unable to create the patient image directory.'
            ];
        }
    }

    $destination = $uploadDirectory . $imageName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success' => false,
            'error' => 'Unable to save the patient image.'
        ];
    }

    return [
        'success' => true,
        'filename' => $imageName
    ];
}

//function delete_patient_image($filename)
//{
//    if (empty($filename)) {
//        return true;
//    }
//
//    $uploadDirectory =
//        __DIR__ . '/../public/modules/patients/images/patient_pictures/';
//
//    $filePath = $uploadDirectory . basename($filename);
//
//    if (is_file($filePath)) {
//        return unlink($filePath);
//    }
//
//    return true;
//}
?>