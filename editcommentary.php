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
 * File : editcommentary.php
 * Page to add and edit a commentary of a room
 *
 */




require_once('../../config.php');
require_once('commentary_form.php');
require_once('locallib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->dirroot.'/mod/inventory/lib.php');

global $DB, $OUTPUT, $PAGE, $USER;

// Check params.


$id      = optional_param('id', 0, PARAM_INT); // Course Module ID.
$p       = optional_param('p', 0, PARAM_INT);  // Page instance ID.
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);
$room    = required_param('room', PARAM_INT);
$mode    = required_param('mode', PARAM_TEXT);

if ($p) {
    if (!$inventory = $DB->get_record('inventory', array('id' => $p))) {
        print_error('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('inventory', $inventory->id, $inventory->course, false, MUST_EXIST);

} else {
    if (!$cm = get_coursemodule_from_id('inventory', $id)) {
        print_error('invalidcoursemodule');
    }
    $inventory = $DB->get_record('inventory', array('id' => $cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);

// Header code.
$PAGE->set_url('/mod/inventory/editcommentary.php',
        array('id' => $id, 'p' => $p, 'inpopup' => $inpopup, 'room' => $room, 'mode' => $mode));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($course->fullname);

// Navigation node.
$editurl = new moodle_url('/mod/inventory/editcommentary.php',
        array('id' => $id, 'p' => $p, 'inpopup' => $inpopup, 'room' => $room, 'mode' => $mode));

// Get buildind and room id.
$currentrecord = $DB->get_record('inventory_room', array('id' => $room));
$currentbuilding = $DB->get_record('inventory_building', array('id' => $currentrecord->buildingid));

$PAGE->navbar->add($currentbuilding->name, new moodle_url('/mod/inventory/listRooms.php',
        array('id' => $id, 'building' => $currentbuilding->id)));
$PAGE->navbar->add($currentrecord->name, new moodle_url('/mod/inventory/listDevices.php',
        array('id' => $id, 'room' => $room)));
$PAGE->navbar->add(get_string('editcommentary', 'inventory'), $editurl);

require_capability('mod/inventory:edit', $context);

$options = empty($inventory->displayoptions) ? array() : unserialize($inventory->displayoptions);

if (!empty($options['printintro'])) {
    if (trim(strip_tags($inventory->intro))) {
        echo $OUTPUT->box_start('mod_introbox', 'inventoryintro');
        echo format_module_intro('inventory', $inventory, $cm->id);
        echo $OUTPUT->box_end();
    }
}

// Form instanciation.
$mform = new commentary_form();
$formdata['id'] = $id;
$formdata['p'] = $p;
$formdata['inpopup'] = $inpopup;
$formdata['room'] = $room;
$formdata['mode'] = $mode;

$currentroom = $DB->get_record('inventory_room', array('id' => $room));

if ($mode == "public") {

    $oldlongtext['text'] = $currentroom->publiccommentary;
} else {

    $oldlongtext['text'] = $currentroom->privatecommentary;
}

$formdata['commentary'] = $oldlongtext;

global $USER;
$fs = get_file_storage();
$usercontext = context_user::instance($USER->id);
$contextmodule = context_module::instance($id);
$draftitemid = file_get_submitted_draft_itemid('attachment');

if ($mode == "public") {

    file_prepare_draft_area($draftitemid, $contextmodule->id, 'mod_inventory', 'publicattachment', $room,
            array());

} else {

    file_prepare_draft_area($draftitemid, $contextmodule->id, 'mod_inventory', 'privateattachment', $room,
            array());
}

$formdata['attachment'] = $draftitemid;

$mform->set_data($formdata);

// Three possible states
if ($mform->is_cancelled()) { // First scenario : the form has been canceled.
    if (!$moduleid) {
        $moduleid = 1;
    }
    $courseurl = new moodle_url('/mod/inventory/listDevices.php', array('id' => $id, 'room' => $room));
    redirect($courseurl);
} else if ($submitteddata = $mform->get_data()) { // Second scenario : the form was validated.

    $submitteddata->uploadedat = time();
    if ($USER->id) {
        $submitteddatta->uploaderid = $USER->id;
    }

    // Store submitted data into database.

    $finaldata['id'] = $submitteddata->room;
    $finaldata['buildingid'] = $currentroom->buildingid;
    $finaldata['name'] = $currentroom->name;
    $finaldata['isamphi'] = $currentroom->isamphi;

    if ($mode == "public") {

        $submitteddataeditor = $submitteddata->commentary;

        $finaldata['publiccommentary'] = $submitteddataeditor['text'];
        $finaldata['privatecommentary'] = $currentroom->privatecommentary;

        $attachmentdata['isprivate'] = 0;
        $filearea = "publicattachment";

    } else {

        $submitteddataeditor = $submitteddata->commentary;

        $finaldata['privatecommentary'] = $submitteddataeditor['text'];
        $finaldata['publiccommentary'] = $currentroom->publiccommentary;

        $attachmentdata['isprivate'] = 1;
        $filearea = "privateattachment";
    }

    $fileid = $DB->update_record('inventory_room', $finaldata);

    $attachmentdata['roomid'] = $room;

    global $USER;
    $fs = get_file_storage();
    $usercontext = context_user::instance($USER->id);
    $contextmodule = context_module::instance($id);

    $listoldattachments = $DB->get_records('inventory_attachmentroom',
            array('roomid' => $room, 'isprivate' => $attachmentdata['isprivate']));
    $listattachment = $fs->get_area_files($usercontext->id, 'user', 'draft', $submitteddata->attachment, 'id');

    foreach ($listoldattachments as $oldattachment) {

        $oldattachementname = $oldattachment->name;

        $delete = 1;

        foreach ($listattachment as $attachment) {

            if ($attachment->get_filename() != ".") {
                $attachmentname = $attachment->get_filename();
            }

            if ($oldattachementname == $attachmentname) {

                $delete = 0;
            }
        }

        if ($delete == 1) {

            // Prepare file record object.
            $fileinfo = array(
                'component' => 'mod_inventory',
                'filearea' => $filearea,     // Usually = table name.
                'itemid' => $room,               // Usually = ID of row in table.
                'contextid' => $contextmodule->id, // ID of context.
                'filepath' => '/',           // Any path beginning and ending in /.
                'filename' => $oldattachementname); // Any filename.

            // Get file.
            $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                    $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

            // Delete it if it exists.
            if ($file) {
                $file->delete();
            }
        }

        $DB->delete_records('inventory_attachmentroom', array('id' => $oldattachment->id));
    }


    foreach ($listattachment as $attachment) {

        if ($attachment->get_filename() != ".") {
            $attachmentname = $attachment->get_filename();

            $attachmentdata['name'] = $attachmentname;

            file_save_draft_area_files($submitteddata->attachment, $contextmodule->id, 'mod_inventory', $filearea,
                   $room, array());

            if ($DB->record_exists('inventory_attachmentroom',
                    array('roomid' => $room, 'name' => $attachmentname, 'isprivate' => $attachmentdata->isprivate))) {

                $currentattachment = $DB->get_record('inventory_attachmentroom',
                        array('roomid' => $room, 'name' => $attachmentname, 'isprivate' => $attachmentdata->isprivate));

                $attachmentdata['id'] = $currentattachment->id;

                $DB->update_record('inventory_attachmentroom', $attachmentdata);
            } else {

                $DB->insert_record('inventory_attachmentroom', $attachmentdata);
            }
        }
    }

    if (!$fileid) {

        print_error('databaseerror', 'inventory');
    } else {

        $courseurl = new moodle_url('/mod/inventory/listDevices.php', array('id' => $id, 'room' => $room));
        redirect($courseurl);
    }
}

$site = get_site();
echo $OUTPUT->header();
$mform->display();

echo $OUTPUT->footer();
