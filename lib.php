<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Initially developped for :
 * Université de Cergy-Pontoise
 * 33, boulevard du Port
 * 95011 Cergy-Pontoise cedex
 * FRANCE
 *
 * The inventory module is used to list the devices available in a room
 *
 * @package    mod_inventory
 * @author     Laurent Guillet
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 * File : lib.php
 * functions internal to mod_inventory
 *
 */

defined('MOODLE_INTERNAL') || die;

/**
 * List of features supported in Inventory module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function inventory_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;

        default:
            return null;
    }
}

/**
 * Returns all other caps used in module
 * @return array
 */
function inventory_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function inventory_reset_userdata($data) {
    return array();
}

/**
 * List the actions that correspond to a view of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = 'r' and edulevel = LEVEL_PARTICIPATING will
 *       be considered as view action.
 *
 * @return array
 */
function inventory_get_view_actions() {
    return array('view', 'view all');
}

/**
 * List the actions that correspond to a post of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = ('c' || 'u' || 'd') and edulevel = LEVEL_PARTICIPATING
 *       will be considered as post action.
 *
 * @return array
 */
function inventory_get_post_actions() {
    return array('update', 'add');
}

/**
 * Add inventory instance.
 * @param stdClass $data
 * @param mod_inventory_mod_form $mform
 * @return int new inventory instance id
 */
function inventory_add_instance($data, $mform = null) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $cmid = $data->coursemodule;

    $data->timemodified = time();
    $displayoptions = array();

    $data->id = $DB->insert_record('inventory', $data);

    // We need to use context now, so we need to make sure all needed info is already in db.
    $DB->set_field('course_modules', 'instance', $data->id, array('id' => $cmid));
    $context = context_module::instance($cmid);

    if ($mform and !empty($data->inventory['itemid'])) {
        $draftitemid = $data->inventory['itemid'];
        $data->content = file_save_draft_area_files($draftitemid, $context->id,
                'mod_inventory', 'content', 0, inventory_get_editor_options($context), $data->content);
        $DB->update_record('inventory', $data);
    }

    return $data->id;
}

/**
 * Update inventory instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function inventory_update_instance($data, $mform) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $cmid        = $data->coursemodule;

    $data->timemodified = time();
    $data->id           = $data->instance;
    $data->revision++;

    $DB->update_record('inventory', $data);

    $context = context_module::instance($cmid);

    return true;
}

/**
 * Delete inventory instance.
 * @param int $id
 * @return bool true
 */
function inventory_delete_instance($id) {
    global $DB;

    if (!$inventory = $DB->get_record('inventory', array('id' => $id))) {
        return false;
    }

    // Note: all context files are deleted automatically.

    $DB->delete_records('inventory', array('id' => $inventory->id));

    return true;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param stdClass $coursemodule
 * @return cached_cm_info Info to customise main inventory display
 */
function inventory_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    if (!$inventory = $DB->get_record('inventory', array('id' => $coursemodule->instance),
            'id, name, display, displayoptions, intro, introformat')) {
        return null;
    }

    $info = new cached_cm_info();
    $info->name = $inventory->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $info->content = format_module_intro('inventory', $inventory, $coursemodule->id, false);
    }

    if ($inventory->display != RESOURCELIB_DISPLAY_POPUP) {
        return $info;
    }

    $fullurl = "$CFG->wwwroot/mod/inventory/view.php?id=$coursemodule->id&amp;inpopup=1";
    $options = empty($inventory->displayoptions) ? array() : unserialize($inventory->displayoptions);
    $width  = empty($options['popupwidth']) ? 620 : $options['popupwidth'];
    $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
    $wh = "width=$width,height=$height,toolbar=no,location=no,"
            . "menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
    $info->onclick = "window.open('$fullurl', '', '$wh'); return false;";

    return $info;
}


/**
 * Lists all browsable file areas
 *
 * @package  mod_inventory
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @return array
 */
function inventory_get_file_areas($course, $cm, $context) {
    $areas = array();
    $areas['content'] = get_string('content', 'inventory');
    $areas['image'] = get_string('image', 'inventory');
    $areas['manuel'] = get_string('manuel', 'inventory');
    $areas['manuelreference'] = get_string('manuelreference', 'inventory');
    $areas['icon'] = get_string('icon', 'inventory');
    $areas['publicattachment'] = get_string('publicattachment', 'inventory');
    $areas['privateattachment'] = get_string('privateattachment', 'inventory');
    return $areas;
}

/**
 * File browsing support for inventory module content area.
 *
 * @package  mod_inventory
 * @category files
 * @param stdClass $browser file browser instance
 * @param stdClass $areas file areas
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param int $itemid item ID
 * @param string $filepath file path
 * @param string $filename file name
 * @return file_info instance or null if not found
 */
function inventory_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG;

    if (!has_capability('moodle/course:managefiles', $context)) {
        // Students can not peak here!
        return null;
    }

    $fs = get_file_storage();

    if ($filearea === 'content' || $filearea === 'image' || $filearea === 'manuel' ||
            $filearea === 'manuelreference' || $filearea === 'icon' ||
            $filearea === 'publicattachment' || $filearea === 'privateattachment') {
        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        if (!$storedfile = $fs->get_file($context->id, 'mod_inventory', 'content', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_inventory', 'content', 0);
            } else {
                // Not found.
                return null;
            }
        }
        require_once("$CFG->dirroot/mod/inventory/locallib.php");
        return new inventory_content_file_info($browser, $context, $storedfile,
                $urlbase, $areas[$filearea], true, true, true, false);
    }

    // Note: inventory_intro handled in file_browser automatically.

    return null;
}

/**
 * Serves the inventory files.
 *
 * @package  mod_inventory
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function inventory_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    // All files require at least 'newview' capability.

    require_course_login($course, true, $cm);
    if (!has_capability('mod/inventory:newview', $context)) {
        return false;
    }

    if ($filearea !== 'content' && $filearea !== 'image' && $filearea !== 'manuel' &&
            $filearea !== 'manuelreference' && $filearea !== 'icon' &&
            $filearea !== 'publicattachment' && $filearea !== 'privateattachment') {
        // Intro is handled automatically in pluginfile.php.
        return false;
    }

    if ($filearea == 'content') {

        // ... $arg could be revision number or index.html.
        $arg = array_shift($args);
        if ($arg == 'index.html' || $arg == 'index.htm') {
            // Serve inventory content.
            $filename = $arg;

            if (!$inventory = $DB->get_record('inventory', array('id' => $cm->instance), '*', MUST_EXIST)) {
                return false;
            }

            // Remove @@PLUGINFILE@@/.
            $content = str_replace('@@PLUGINFILE@@/', '', $inventory->content);

            $formatoptions = new stdClass;
            $formatoptions->noclean = true;
            $formatoptions->overflowdiv = true;
            $formatoptions->context = $context;
            $content = format_text($content, $inventory->contentformat, $formatoptions);

            send_file($content, $filename, 0, 0, true, true);
        } else {
            $fs = get_file_storage();
            $relativepath = implode('/', $args);
            $fullpath = "/$context->id/mod_inventory/$filearea/0/$relativepath";
            if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
                $inventory = $DB->get_record('inventory', array('id' => $cm->instance), 'id, legacyfiles', MUST_EXIST);
                if ($inventory->legacyfiles != RESOURCELIB_LEGACYFILES_ACTIVE) {
                    return false;
                }
                if (!$file = resourcelib_try_file_migration('/'.$relativepath,
                        $cm->id, $cm->course, 'mod_inventory', 'content', 0)) {
                    return false;
                }
                // File migrate - update flag.
                $inventory->legacyfileslast = time();
                $DB->update_record('inventory', $inventory);
            }

            // Finally send the file.
            send_stored_file($file, null, 0, $forcedownload, $options);
        }
    } else if ($filearea == 'image' || $filearea == 'manuel' || $filearea == 'manuelreference' ||
            $filearea == 'icon' || $filearea == 'publicattachment' || $filearea == 'privateattachment') {

        // Private attachments require the edit capability.

        if ($filearea == 'privateattachment' && !has_capability('mod/inventory:edit', $context)) {

            return false;
        }

        // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
        $itemid = array_shift($args); // The first item in the $args array.

        // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
        // user really does have access to the file in question.

        // Extract the filename / filepath from the $args array.
        $filename = array_pop($args); // The last item in the $args array.
        if (!$args) {
            $filepath = '/'; // ...$args is empty => the path is '/'.
        } else {
            $filepath = '/'.implode('/', $args).'/'; // ...$args contains elements of the filepath.
        }

        // Retrieve the file from the Files API.
        $fs = get_file_storage();
        $file = $fs->get_file($context->id, 'mod_inventory', $filearea, $itemid, $filepath, $filename);
        if (!$file) {
            return false; // The file does not exist.
        }

        send_stored_file($file, null, 0, $forcedownload, $options);
    }
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function inventory_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $modulepagetype = array('mod-inventory-*' => get_string('page-mod-inventory-x', 'inventory'));
    return $modulepagetype;
}

/**
 * Export inventory resource contents
 *
 * @return array of file content
 */
function inventory_export_contents($cm, $baseurl) {
    global $CFG, $DB;
    $contents = array();
    $context = context_module::instance($cm->id);

    $inventory = $DB->get_record('inventory', array('id' => $cm->instance), '*', MUST_EXIST);

    // Inventory contents.
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_inventory', 'content', 0, 'sortorder DESC, id ASC', false);
    foreach ($files as $fileinfo) {
        $file = array();
        $file['type']         = 'file';
        $file['filename']     = $fileinfo->get_filename();
        $file['filepath']     = $fileinfo->get_filepath();
        $file['filesize']     = $fileinfo->get_filesize();
        $file['fileurl']      = file_encode_url("$CFG->wwwroot/" . $baseurl,
                '/'.$context->id.'/mod_inventory'
                . '/content/'.$inventory->revision.$fileinfo->get_filepath().$fileinfo->get_filename(), true);
        $file['timecreated']  = $fileinfo->get_timecreated();
        $file['timemodified'] = $fileinfo->get_timemodified();
        $file['sortorder']    = $fileinfo->get_sortorder();
        $file['userid']       = $fileinfo->get_userid();
        $file['author']       = $fileinfo->get_author();
        $file['license']      = $fileinfo->get_license();
        $contents[] = $file;
    }

    // Page html content.
    $filename = 'index.html';
    $pagefile = array();
    $pagefile['type']         = 'file';
    $pagefile['filename']     = $filename;
    $pagefile['filepath']     = '/';
    $pagefile['filesize']     = 0;
    $pagefile['fileurl']      = file_encode_url("$CFG->wwwroot/" . $baseurl,
            '/'.$context->id.'/mod_inventory/content/' . $filename, true);
    $pagefile['timecreated']  = null;
    $pagefile['timemodified'] = $inventory->timemodified;
    // Make this file as main file.
    $pagefile['sortorder']    = 1;
    $pagefile['userid']       = null;
    $pagefile['author']       = null;
    $pagefile['license']      = null;
    $contents[] = $pagefile;

    return $contents;
}

/**
 * Register the ability to handle drag and drop file uploads
 * @return array containing details of the files / types the mod can handle
 */
function inventory_dndupload_register() {
    return array('types' => array(
                     array('identifier' => 'text/html', 'message' => get_string('createinventory', 'inventory')),
                     array('identifier' => 'text', 'message' => get_string('createinventory', 'inventory'))
                 ));
}

/**
 * Handle a file that has been uploaded
 * @param object $uploadinfo details of the file / content that has been uploaded
 * @return int instance id of the newly created mod
 */
function inventory_dndupload_handle($uploadinfo) {
    // Gather the required info.
    $data = new stdClass();
    $data->course = $uploadinfo->course->id;
    $data->name = $uploadinfo->displayname;
    $data->intro = '<p>'.$uploadinfo->displayname.'</p>';
    $data->introformat = FORMAT_HTML;
    if ($uploadinfo->type == 'text/html') {
        $data->contentformat = FORMAT_HTML;
        $data->content = clean_param($uploadinfo->content, PARAM_CLEANHTML);
    } else {
        $data->contentformat = FORMAT_PLAIN;
        $data->content = clean_param($uploadinfo->content, PARAM_TEXT);
    }
    $data->coursemodule = $uploadinfo->coursemodule;

     // Set the display options to the site defaults.
    $config = get_config('inventory');
    $data->display = $config->display;
    $data->popupheight = $config->popupheight;
    $data->popupwidth = $config->popupwidth;
    $data->printheading = $config->printheading;
    $data->printintro = $config->printintro;

    return inventory_add_instance($data, null);
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $inventory       inventory object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.0
 */
function inventory_view($inventory, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $inventory->id
    );

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}
