<?php
/**
 * Canonical FUTA Faculties / Schools and Departments Dataset
 * Maps each department to its Faculty/School, Code, Degree Awarded, and Study Duration (Years).
 */

function get_futa_departments_data() {
    return [
        'School of Agriculture and Agricultural Technology' => [
            'Agricultural Extension and Communication Technology' => ['code' => 'AEC', 'degree' => 'B.Agric.Tech', 'years' => 5],
            'Agricultural and Resource Economics' => ['code' => 'ARE', 'degree' => 'B.Agric.Tech', 'years' => 5],
            'Animal Production and Health' => ['code' => 'APH', 'degree' => 'B.Agric.Tech', 'years' => 5],
            'Crop, Soil and Pest Management' => ['code' => 'CSP', 'degree' => 'B.Agric.Tech', 'years' => 5],
            'Ecotourism and Wildlife Management' => ['code' => 'EWM', 'degree' => 'B.Agric.Tech', 'years' => 5],
            'Fisheries and Aquaculture Technology' => ['code' => 'FAT', 'degree' => 'B.Agric.Tech', 'years' => 5],
            'Food Science and Technology' => ['code' => 'FST', 'degree' => 'B.Tech', 'years' => 5],
            'Forestry and Wood Technology' => ['code' => 'FWT', 'degree' => 'B.Agric.Tech', 'years' => 5],
            'Nutrition and Dietetics' => ['code' => 'NUT', 'degree' => 'B.Tech', 'years' => 5]
        ],
        'School of Infrastructure, Minerals and Manufacturing Engineering' => [
            'Agricultural and Environmental Engineering' => ['code' => 'AGE', 'degree' => 'B.Eng', 'years' => 5],
            'Chemical Engineering' => ['code' => 'CHE', 'degree' => 'B.Eng', 'years' => 5],
            'Civil Engineering' => ['code' => 'CVE', 'degree' => 'B.Eng', 'years' => 5],
            'Industrial and Production Engineering' => ['code' => 'IPE', 'degree' => 'B.Eng', 'years' => 5],
            'Mechanical Engineering' => ['code' => 'MEE', 'degree' => 'B.Eng', 'years' => 5],
            'Metallurgical and Materials Engineering' => ['code' => 'MME', 'degree' => 'B.Eng', 'years' => 5],
            'Mining Engineering' => ['code' => 'MNE', 'degree' => 'B.Eng', 'years' => 5]
        ],
        'School of Electrical Systems Engineering' => [
            'Biomedical Engineering' => ['code' => 'BME', 'degree' => 'B.Eng', 'years' => 5],
            'Computer Engineering' => ['code' => 'CPE', 'degree' => 'B.Eng', 'years' => 5],
            'Electrical and Electronics Engineering' => ['code' => 'EEE', 'degree' => 'B.Eng', 'years' => 5],
            'Information and Communication Engineering' => ['code' => 'ICE', 'degree' => 'B.Eng', 'years' => 5],
            'Mechatronics Engineering' => ['code' => 'MCE', 'degree' => 'B.Eng', 'years' => 5]
        ],
        'School of Computing' => [
            'Computer Science' => ['code' => 'CSC', 'degree' => 'B.Tech', 'years' => 5],
            'Cyber Security Science' => ['code' => 'CYS', 'degree' => 'B.Tech', 'years' => 5],
            'Information Systems' => ['code' => 'IFS', 'degree' => 'B.Tech', 'years' => 5],
            'Information Technology' => ['code' => 'IFT', 'degree' => 'B.Tech', 'years' => 5],
            'Software Engineering' => ['code' => 'SEN', 'degree' => 'B.Tech', 'years' => 5]
        ],
        'School of Earth and Mineral Sciences' => [
            'Applied Geology' => ['code' => 'AGY', 'degree' => 'B.Tech', 'years' => 5],
            'Applied Geophysics' => ['code' => 'AGP', 'degree' => 'B.Tech', 'years' => 5],
            'Marine Science and Technology' => ['code' => 'MST', 'degree' => 'B.Tech', 'years' => 5],
            'Meteorology and Climate Science' => ['code' => 'MCS', 'degree' => 'B.Tech', 'years' => 5],
            'Remote Sensing and Geoscience Information Systems' => ['code' => 'RSG', 'degree' => 'B.Tech', 'years' => 5]
        ],
        'School of Environmental Technology' => [
            'Architecture' => ['code' => 'ARC', 'degree' => 'B.Tech', 'years' => 5],
            'Building' => ['code' => 'BDG', 'degree' => 'B.Tech', 'years' => 5],
            'Estate Management' => ['code' => 'ESM', 'degree' => 'B.Tech', 'years' => 5],
            'Industrial Design' => ['code' => 'IDD', 'degree' => 'B.Tech', 'years' => 5],
            'Quantity Surveying' => ['code' => 'QSV', 'degree' => 'B.Tech', 'years' => 5],
            'Surveying and Geoinformatics' => ['code' => 'SVG', 'degree' => 'B.Tech', 'years' => 5],
            'Urban and Regional Planning' => ['code' => 'URP', 'degree' => 'B.Tech', 'years' => 5],
            'Environmental Management Technology' => ['code' => 'EMT', 'degree' => 'B.Tech', 'years' => 5]
        ],
        'School of Logistics and Innovation Technology' => [
            'Logistics and Transport Technology' => ['code' => 'LTT', 'degree' => 'B.Tech', 'years' => 5],
            'Project Management Technology' => ['code' => 'PMT', 'degree' => 'B.Tech', 'years' => 5],
            'Procurement Management Technology' => ['code' => 'PRM', 'degree' => 'B.Tech', 'years' => 5],
            'Financial Technology' => ['code' => 'FIN', 'degree' => 'B.Tech', 'years' => 5],
            'Entrepreneurship Management Technology' => ['code' => 'EMT', 'degree' => 'B.Tech', 'years' => 5],
            'Business Information Technology' => ['code' => 'BIT', 'degree' => 'B.Tech', 'years' => 5]
        ],
        'School of Life Sciences' => [
            'Biochemistry' => ['code' => 'BCH', 'degree' => 'B.Tech', 'years' => 4],
            'Biology' => ['code' => 'BIO', 'degree' => 'B.Tech', 'years' => 4],
            'Biotechnology' => ['code' => 'BTC', 'degree' => 'B.Tech', 'years' => 4],
            'Microbiology' => ['code' => 'MCB', 'degree' => 'B.Tech', 'years' => 4]
        ],
        'School of Physical Sciences' => [
            'Chemistry' => ['code' => 'CHM', 'degree' => 'B.Tech', 'years' => 4],
            'Mathematical Sciences' => ['code' => 'MTS', 'degree' => 'B.Tech', 'years' => 4],
            'Physics' => ['code' => 'PHY', 'degree' => 'B.Tech', 'years' => 4],
            'Statistics' => ['code' => 'STA', 'degree' => 'B.Tech', 'years' => 4]
        ],
        'College of Health Sciences' => [
            'Human Anatomy' => ['code' => 'ANA', 'degree' => 'B.Sc', 'years' => 4],
            'Human Physiology' => ['code' => 'PHS', 'degree' => 'B.Sc', 'years' => 4],
            'Biomedical Technology' => ['code' => 'BIM', 'degree' => 'B.Tech', 'years' => 5],
            'Medical Laboratory Science' => ['code' => 'MLS', 'degree' => 'BMLS', 'years' => 5],
            'Nursing Science' => ['code' => 'NSC', 'degree' => 'BNSc', 'years' => 5],
            'Medicine and Surgery' => ['code' => 'MED', 'degree' => 'MBBS', 'years' => 6]
        ]
    ];
}

/**
 * Returns study duration in years for a given department name.
 * Default fallback is 5 years.
 */
function get_department_study_duration($department_name) {
    if (empty($department_name)) {
        return 5;
    }
    
    $clean_dept = trim(strtolower($department_name));
    $all = get_futa_departments_data();
    
    foreach ($all as $faculty => $depts) {
        foreach ($depts as $dName => $info) {
            if (strtolower($dName) === $clean_dept || strpos($clean_dept, strtolower($dName)) !== false || strpos(strtolower($dName), $clean_dept) !== false) {
                return (int)$info['years'];
            }
        }
    }
    
    // Check specific known patterns
    if (strpos($clean_dept, 'medicine') !== false || strpos($clean_dept, 'surgery') !== false || strpos($clean_dept, 'mbbs') !== false) {
        return 6;
    }
    if (strpos($clean_dept, 'anatomy') !== false || strpos($clean_dept, 'physiology') !== false || 
        strpos($clean_dept, 'biochem') !== false || strpos($clean_dept, 'biology') !== false || 
        strpos($clean_dept, 'biotech') !== false || strpos($clean_dept, 'microbio') !== false || 
        strpos($clean_dept, 'chem') !== false || strpos($clean_dept, 'math') !== false || 
        strpos($clean_dept, 'physics') !== false || strpos($clean_dept, 'stat') !== false) {
        return 4;
    }

    return 5;
}

/**
 * Returns the Faculty/School for a given department name.
 */
function get_department_faculty($department_name) {
    if (empty($department_name)) {
        return '';
    }
    $clean_dept = trim(strtolower($department_name));
    $all = get_futa_departments_data();
    foreach ($all as $faculty => $depts) {
        foreach ($depts as $dName => $info) {
            if (strtolower($dName) === $clean_dept || strpos($clean_dept, strtolower($dName)) !== false || strpos(strtolower($dName), $clean_dept) !== false) {
                return $faculty;
            }
        }
    }
    return '';
}

/**
 * Renders an HTML <optgroup> list of all FUTA departments.
 */
function render_futa_department_options($selected_department = '') {
    $data = get_futa_departments_data();
    $html = '<option value="">-- Select Department --</option>';
    $clean_selected = trim(strtolower($selected_department));
    $is_matched = false;

    foreach ($data as $faculty => $depts) {
        $html .= '<optgroup label="' . htmlspecialchars($faculty) . '">';
        foreach ($depts as $dName => $info) {
            $selected = ($clean_selected !== '' && (strtolower($dName) === $clean_selected || strpos($clean_selected, strtolower($dName)) !== false)) ? 'selected' : '';
            if ($selected) $is_matched = true;
            $html .= '<option value="' . htmlspecialchars($dName) . '" data-faculty="' . htmlspecialchars($faculty) . '" data-years="' . $info['years'] . '" ' . $selected . '>';
            $html .= htmlspecialchars($dName) . ' (' . $info['degree'] . ' - ' . $info['years'] . ' yrs)';
            $html .= '</option>';
        }
        $html .= '</optgroup>';
    }

    if (!empty($selected_department) && !$is_matched) {
        $html .= '<optgroup label="Other / Custom">';
        $html .= '<option value="' . htmlspecialchars($selected_department) . '" selected>' . htmlspecialchars($selected_department) . '</option>';
        $html .= '</optgroup>';
    }

    return $html;
}
