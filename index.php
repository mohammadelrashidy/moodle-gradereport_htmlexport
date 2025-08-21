<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin version and other meta-data are defined here.
 *
 * @package     gradeexport_htmlexport
 * @copyright   2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot.'/grade/querylib.php');
require_once($CFG->dirroot.'/grade/report/htmlexport/classes/report.php');

$courseid = required_param('id', PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$bulk = optional_param('bulk', 0, PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
require_login($course);

$context = context_course::instance($course->id);
require_capability('gradereport/htmlexport:view', $context);

$PAGE->set_url('/grade/report/htmlexport/index.php', array('id' => $courseid));
$PAGE->set_title(get_string('pluginname', 'gradereport_htmlexport'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('report');

// Create the report instance
$report = new gradereport_htmlexport_report($courseid, $context);

// Handle export actions
if ($action === 'export' && $userid) {
    $report->export_student_grades($userid);
    exit;
} elseif ($action === 'export' && $bulk) {
    $report->export_all_students_bulk();
    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'gradereport_htmlexport'));

// Display student selection form
echo $report->display_student_selection_form();

echo $OUTPUT->footer();
