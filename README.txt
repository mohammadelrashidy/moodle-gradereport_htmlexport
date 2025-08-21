 HTML Export Grade Report Plugin
================================

Version: 3.0.0
Release Date: August 2025
Compatibility: Moodle 4.0+

DESCRIPTION
-----------
The HTML Export Grade Report plugin allows teachers to export student grades as Word-compatible HTML files. The plugin provides hierarchical grade display grouped by categories, items, and users, just like the built-in Moodle gradebook. It respects grade visibility, hidden items, and grade display settings.

FEATURES
--------
* Export individual student grade reports as HTML files
* Bulk export all students' grades as ZIP file
* Hierarchical grade structure with categories and totals
* Site logo integration from admin settings
* Customizable color scheme (18+ color settings)
* Word-compatible HTML output for easy document processing
* RTL language support
* Grade visibility and permission checking
* Print-friendly CSS styling

INSTALLATION
------------
1. Download the plugin ZIP file
2. Extract to /path/to/moodle/grade/report/htmlexport/
3. Visit Site Administration > Notifications to complete installation
4. Configure color settings at Site Administration > Plugins > Grade Reports > HTML Export

PERMISSIONS
-----------
* gradereport/htmlexport:view - View and use the HTML export functionality
  - Assigned to: teachers, editing teachers, managers, students

RELEASE NOTES
-------------

Version 3.0.0 (August 2025)
----------------------------
* NEW: Comprehensive color customization system with 18+ configurable colors
* NEW: Admin settings page for color configuration
* NEW: Privacy API implementation for GDPR compliance
* IMPROVED: Robust error handling for sites without logos
* IMPROVED: Enhanced role/capability error handling
* IMPROVED: CSS namespacing to prevent conflicts
* IMPROVED: Proper file headers and copyright information
* FIX: Error handling when parent roles not configured
* FIX: Logo detection and fallback mechanisms

Version 2.1.1 (January 2025)
-----------------------------
* FIX: Logo detection prioritizing site-wide logo settings
* IMPROVED: Enhanced CSS with modern styling and better visual hierarchy

Version 2.1.0 (January 2025)
-----------------------------
* NEW: Site logo integration from Site administration settings
* NEW: Category totals and course totals with proper calculations
* NEW: Color-coded grade displays (green for values, blue for percentages)
* IMPROVED: Modern purple gradient styling matching Moodle themes
* IMPROVED: Enhanced CSS with rounded corners, shadows, and print-friendly design

Version 2.0.0 (January 2025)
-----------------------------
* NEW: Bulk download functionality - export all students as ZIP file
* NEW: Progress confirmation dialog for large courses
* IMPROVED: Enhanced error handling for missing grades and permissions
* IMPROVED: Teacher-only bulk capability with proper permission checks

Version 1.0.0 (January 2025)
-----------------------------
* Initial release
* Individual student grade export as HTML files
* Hierarchical grade structure with categories
* Grade visibility and permission checking
* Word-compatible HTML output
* RTL language support

SUPPORT
-------
For support and bug reports, please use the Moodle plugins directory or contact the plugin maintainer.

PRIVACY
-------
This plugin does not store any personal data. It only provides functionality to export grade data that is already stored by Moodle's core grade system. Exported HTML files are generated on-demand and not persistently stored.

LICENSE
-------
GNU GPL v3 or later - http://www.gnu.org/copyleft/gpl.html
