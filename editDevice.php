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
 * File : editDevice.php
 * Page to add and edit a device
 *
 */




require_once('../../config.php');
require_once('device_form.php');
require_once('locallib.php');

global $DB, $OUTPUT, $PAGE, $USER;

// Check params.

$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$moduleid = required_param('moduleid', PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$roomid = required_param('roomid', PARAM_INT);
$editmode = required_param('editmode', PARAM_INT);
$categoryid = required_param('categoryid', PARAM_INT);

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
$PAGE->set_url('/mod/inventory/editDevice.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid,
    'moduleid' => $moduleid, 'editmode' => $editmode, 'roomid' => $roomid, 'categoryid' => $categoryid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($course->fullname);

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
$editurl = new moodle_url('/mod/inventory/editDevice.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid,
    'moduleid' => $moduleid, 'editmode' => $editmode, 'roomid' => $roomid, 'categoryid' => $categoryid));

// Get buildind and room id.
$currentrecord = $DB->get_record('inventory_room', array('id' => $roomid));
$currentbuilding = $DB->get_record('inventory_building', array('id' => $currentrecord->buildingid));

$PAGE->navbar->add($currentbuilding->name, new moodle_url('/mod/inventory/listRooms.php',
        array('id' => $moduleid, 'building' => $currentbuilding->id)));
$PAGE->navbar->add($currentrecord->name, new moodle_url('/mod/inventory/listDevices.php',
        array('id' => $moduleid, 'room' => $roomid)));

if ($editmode == 0) {
    $PAGE->navbar->add(get_string('adddevice', 'inventory'), $editurl);
} else {
    $PAGE->navbar->add(get_string('editdevice', 'inventory'), $editurl);
}
$site = get_site();
echo $OUTPUT->header();
require_capability('mod/inventory:edit', $context);

$brandid = 1;
$referenceid = 1;

if ($editmode == 1) {

    if ($DB->record_exists('inventory_device', array('id' => $id))) {

        $currentrecord = $DB->get_record('inventory_device', array('id' => $id));
        $currentreference = $DB->get_record('inventory_reference', array('id' => $currentrecord->refid));
        $brandid = $currentreference->brandid;
        $referenceid = $currentreference->id;
    } else {

        $courseurl = new moodle_url('/mod/inventory/listDevices.php', array('id' => $moduleid, 'room' => $roomid));
        redirect($courseurl);
    }
}


$mform = new device_form(null, array('categoryid' => $categoryid, 'brandid' => $brandid, 'blockid' => $blockid,
    'moduleid' => $moduleid, 'courseid' => $courseid, 'editmode' => $editmode, 'roomid' => $roomid,
    'id' => $id, 'referenceid' => $referenceid));
$formdata['blockid'] = $blockid;
$formdata['moduleid'] = $moduleid;
$formdata['courseid'] = $courseid;
$formdata['editmode'] = $editmode;
$formdata['roomid'] = $roomid;
$formdata['categoryid'] = $categoryid;
$formdata['type'] = $DB->get_record('inventory_devicecategory', array('id' => $categoryid))->name;
$formdata['referenceid'] = 1;

if ($editmode == 1) {

    $formdata['referenceid'] = $currentreference->id;
    $formdata['type'] = $DB->get_record('inventory_devicecategory', array('id' => $categoryid))->name;
    $formdata['id'] = $id;
    $formdata['reference'] = $currentreference->id;
    $formdata['brand'] = $brandid;

    $listfields = $DB->get_records('inventory_devicefield', array('categoryid' => $categoryid));

    foreach ($listfields as $field) {

        $currentvalue = $DB->get_record('inventory_devicevalue', array('deviceid' => $id, 'fieldid' => $field->id));

        if ($field->type == "longtext") {

            $oldlongtext['text'] = $currentvalue->value;
            $formfieldname = 'numerofield'.$field->id;
            $formdata[$formfieldname] = $oldlongtext;
        } else {

            $formfieldname = 'numerofield'.$field->id;
            $formdata[$formfieldname] = $currentvalue->value;
        }
    }

    global $USER;
    $fs = get_file_storage();
    $usercontext = context_user::instance($USER->id);
    $contextmodule = context_module::instance($moduleid);

    $draftitemid = file_get_submitted_draft_itemid('manuel');

    file_prepare_draft_area($draftitemid, $contextmodule->id, 'mod_inventory', 'manuel', $id,
                            array('maxbytes' => 0, 'maxfiles' => 1));

    $formdata['manuel'] = $draftitemid;

    if ($currentrecord->isworking == "Oui") {

        $formdata['isworking']  = 0;
    } else {

        $formdata['isworking']  = 1;
    }
}

$mform->set_data($formdata);

// Three possible states
if ($mform->is_cancelled()) { // First scenario : the form has been canceled.
    if (!$moduleid) {
        $moduleid = 1;
    }
    $courseurl = new moodle_url('/mod/inventory/listDevices.php', array('id' => $moduleid, 'room' => $roomid));
    redirect($courseurl);
} else if ($submitteddata = $mform->get_data()) { // Second scenario : the form was validated.

    $submitteddata->uploadedat = time();
    if ($USER->id) {
        $submitteddatta->uploaderid = $USER->id;
    }

    // Store submitted data into database.
    if ($submitteddata->id) {

        $devicedata['id'] = $submitteddata->id;
        $devicedata['roomid'] = $submitteddata->roomid;
        $devicedata['categoryid'] = $categoryid;
        $devicedata['refid'] = $submitteddata->referenceid;

        if ($submitteddata->isworking == 0) {

            $devicedata['isworking'] = "Oui";
        } else {

            $devicedata['isworking'] = "Non";
        }

        global $USER;
        $fs = get_file_storage();
        $usercontext = context_user::instance($USER->id);
        $contextmodule = context_module::instance($moduleid);

        $listmanuel = $fs->get_area_files($usercontext->id, 'user', 'draft', $submitteddata->manuel, 'id');

        foreach ($listmanuel as $manuel) {

            if ($manuel->get_filename() != ".") {
                $manuelname = $manuel->get_filename();
            }
        }

        $devicedata['documentation'] = $manuelname;

        // Before update_record, we retrieve the name of the old manual and we delete the url.

        $oldmanuelname = $currentrecord->documentation;

        // Prepare file record object.
        $fileinfo = array(
            'component' => 'mod_inventory',
            'filearea' => 'manuel',     // Usually = table name.
            'itemid' => $key,               // Usually = ID of row in table.
            'contextid' => $contextmodule->id, // ID of context.
            'filepath' => '/',           // Any path beginning and ending in /.
            'filename' => $oldmanuelname); // Any filename.

        // Get file.
        $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

        // Delete it if it exists.
        if ($file) {
            $file->delete();
        }

        $deviceid = $DB->update_record('inventory_device', $devicedata);

        file_save_draft_area_files($submitteddata->manuel, $contextmodule->id, 'mod_inventory', 'manuel',
               $submitteddata->id, array('maxbytes' => 0, 'maxfiles' => 1));

        $listfields = $DB->get_records('inventory_devicefield', array('categoryid' => $categoryid));

        $valuedata['deviceid'] = $submitteddata->id;

        foreach ($listfields as $field) {

            $fieldid = $field->id;

            $currentvalue = $DB->get_record('inventory_devicevalue',
                    array('fieldid' => $fieldid, 'deviceid' => $submitteddata->id));

            $valuedata['id'] = $currentvalue->id;
            $valuedata['fieldid'] = $fieldid;
            $numerofield = 'numerofield'.$field->id;

            if ($field->type == "longtext") {

                $submitteddatanumerofield = $submitteddata->$numerofield;

                $valuedata['value'] = $submitteddatanumerofield['text'];
            } else if ($field->type == "shorttext") {

                $valuedata['value'] = $submitteddata->$numerofield;
            }

            if ($DB->record_exists('inventory_devicevalue', array('id' => $currentvalue->id))) {

                $valueid = $DB->update_record('inventory_devicevalue', $valuedata);
            } else {

                $valueid = $DB->insert_record('inventory_devicevalue', $valuedata);
            }

            if (!$valueid) {

                print_error('databaseerror', 'inventory');
            }
        }
    } else {

        $devicedata['roomid'] = $submitteddata->roomid;
        $devicedata['categoryid'] = $categoryid;
        $devicedata['refid'] = $submitteddata->referenceid;


        global $USER;
        $fs = get_file_storage();
        $usercontext = context_user::instance($USER->id);
        $contextmodule = context_module::instance($moduleid);

        $listmanuel = $fs->get_area_files($usercontext->id, 'user', 'draft', $submitteddata->manuel, 'id');

        foreach ($listmanuel as $manuel) {

            if ($manuel->get_filename() != ".") {
                $manuelname = $manuel->get_filename();
            }
        }

        $devicedata['documentation'] = $manuelname;

        if ($submitteddata->isworking == 0) {

            $devicedata['isworking'] = "Oui";
        } else {

            $devicedata['isworking'] = "Non";
        }

        $deviceid = $DB->insert_record('inventory_device', $devicedata);

        file_save_draft_area_files($submitteddata->manuel, $contextmodule->id, 'mod_inventory', 'manuel',
               $deviceid, array('maxbytes' => 0, 'maxfiles' => 1));

        $listfields = $DB->get_records('inventory_devicefield', array('categoryid' => $categoryid));

        $valuedata['deviceid'] = $deviceid;

        foreach ($listfields as $field) {

            $fieldid = $field->id;

            $valuedata['fieldid'] = $fieldid;
            $numerofield = 'numerofield'.$field->id;

            if ($field->type == "longtext") {

                $submitteddatanumerofield = $submitteddata->$numerofield;

                $valuedata['value'] = $submitteddatanumerofield['text'];
            } else if ($field->type == "shorttext") {

                $valuedata['value'] = $submitteddata->$numerofield;
            }

            $valueid = $DB->insert_record('inventory_devicevalue', $valuedata);

            if (!$valueid) {

                print_error('databaseerror', 'inventory');
            }
        }
    }
    if (!$deviceid) {

        print_error('databaseerror', 'inventory');
    } else {

        $courseurl = new moodle_url('/mod/inventory/listDevices.php', array('id' => $moduleid, 'room' => $roomid));
        redirect($courseurl);
    }
}



$mform->display();

echo $OUTPUT->footer();
