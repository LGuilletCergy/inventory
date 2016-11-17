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
 * File : editbuilding.php
 * Page to add a building
 *
 */



require_once('../../config.php');
require_once('building_form.php');
require_once('locallib.php');

global $DB, $OUTPUT, $PAGE, $USER;

// Check params.


$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$moduleid = required_param('moduleid', PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$editmode = required_param('editmode', PARAM_INT);

// Check access.
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}
require_login($course);

$cm = get_coursemodule_from_id('inventory', $moduleid);
$inventory = $DB->get_record('inventory', array('id' => $cm->instance), '*', MUST_EXIST);

$context = context_module::instance($moduleid);
require_course_login($course, true, $cm);

// Header code.
$PAGE->set_url('/mod/inventory/editbuilding.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'editmode' => $editmode));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($course->fullname);

$options = empty($inventory->displayoptions) ? array() : unserialize($inventory->displayoptions);

if ($inpopup and $inventory->display == RESOURCELIB_DISPLAY_POPUP) {
    $PAGE->set_pagelayout('popup');
    $PAGE->set_title($course->shortname.': '.$inventory->name);
    $PAGE->set_heading($course->fullname);
} else {
    $PAGE->set_title($course->shortname.': '.$inventory->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($inventory);
}

// Navigation node.
$editurl = new moodle_url('/mod/inventory/editbuilding.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'editmode' => $editmode));

if($editmode == 0) {
    $PAGE->navbar->add(get_string('addbuilding', 'inventory'), $editurl);
} else {
    $PAGE->navbar->add(get_string('editbuilding', 'inventory'), $editurl);
}

$site = get_site();
echo $OUTPUT->header();
require_capability('mod/inventory:edit', $context);


// Form instanciation.
$mform = new building_form();
$formdata['blockid'] = $blockid;
$formdata['moduleid'] = $moduleid;
$formdata['courseid'] = $courseid;
$formdata['editmode'] = $editmode;

if ($editmode == 1) {

    $formdata['id'] = $id;
    $currentrecord = $DB->get_record('inventory_building', array('id' => $id));
    $formdata['name'] = $currentrecord->name;
    $formdata['city'] = $currentrecord->city;
    $formdata['department'] = $currentrecord->department;
    $formdata['address'] = $currentrecord->address;
    $formdata['phone'] = $currentrecord->phone;

    global $USER;
    $fs = get_file_storage();
    $usercontext = context_user::instance($USER->id);
    $contextmodule = context_module::instance($moduleid);

    $draftitemid = file_get_submitted_draft_itemid('image');

    file_prepare_draft_area($draftitemid, $contextmodule->id, 'mod_inventory', 'image', $id,
                            array('maxbytes' => 0, 'maxfiles' => 1));

    $formdata['image'] = $draftitemid;
}
$mform->set_data($formdata);

// Three possible states.
if ($mform->is_cancelled()) { // First scenario : the form has been canceled.
    if (!$moduleid) {
        $moduleid = 1;
    }
    $courseurl = new moodle_url('/mod/inventory/view.php', array('id' => $moduleid));
    redirect($courseurl);
} else if ($submitteddata = $mform->get_data()) { // Second scenario : the form was validated.

    $submitteddata->uploadedat = time();
    if ($USER->id) {
        $submitteddatta->uploaderid = $USER->id;
    }

    // Store submitted data into database.
    if ($submitteddata->id) {

        global $USER;
        $fs = get_file_storage();
        $usercontext = context_user::instance($USER->id);
        $contextmodule = context_module::instance($moduleid);

        $listimages = $fs->get_area_files($usercontext->id, 'user', 'draft', $submitteddata->image, 'id');

        foreach ($listimages as $image) {

            if ($image->get_filename()!= ".") {
                $imagename = $image->get_filename();
            }
        }

        $finaldata['id'] = $submitteddata->id;
        $finaldata['name'] = $submitteddata->name;
        $finaldata['city'] = $submitteddata->city;
        $finaldata['department'] = $submitteddata->department;
        $finaldata['address'] = $submitteddata->address;
        $finaldata['phone'] = $submitteddata->phone;
        $finaldata['imagename'] = $imagename;

        //Avant l'update_record, on récupère le nom de l'ancienne image et on delete l'URL

        $oldimagename = $currentrecord->imagename;

        // Prepare file record object
        $fileinfo = array(
            'component' => 'mod_inventory',
            'filearea' => 'image',     // usually = table name
            'itemid' => $key,               // usually = ID of row in table
            'contextid' => $contextmodule->id, // ID of context
            'filepath' => '/',           // any path beginning and ending in /
            'filename' => $oldimagename); // any filename

        // Get file
        $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

        // Delete it if it exists
        if ($file) {
            $file->delete();
        }

        $fileid = $DB->update_record('inventory_building', $finaldata);

        file_save_draft_area_files($submitteddata->image, $contextmodule->id, 'mod_inventory', 'image',
               $submitteddata->id, array('maxbytes' => 0, 'maxfiles' => 1));
    } else {

        global $USER;
        $fs = get_file_storage();
        $usercontext = context_user::instance($USER->id);
        $contextmodule = context_module::instance($moduleid);

        $listimages = $fs->get_area_files($usercontext->id, 'user', 'draft', $submitteddata->image, 'id');

        foreach ($listimages as $image) {

            if ($image->get_filename()!= ".") {
                $imagename = $image->get_filename();
            }
        }

        $finaldata['name'] = $submitteddata->name;
        $finaldata['city'] = $submitteddata->city;
        $finaldata['department'] = $submitteddata->department;
        $finaldata['address'] = $submitteddata->address;
        $finaldata['phone'] = $submitteddata->phone;
        $finaldata['imagename'] = $imagename;

        $fileid = $DB->insert_record('inventory_building', $finaldata);

        file_save_draft_area_files($submitteddata->image, $contextmodule->id, 'mod_inventory', 'image',
               $fileid, array('maxbytes' => 0, 'maxfiles' => 1));
    }
    if (!$fileid) {

        print_error('databaseerror', 'inventory');
    } else {

        $courseurl = new moodle_url('/mod/inventory/view.php', array('id' => $moduleid));
        redirect($courseurl);
    }
}

$mform->display();

echo $OUTPUT->footer();
