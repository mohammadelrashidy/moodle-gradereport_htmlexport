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

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    
    // Color Settings Section
    $settings->add(new admin_setting_heading('gradereport_htmlexport/colorheading',
        get_string('colorsettings', 'gradereport_htmlexport'),
        get_string('colorsettingsdesc', 'gradereport_htmlexport')));
    
    // Header Colors
    $settings->add(new admin_setting_configcolourpicker('gradereport_htmlexport/header_primary_color',
        get_string('headerprimarycolor', 'gradereport_htmlexport'),
        get_string('headerprimarycolordesc', 'gradereport_htmlexport'),
        '#6f42c1'));
    
    $settings->add(new admin_setting_configcolourpicker('gradereport_htmlexport/header_secondary_color',
        get_string('headersecondarycolor', 'gradereport_htmlexport'),
        get_string('headersecondarycolordesc', 'gradereport_htmlexport'),
        '#8e44ad'));
    
    $settings->add(new admin_setting_configcolourpicker('gradereport_htmlexport/header_text_color',
        get_string('headertextcolor', 'gradereport_htmlexport'),
        get_string('headertextcolordesc', 'gradereport_htmlexport'),
        '#ffffff'));
    
    // Grade Performance Colors
    $settings->add(new admin_setting_configcolourpicker('gradereport_htmlexport/grade_excellent_color',
        get_string('gradeexcellentcolor', 'gradereport_htmlexport'),
        get_string('gradeexcellentcolordesc', 'gradereport_htmlexport'),
        '#28a745'));
    
    $settings->add(new admin_setting_configcolourpicker('gradereport_htmlexport/grade_good_color',
        get_string('gradegoodcolor', 'gradereport_htmlexport'),
        get_string('gradegoodcolordesc', 'gradereport_htmlexport'),
        '#17a2b8'));
    
    $settings->add(new admin_setting_configcolourpicker('gradereport_htmlexport/grade_average_color',
        get_string('gradeaveragecolor', 'gradereport_htmlexport'),
        get_string('gradeaveragecolordesc', 'gradereport_htmlexport'),
        '#ffc107'));
    
    $settings->add(new admin_setting_configcolourpicker('gradereport_htmlexport/grade_poor_color',
        get_string('gradepoorcolor', 'gradereport_htmlexport'),
        get_string('gradepoorcolordesc', 'gradereport_htmlexport'),
        '#dc3545'));
    
    // Table and Row Colors
    $settings->add(new admin_setting_configcolourpicker('gradereport_htmlexport/table_border_color',
        get_string('tablebordercolor', 'gradereport_htmlexport'),
        get_string('tablebordercolordesc', 'gradereport_htmlexport'),
        '#dee2e6'));
    
    $settings->add(new admin_setting_configcolourpicker('gradereport_htmlexport/row_alternate_color',
        get_string('rowalternatecolor', 'gradereport_htmlexport'),
        get_string('rowalternatecolordesc', 'gradereport_htmlexport'),
        '#f8f9fa'));
    
    $settings->add(new admin_setting_configcolourpicker('gradereport_htmlexport/row_hover_color',
        get_string('rowhovercolor', 'gradereport_htmlexport'),
        get_string('rowhovercolordesc', 'gradereport_htmlexport'),
        '#e8f4fd'));
    
    // Category Colors
    $settings->add(new admin_setting_configcolourpicker('gradereport_htmlexport/category_primary_color',
        get_string('categoryprimarycolor', 'gradereport_htmlexport'),
        get_string('categoryprimarycolordesc', 'gradereport_htmlexport'),
        '#4a4a4a'));
    
    $settings->add(new admin_setting_configcolourpicker('gradereport_htmlexport/category_secondary_color',
        get_string('categorysecondarycolor', 'gradereport_htmlexport'),
        get_string('categorysecondarycolordesc', 'gradereport_htmlexport'),
        '#2d2d2d'));
    
    // Total Row Colors
    $settings->add(new admin_setting_configcolourpicker('gradereport_htmlexport/category_total_primary_color',
        get_string('categorytotalprimarycolor', 'gradereport_htmlexport'),
        get_string('categorytotalprimarycolordesc', 'gradereport_htmlexport'),
        '#17a2b8'));
    
    $settings->add(new admin_setting_configcolourpicker('gradereport_htmlexport/category_total_secondary_color',
        get_string('categorytotalsecondarycolor', 'gradereport_htmlexport'),
        get_string('categorytotalsecondarycolordesc', 'gradereport_htmlexport'),
        '#138496'));
    
    $settings->add(new admin_setting_configcolourpicker('gradereport_htmlexport/course_total_primary_color',
        get_string('coursetotalprimarycolor', 'gradereport_htmlexport'),
        get_string('coursetotalprimarycolordesc', 'gradereport_htmlexport'),
        '#28a745'));
    
    $settings->add(new admin_setting_configcolourpicker('gradereport_htmlexport/course_total_secondary_color',
        get_string('coursetotalsecondarycolor', 'gradereport_htmlexport'),
        get_string('coursetotalsecondarycolordesc', 'gradereport_htmlexport'),
        '#1e7e34'));
    
    // Grade Value Display Colors
    $settings->add(new admin_setting_configcolourpicker('gradereport_htmlexport/grade_value_color',
        get_string('gradevaluecolor', 'gradereport_htmlexport'),
        get_string('gradevaluecolordesc', 'gradereport_htmlexport'),
        '#28a745'));
    
    $settings->add(new admin_setting_configcolourpicker('gradereport_htmlexport/grade_value_bg_color',
        get_string('gradevaluebgcolor', 'gradereport_htmlexport'),
        get_string('gradevaluebgcolordesc', 'gradereport_htmlexport'),
        '#f8fff9'));
    
    $settings->add(new admin_setting_configcolourpicker('gradereport_htmlexport/percentage_color',
        get_string('percentagecolor', 'gradereport_htmlexport'),
        get_string('percentagecolordesc', 'gradereport_htmlexport'),
        '#007bff'));
    
    $settings->add(new admin_setting_configcolourpicker('gradereport_htmlexport/percentage_bg_color',
        get_string('percentagebgcolor', 'gradereport_htmlexport'),
        get_string('percentagebgcolordesc', 'gradereport_htmlexport'),
        '#f8feff'));
    
    // Reset to defaults button (informational)
    $settings->add(new admin_setting_heading('gradereport_htmlexport/resetheading',
        get_string('resetcolorsheading', 'gradereport_htmlexport'),
        get_string('resetcolorsdesc', 'gradereport_htmlexport')));
}
