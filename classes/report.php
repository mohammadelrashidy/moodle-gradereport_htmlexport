<?php
/**
 * HTML Export grade report class
 *
 * @package    gradereport_htmlexport
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/grade/report/lib.php');
require_once($CFG->dirroot.'/grade/lib.php');

/**
 * Class for HTML export grade report
 */
class gradereport_htmlexport_report extends grade_report {
    
    /**
     * Constructor
     *
     * @param int $courseid
     * @param context $context
     */
    public function __construct($courseid, $context) {
        parent::__construct($courseid, null, $context);
        // Grade report initialization is handled by parent class
        // User and grade data is loaded when needed for export
    }
    
    /**
     * Process the data sent by the form (required by grade_report)
     *
     * @param array $data
     * @return void
     */
    public function process_data($data) {
        // This method is required by the parent class but not used for HTML export
        // Data processing is handled directly in the export methods
    }
    
    /**
     * Process any actions (required by grade_report)
     *
     * @param string $target
     * @param string $action
     * @return void
     */
    public function process_action($target, $action) {
        // This method is required by the parent class but not used for HTML export
        // Actions are handled directly in index.php
    }
    
    /**
     * Display student selection form
     *
     * @return string HTML form
     */
    public function display_student_selection_form() {
        global $OUTPUT;
        
        $html = '';
        
        // Get enrolled students
        $students = get_enrolled_users($this->context, 'moodle/grade:view', 0, 'u.*', 'u.lastname, u.firstname');
        
        if (empty($students)) {
            return $OUTPUT->notification(get_string('nostudents', 'gradereport_htmlexport'), 'info');
        }
        
        $html .= html_writer::start_tag('form', array('method' => 'post', 'action' => ''));
        $html .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $this->courseid));
        $html .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'export'));
        
        $html .= html_writer::start_tag('div', array('class' => 'form-group'));
        $html .= html_writer::tag('label', get_string('selectstudent', 'gradereport_htmlexport'), 
                                 array('for' => 'userid'));
        
        $options = array();
        foreach ($students as $student) {
            $options[$student->id] = fullname($student);
        }
        
        $html .= html_writer::select($options, 'userid', '', array('' => get_string('choosedots')), 
                                   array('id' => 'userid', 'class' => 'form-control'));
        $html .= html_writer::end_tag('div');
        
        $html .= html_writer::tag('button', get_string('exporthtml', 'gradereport_htmlexport'), 
                                array('type' => 'submit', 'class' => 'btn btn-primary'));
        $html .= html_writer::end_tag('form');
        
        // Add bulk download form
        $html .= html_writer::tag('hr', '');
        $html .= html_writer::tag('h3', get_string('bulkdownload', 'gradereport_htmlexport'));
        $html .= html_writer::tag('p', get_string('bulkdownloaddesc', 'gradereport_htmlexport'));
        
        $html .= html_writer::start_tag('form', array('method' => 'post', 'action' => ''));
        $html .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $this->courseid));
        $html .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'export'));
        $html .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'bulk', 'value' => '1'));
        
        $html .= html_writer::tag('button', get_string('exportallstudents', 'gradereport_htmlexport'), 
                                array('type' => 'submit', 'class' => 'btn btn-success btn-lg', 
                                      'onclick' => 'return confirm("' . get_string('confirmexportall', 'gradereport_htmlexport') . '")'));
        $html .= html_writer::end_tag('form');
        
        return $html;
    }
    
    /**
     * Export student grades as HTML
     *
     * @param int $userid
     */
    public function export_student_grades($userid) {
        global $CFG, $SITE, $DB;
        
        // Verify user access
        if (!$this->can_view_user_grades($userid)) {
            throw new moodle_exception('nopermissions', 'error');
        }
        
        $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $this->courseid), '*', MUST_EXIST);
        
        // Generate HTML content
        $html = $this->generate_grade_html($user, $course);
        
        // Set headers for download
        $filename = clean_filename($course->shortname . '_' . fullname($user) . '_grades.html');
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        
        echo $html;
    }
    
    /**
     * Export all students' grades as a ZIP file containing HTML files
     */
    public function export_all_students_bulk() {
        global $CFG, $DB;
        
        // Get enrolled students
        $students = get_enrolled_users($this->context, 'moodle/grade:view', 0, 'u.*', 'u.lastname, u.firstname');
        
        if (empty($students)) {
            throw new moodle_exception('nostudents', 'gradereport_htmlexport');
        }
        
        $course = $DB->get_record('course', array('id' => $this->courseid), '*', MUST_EXIST);
        
        // Create temporary directory for HTML files
        $tempdir = make_temp_directory('gradereport_htmlexport');
        $exportdir = $tempdir . '/' . uniqid('export_');
        mkdir($exportdir, 0755, true);
        
        $files = array();
        
        // Generate HTML file for each student
        foreach ($students as $student) {
            if (!$this->can_view_user_grades($student->id)) {
                continue; // Skip students the teacher cannot view
            }
            
            $html = $this->generate_grade_html($student, $course);
            $filename = clean_filename($course->shortname . '_' . fullname($student) . '_grades.html');
            $filepath = $exportdir . '/' . $filename;
            
            file_put_contents($filepath, $html);
            $files[] = $filepath;
        }
        
        if (empty($files)) {
            throw new moodle_exception('nofilestoexport', 'gradereport_htmlexport');
        }
        
        // Create ZIP file
        $zipfilename = clean_filename($course->shortname . '_all_grades_' . date('Y-m-d_H-i-s') . '.zip');
        $zippath = $tempdir . '/' . $zipfilename;
        
        $zip = new ZipArchive();
        if ($zip->open($zippath, ZipArchive::CREATE) !== TRUE) {
            throw new moodle_exception('cannotcreatezip', 'gradereport_htmlexport');
        }
        
        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();
        
        // Send ZIP file to browser
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipfilename . '"');
        header('Content-Length: ' . filesize($zippath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        
        readfile($zippath);
        
        // Clean up temporary files
        foreach ($files as $file) {
            unlink($file);
        }
        unlink($zippath);
        rmdir($exportdir);
    }
    
    /**
     * Generate HTML content for student grades
     *
     * @param stdClass $user
     * @param stdClass $course
     * @return string HTML content
     */
    private function generate_grade_html($user, $course) {
        global $CFG, $SITE, $OUTPUT, $DB;
        
        // Get course grade tree using proper Moodle API
        $context = context_course::instance($this->courseid);
        $gpr = new grade_plugin_return(array('type' => 'report', 'plugin' => 'htmlexport', 'courseid' => $this->courseid));
        $gtree = new grade_tree($this->courseid, false, false, null, $gpr);
        
        $html = $this->get_html_header($user, $course);
        $html .= $this->get_grade_table_html($user, $gtree);
        $html .= $this->get_html_footer();
        
        return $html;
    }
    
    /**
     * Generate HTML header with course and student info
     *
     * @param stdClass $user
     * @param stdClass $course
     * @return string HTML header
     */
    private function get_html_header($user, $course) {
        global $SITE, $CFG;
        
        $html = '<!DOCTYPE html>';
        $html .= '<html dir="' . (right_to_left() ? 'rtl' : 'ltr') . '">';
        $html .= '<head>';
        $html .= '<meta charset="utf-8">';
        $html .= '<title>' . format_string($course->fullname) . ' - ' . fullname($user) . '</title>';
        $html .= '<style>' . $this->get_word_compatible_css() . '</style>';
        $html .= '</head>';
        $html .= '<body>';
        
        // Header section with logo
        $html .= '<div class="header">';
        $html .= '<div class="logo-section">';
        
        // Add site logo if available
        $logourl = $this->get_site_logo_url();
        if ($logourl) {
            $html .= '<img src="' . $logourl . '" alt="' . format_string($SITE->fullname) . ' Logo" class="site-logo">';
        }
        
        $html .= '<div class="header-text">';
        $html .= '<h1>' . format_string($SITE->fullname) . '</h1>';
        $html .= '<h2>' . format_string($course->fullname) . '</h2>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="student-info">';
        $html .= '<h3>' . get_string('student', 'gradereport_htmlexport') . ': ' . fullname($user) . '</h3>';
        $html .= '<p>' . get_string('reportdate', 'gradereport_htmlexport') . ': ' . userdate(time()) . '</p>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Generate grade table HTML
     *
     * @param stdClass $user
     * @param grade_tree $gtree
     * @return string HTML table
     */
    private function get_grade_table_html($user, $gtree) {
        $html = '<div class="grade-table">';
        $html .= '<table>';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>' . get_string('gradeitem', 'gradereport_htmlexport') . '</th>';
        $html .= '<th>' . get_string('grade', 'gradereport_htmlexport') . '</th>';
        $html .= '<th>' . get_string('range', 'gradereport_htmlexport') . '</th>';
        $html .= '<th>' . get_string('percentage', 'gradereport_htmlexport') . '</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        
        // Process grade tree
        if (isset($gtree->top_element['children'])) {
            $html .= $this->process_grade_tree_children($gtree->top_element['children'], $user->id, 0);
        }
        
        // Add course total
        $course_total = $this->get_course_total($user->id);
        if ($course_total !== null) {
            $html .= '<tr class="course-total-row">';
            $html .= '<td class="total-name"><strong>' . get_string('coursetotal', 'gradereport_htmlexport') . '</strong></td>';
            $html .= '<td class="grade-value total-value">' . $course_total['value'] . '</td>';
            $html .= '<td class="grade-range">' . $course_total['range'] . '</td>';
            $html .= '<td class="grade-percentage total-percentage">' . $course_total['percentage'] . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Process grade tree children recursively
     *
     * @param array $children
     * @param int $userid
     * @param int $level
     * @return string HTML rows
     */
    private function process_grade_tree_children($children, $userid, $level = 0) {
        $html = '';
        
        foreach ($children as $key => $child) {
            if ($child['type'] == 'category') {
                $category = $child['object'];
                
                // Category header
                $html .= '<tr class="category-row level-' . $level . '">';
                $html .= '<td colspan="4"><strong>' . format_string($category->fullname) . '</strong></td>';
                $html .= '</tr>';
                
                // Process category children
                if (!empty($child['children'])) {
                    $html .= $this->process_grade_tree_children($child['children'], $userid, $level + 1);
                }
                
                // Add category total if it has items
                if (!empty($child['children'])) {
                    $category_total = $this->calculate_category_total($category, $userid);
                    if ($category_total !== null) {
                        $html .= '<tr class="category-total-row level-' . $level . '">';
                        $html .= '<td class="total-name"><strong>' . get_string('total', 'gradereport_htmlexport') . ' - ' . format_string($category->fullname) . '</strong></td>';
                        $html .= '<td class="grade-value total-value">' . $category_total['value'] . '</td>';
                        $html .= '<td class="grade-range">' . $category_total['range'] . '</td>';
                        $html .= '<td class="grade-percentage total-percentage">' . $category_total['percentage'] . '</td>';
                        $html .= '</tr>';
                    }
                }
                
            } else if ($child['type'] == 'item') {
                $grade_item = $child['object'];
                
                // Skip course total item (we'll add it at the end)
                if ($grade_item->itemtype == 'course') {
                    continue;
                }
                
                // Check visibility
                if (!$this->can_view_grade_item($grade_item, $userid)) {
                    continue;
                }
                
                $grade_grade = grade_grade::fetch(array('itemid' => $grade_item->id, 'userid' => $userid));
                
                $html .= '<tr class="grade-row level-' . $level . '">';
                $html .= '<td class="item-name">' . format_string($grade_item->itemname) . '</td>';
                
                // Grade value
                $gradevalue = $this->format_grade_value($grade_grade, $grade_item);
                $html .= '<td class="grade-value">' . $gradevalue . '</td>';
                
                // Grade range
                $range = $this->format_grade_range($grade_item);
                $html .= '<td class="grade-range">' . $range . '</td>';
                
                // Percentage
                $percentage = $this->format_grade_percentage($grade_grade, $grade_item);
                $html .= '<td class="grade-percentage">' . $percentage . '</td>';
                
                $html .= '</tr>';
            }
        }
        
        return $html;
    }
    
    /**
     * Format grade value for display
     *
     * @param grade_grade|false $grade_grade
     * @param grade_item $grade_item
     * @return string
     */
    private function format_grade_value($grade_grade, $grade_item) {
        if (!$grade_grade || is_null($grade_grade->finalgrade)) {
            return '-';
        }
        
        return grade_format_gradevalue($grade_grade->finalgrade, $grade_item, true);
    }
    
    /**
     * Format grade range for display
     *
     * @param grade_item $grade_item
     * @return string
     */
    private function format_grade_range($grade_item) {
        return grade_format_gradevalue($grade_item->grademin, $grade_item, true) . ' - ' . 
               grade_format_gradevalue($grade_item->grademax, $grade_item, true);
    }
    
    /**
     * Format grade percentage for display
     *
     * @param grade_grade|false $grade_grade
     * @param grade_item $grade_item
     * @return string
     */
    private function format_grade_percentage($grade_grade, $grade_item) {
        if (!$grade_grade || is_null($grade_grade->finalgrade)) {
            return '-';
        }
        
        $percentage = grade_format_gradevalue($grade_grade->finalgrade, $grade_item, true, GRADE_DISPLAY_TYPE_PERCENTAGE);
        return $percentage;
    }
    
    /**
     * Check if user can view grade item
     *
     * @param grade_item $grade_item
     * @param int $userid
     * @return bool
     */
    private function can_view_grade_item($grade_item, $userid) {
        // Check if item is hidden
        if ($grade_item->is_hidden()) {
            return false;
        }
        
        // Check if user has capability to view hidden items
        if (has_capability('moodle/grade:viewhidden', $this->context)) {
            return true;
        }
        
        // For students, check if the grade item is visible to them
        // This is a simplified check - in practice you might need more complex logic
        return !$grade_item->is_locked() && !$grade_item->is_hidden();
    }
    
    /**
     * Check if current user can view grades for specified user
     *
     * @param int $userid
     * @return bool
     */
    private function can_view_user_grades($userid) {
        global $USER;
        
        // Teachers can view all students
        if (has_capability('moodle/grade:viewall', $this->context)) {
            return true;
        }
        
        // Students can only view their own grades
        if ($userid == $USER->id && has_capability('moodle/grade:view', $this->context)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get color setting with fallback to default
     *
     * @param string $setting Setting name (without plugin prefix)
     * @param string $default Default color value
     * @return string Color value
     */
    private function get_color_setting($setting, $default) {
        $value = get_config('gradereport_htmlexport', $setting);
        return !empty($value) ? $value : $default;
    }
    
    /**
     * Get all color settings as an associative array
     *
     * @return array Color settings
     */
    private function get_color_settings() {
        return array(
            'header_primary' => $this->get_color_setting('header_primary_color', '#6f42c1'),
            'header_secondary' => $this->get_color_setting('header_secondary_color', '#8e44ad'),
            'header_text' => $this->get_color_setting('header_text_color', '#ffffff'),
            'grade_excellent' => $this->get_color_setting('grade_excellent_color', '#28a745'),
            'grade_good' => $this->get_color_setting('grade_good_color', '#17a2b8'),
            'grade_average' => $this->get_color_setting('grade_average_color', '#ffc107'),
            'grade_poor' => $this->get_color_setting('grade_poor_color', '#dc3545'),
            'table_border' => $this->get_color_setting('table_border_color', '#dee2e6'),
            'row_alternate' => $this->get_color_setting('row_alternate_color', '#f8f9fa'),
            'row_hover' => $this->get_color_setting('row_hover_color', '#e8f4fd'),
            'category_primary' => $this->get_color_setting('category_primary_color', '#4a4a4a'),
            'category_secondary' => $this->get_color_setting('category_secondary_color', '#2d2d2d'),
            'category_total_primary' => $this->get_color_setting('category_total_primary_color', '#17a2b8'),
            'category_total_secondary' => $this->get_color_setting('category_total_secondary_color', '#138496'),
            'course_total_primary' => $this->get_color_setting('course_total_primary_color', '#28a745'),
            'course_total_secondary' => $this->get_color_setting('course_total_secondary_color', '#1e7e34'),
            'grade_value' => $this->get_color_setting('grade_value_color', '#28a745'),
            'grade_value_bg' => $this->get_color_setting('grade_value_bg_color', '#f8fff9'),
            'percentage' => $this->get_color_setting('percentage_color', '#007bff'),
            'percentage_bg' => $this->get_color_setting('percentage_bg_color', '#f8feff'),
        );
    }
    
    /**
     * Get Word-compatible CSS with configurable colors
     *
     * @return string CSS
     */
    private function get_word_compatible_css() {
        $colors = $this->get_color_settings();
        return '
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.4;
            margin: 20px;
            direction: ' . (right_to_left() ? 'rtl' : 'ltr') . ';
            background-color: ' . $colors['row_alternate'] . ';
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid ' . $colors['header_primary'] . ';
            padding-bottom: 20px;
            background: linear-gradient(135deg, ' . $colors['header_primary'] . ' 0%, ' . $colors['header_secondary'] . ' 100%);
            color: ' . $colors['header_text'] . ';
            border-radius: 8px 8px 0 0;
            padding: 20px;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .site-logo {
            max-height: 60px;
            max-width: 200px;
            margin-right: 20px;
            background: white;
            padding: 8px;
            border-radius: 8px;
        }
        
        .header-text {
            flex: 1;
        }
        
        .header h1 {
            font-size: 18pt;
            margin: 0 0 8px 0;
            color: ' . $colors['header_text'] . ';
            font-weight: bold;
        }
        
        .header h2 {
            font-size: 16pt;
            margin: 0;
            color: #e8f4fd;
            font-weight: normal;
        }
        
        .student-info {
            background-color: ' . $colors['row_alternate'] . ';
            color: #333;
            padding: 15px;
            border-radius: 0 0 8px 8px;
            margin-top: 10px;
        }
        
        .student-info h3 {
            font-size: 14pt;
            margin: 0 0 5px 0;
            color: ' . $colors['header_primary'] . ';
            font-weight: bold;
        }
        
        .student-info p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 11pt;
        }
        
        .grade-table {
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            background: white;
        }
        
        th, td {
            border: 1px solid ' . $colors['table_border'] . ';
            padding: 12px 15px;
            text-align: ' . (right_to_left() ? 'right' : 'left') . ';
            vertical-align: middle;
        }
        
        th {
            background: linear-gradient(135deg, ' . $colors['header_primary'] . ' 0%, ' . $colors['header_secondary'] . ' 100%);
            color: ' . $colors['header_text'] . ';
            font-weight: bold;
            font-size: 11pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .category-row td {
            background: linear-gradient(135deg, ' . $colors['category_primary'] . ' 0%, ' . $colors['category_secondary'] . ' 100%);
            color: white;
            font-weight: bold;
            font-size: 12pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .grade-row td {
            background-color: ' . $colors['row_alternate'] . ';
            border-left: 3px solid transparent;
        }
        
        .grade-row:nth-child(even) td {
            background-color: #ffffff;
        }
        
        .grade-row:hover td {
            background-color: ' . $colors['row_hover'] . ';
        }
        
        .grade-row.level-1 td {
            padding-left: ' . (right_to_left() ? '15px' : '30px') . ';
            padding-right: ' . (right_to_left() ? '30px' : '15px') . ';
            border-left-color: ' . $colors['header_primary'] . ';
        }
        
        .grade-row.level-2 td {
            padding-left: ' . (right_to_left() ? '15px' : '45px') . ';
            padding-right: ' . (right_to_left() ? '45px' : '15px') . ';
            border-left-color: ' . $colors['header_secondary'] . ';
        }
        
        .grade-value {
            text-align: center;
            font-weight: bold;
            color: ' . $colors['grade_value'] . ';
            background-color: ' . $colors['grade_value_bg'] . ' !important;
        }
        
        .grade-range {
            text-align: center;
            font-weight: normal;
            color: #6c757d;
            font-size: 10pt;
        }
        
        .grade-percentage {
            text-align: center;
            font-weight: bold;
            color: ' . $colors['percentage'] . ';
            background-color: ' . $colors['percentage_bg'] . ' !important;
        }
        
        .item-name {
            font-weight: 500;
            color: #333;
        }
        
        /* Total rows styling */
        .category-total-row td {
            background: linear-gradient(135deg, ' . $colors['category_total_primary'] . ' 0%, ' . $colors['category_total_secondary'] . ' 100%);
            color: white;
            font-weight: bold;
            font-size: 11pt;
            border-top: 2px solid ' . $colors['header_primary'] . ';
        }
        
        .course-total-row td {
            background: linear-gradient(135deg, ' . $colors['course_total_primary'] . ' 0%, ' . $colors['course_total_secondary'] . ' 100%);
            color: white;
            font-weight: bold;
            font-size: 12pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-top: 3px solid ' . $colors['header_primary'] . ';
            border-bottom: 3px solid ' . $colors['header_primary'] . ';
        }
        
        .total-name {
            font-weight: bold !important;
        }
        
        .total-value, .total-percentage {
            font-weight: bold !important;
            text-align: center;
        }
        
        /* Grade value color coding */
        .grade-excellent { color: ' . $colors['grade_excellent'] . ' !important; }
        .grade-good { color: ' . $colors['grade_good'] . ' !important; }
        .grade-average { color: ' . $colors['grade_average'] . ' !important; }
        .grade-poor { color: ' . $colors['grade_poor'] . ' !important; }
        
        @media print {
            body { 
                margin: 15px; 
                background-color: white;
            }
            .header { 
                page-break-after: avoid;
                background: ' . $colors['header_primary'] . ' !important;
                -webkit-print-color-adjust: exact;
            }
            .category-row td {
                background: ' . $colors['category_primary'] . ' !important;
                -webkit-print-color-adjust: exact;
            }
            th {
                background: ' . $colors['header_primary'] . ' !important;
                -webkit-print-color-adjust: exact;
            }
        }
        ';
    }
    
    /**
     * Calculate category total grade
     *
     * @param grade_category $category
     * @param int $userid
     * @return array|null Category total information or null if cannot calculate
     */
    private function calculate_category_total($category, $userid) {
        global $CFG;
        
        // Get category grade item
        $category_item = grade_item::fetch(array('itemtype' => 'category', 'iteminstance' => $category->id, 'courseid' => $this->courseid));
        if (!$category_item) {
            return null;
        }
        
        // Get the grade
        $category_grade = grade_grade::fetch(array('itemid' => $category_item->id, 'userid' => $userid));
        if (!$category_grade || is_null($category_grade->finalgrade)) {
            return null;
        }
        
        return array(
            'value' => grade_format_gradevalue($category_grade->finalgrade, $category_item, true),
            'range' => grade_format_gradevalue($category_item->grademin, $category_item, true) . ' - ' . 
                      grade_format_gradevalue($category_item->grademax, $category_item, true),
            'percentage' => grade_format_gradevalue($category_grade->finalgrade, $category_item, true, GRADE_DISPLAY_TYPE_PERCENTAGE)
        );
    }
    
    /**
     * Get course total grade
     *
     * @param int $userid
     * @return array|null Course total information or null if cannot calculate
     */
    private function get_course_total($userid) {
        global $CFG;
        
        // Get course grade item
        $course_item = grade_item::fetch_course_item($this->courseid);
        if (!$course_item) {
            return null;
        }
        
        // Get the grade
        $course_grade = grade_grade::fetch(array('itemid' => $course_item->id, 'userid' => $userid));
        if (!$course_grade || is_null($course_grade->finalgrade)) {
            return null;
        }
        
        return array(
            'value' => grade_format_gradevalue($course_grade->finalgrade, $course_item, true),
            'range' => grade_format_gradevalue($course_item->grademin, $course_item, true) . ' - ' . 
                      grade_format_gradevalue($course_item->grademax, $course_item, true),
            'percentage' => grade_format_gradevalue($course_grade->finalgrade, $course_item, true, GRADE_DISPLAY_TYPE_PERCENTAGE)
        );
    }
    
    /**
     * Get site logo URL
     *
     * @return string|null Logo URL or null if not available
     */
    private function get_site_logo_url() {
        global $CFG, $OUTPUT;
        
        // First priority: Site-wide logo from Site administration > Appearance > Logos
        if (!empty($CFG->logo)) {
            return $CFG->wwwroot . '/pluginfile.php/1/core_admin/logo/0x200/' . $CFG->logo;
        }
        
        // Alternative method for site logo
        if (isset($CFG->custommenuitems) || method_exists($OUTPUT, 'get_logo_url')) {
            $logourl = $OUTPUT->get_logo_url();
            if ($logourl && !empty($logourl)) {
                return $logourl->out();
            }
        }
        
        // Try current theme logo settings
        $theme = theme_config::load();
        if (isset($theme->settings->logo) && !empty($theme->settings->logo)) {
            return $theme->setting_file_url('logo', 'logo');
        }
        
        // Fallback to default theme logo
        $default_theme = theme_config::load(theme_config::DEFAULT_THEME);
        if (isset($default_theme->settings->logo) && !empty($default_theme->settings->logo)) {
            return $default_theme->setting_file_url('logo', 'logo');
        }
        
        // Final fallback: favicon
        if (file_exists($CFG->dirroot . '/theme/boost/pix/favicon.ico')) {
            return $CFG->wwwroot . '/theme/boost/pix/favicon.ico';
        }
        
        return null;
    }
    
    /**
     * Generate HTML footer
     *
     * @return string HTML footer
     */
    private function get_html_footer() {
        return '</body></html>';
    }
}
