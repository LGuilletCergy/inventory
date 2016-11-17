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
 * Page to add and edit a reference
 *
 */

require_once('../../config.php');
require_once('reference_form.php');
require_once('locallib.php');

global $DB, $OUTPUT, $PAGE, $USER;

// Check params.

$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$moduleid = required_param('moduleid', PARAM_INT);
$idreference = optional_param('idreference', 0, PARAM_INT);
$editmodereference = required_param('editmodereference', PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$editmode = optional_param('editmode', 0, PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$roomid = optional_param('roomid', 0, PARAM_INT);


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
$PAGE->set_url('/mod/inventory/editreference.php', array('idreference' => $idreference, 'courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'editmodereference' => $editmodereference, 'roomid' => $roomid, 'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode));
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
$editurl = new moodle_url('/mod/inventory/editreference.php', array('idreference' => $idreference, 'courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'editmodereference' => $editmodereference, 'roomid' => $roomid, 'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode));

//Get buildind and room id
$currentroom = $DB->get_record('inventory_room', array('id' => $roomid));
$currentbuilding = $DB->get_record('inventory_building', array('id' => $currentroom->buildingid));

$PAGE->navbar->add($currentbuilding->name, new moodle_url('/mod/inventory/listRooms.php', array('id' => $moduleid, 'building' => $currentbuilding->id)));
$PAGE->navbar->add($currentroom->name, new moodle_url('/mod/inventory/listDevices.php', array('id' => $moduleid, 'room' => $roomid)));

if($editmode == 0) {
    $PAGE->navbar->add(get_string('adddevice', 'inventory'), new moodle_url('/mod/inventory/editDevice.php', array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'roomid' => $roomid, 'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode)));
} else {
    $PAGE->navbar->add(get_string('editdevice', 'inventory'), new moodle_url('/mod/inventory/editDevice.php', array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'roomid' => $roomid, 'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode)));
}

if($editmodereference == 0) {
    $PAGE->navbar->add(get_string('addreference', 'inventory'), $editurl);
} else {
    $PAGE->navbar->add(get_string('editreference', 'inventory'), $editurl);
}

require_capability('mod/inventory:edit', $context);

if ($editmodereference == 1) {

    if ($DB->record_exists('inventory_reference', array('id' => $idreference))) {

        $currentrecord = $DB->get_record('inventory_reference', array('id' => $idreference));
        $brandid = $currentrecord->brandid;
    } else {

        if (!$moduleid) {
            $moduleid = 1;
        }
        $courseurl = new moodle_url('/mod/inventory/editDevice.php', array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'roomid' => $roomid, 'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode));
        redirect($courseurl);
    }
}


$mform = new reference_form(null, array('categoryid' => $categoryid, 'brandid' => $brandid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'courseid' => $courseid, 'editmode' => $editmode, 'roomid' => $roomid, 'id' => $id, 'idreference' => $idreference, 'editmodereference' => $editmodereference));
$formdata['blockid'] = $blockid;
$formdata['moduleid'] = $moduleid;
$formdata['courseid'] = $courseid;
$formdata['editmodereference'] = $editmodereference;
$formdata['id'] = $id;
$formdata['editmode'] = $editmode;
$formdata['categoryid'] = $categoryid;
$formdata['roomid'] = $roomid;


if ($editmodereference == 1) {

    $formdata['idreference'] = $idreference;
    $formdata['id'] = $id;
    $formdata['name'] = $currentrecord->name;
    $formdata['brand'] = $brandid;

    $listrecordbrand =$DB->get_records('inventory_reference', array('brandid' => $brandid));

    foreach ($listrecordbrand as $recordkey => $recordvalue) {

        if ($recordkey == $currentrecord->id) {

            $firstelement = 1;
            break;
        } else {

            $firstelement = 0;
            break;
        }
    }

    if ($firstelement == 1) {

            $formdata->isfirstreference = 1;
    }

    global $USER;
    $fs = get_file_storage();
    $usercontext = context_user::instance($USER->id);
    $contextmodule = context_module::instance($moduleid);

    $draftitemid = file_get_submitted_draft_itemid('manuel');

    file_prepare_draft_area($draftitemid, $contextmodule->id, 'mod_inventory', 'manuelreference', $idreference,
                            array('maxbytes' => 0, 'maxfiles' => 1));

    $formdata['manuel'] = $draftitemid;
}

$mform->set_data($formdata);

// Three possible states
if ($mform->is_cancelled()) { // First scenario : the form has been canceled.
    if (!$moduleid) {
        $moduleid = 1;
    }
    $courseurl = new moodle_url('/mod/inventory/editDevice.php', array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'roomid' => $roomid, 'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode));
    redirect($courseurl);
} else if ($submitteddata = $mform->get_data()) { // Second scenario : the form was validated.

    $submitteddata->uploadedat = time();
    if ($USER->id) {
        $submitteddatta->uploaderid = $USER->id;
    }

    // Store submitted data into database.
    if ($submitteddata->idreference) {

        $referencedata['id'] = $submitteddata->idreference;
        $referencedata['name'] = $submitteddata->name;
        $referencedata['brandid'] = $submitteddata->brand;

        global $USER;
        $fs = get_file_storage();
        $usercontext = context_user::instance($USER->id);
        $contextmodule = context_module::instance($moduleid);

        $listmanuel = $fs->get_area_files($usercontext->id, 'user', 'draft', $submitteddata->manuel, 'id');

        foreach ($listmanuel as $manuel) {

            if ($manuel->get_filename()!= ".") {
                $manuelname = $manuel->get_filename();
            }
        }

        $referencedata['documentation'] = $manuelname;

        //Avant l'update_record, on récupère le nom de l'ancien manuel et on delete l'URL

        $oldmanuelname = $currentrecord->documentation;

        // Prepare file record object
        $fileinfo = array(
            'component' => 'mod_inventory',
            'filearea' => 'manuelreference',     // usually = table name
            'itemid' => $key,               // usually = ID of row in table
            'contextid' => $contextmodule->id, // ID of context
            'filepath' => '/',           // any path beginning and ending in /
            'filename' => $oldmanuelname); // any filename

        // Get file
        $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

        // Delete it if it exists
        if ($file) {
            $file->delete();
        }

        $referenceid = $DB->update_record('inventory_reference', $referencedata);

        file_save_draft_area_files($submitteddata->manuel, $contextmodule->id, 'mod_inventory', 'manuelreference',
               $submitteddata->idreference, array('maxbytes' => 0, 'maxfiles' => 1));
    } else {

        $referencedata['name'] = $submitteddata->name;
        $referencedata['brandid'] = $submitteddata->brand;

        global $USER;
        $fs = get_file_storage();
        $usercontext = context_user::instance($USER->id);
        $contextmodule = context_module::instance($moduleid);

        $listmanuel = $fs->get_area_files($usercontext->id, 'user', 'draft', $submitteddata->manuel, 'id');

        foreach ($listmanuel as $manuel) {

            if ($manuel->get_filename()!= ".") {
                $manuelname = $manuel->get_filename();
            }
        }

        $referencedata['documentation'] = $manuelname;

        $referenceid = $DB->insert_record('inventory_reference', $referencedata);

        file_save_draft_area_files($submitteddata->manuel, $contextmodule->id, 'mod_inventory', 'manuelreference',
               $referenceid, array('maxbytes' => 0, 'maxfiles' => 1));
    }
    if (!$referenceid) {

        print_error('databaseerror', 'inventory');
    } else {

        $courseurl = new moodle_url('/mod/inventory/editDevice.php', array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'roomid' => $roomid, 'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode));
        redirect($courseurl);
    }
}


$site = get_site();
echo $OUTPUT->header();

$stop = 0;

$listrecordbrands =$DB->get_records('inventory_reference', array('brandid' => $brandid));

foreach ($listrecordbrands as $recordkey => $recordvalue) {

    if ($recordkey == $idreference) {

        $stop = 1;
        break;
    } else {

        $stop = 0;
        break;
    }
}

if($stop == 1) {

    if ($source == "editdevice") {

        $courseurl = new moodle_url('/mod/inventory/editDevice.php', array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'roomid' => $roomid, 'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode));
    } else if ($source == "editreference") {

        $courseurl = new moodle_url('/mod/inventory/editreference.php', array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'roomid' => $roomid, 'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode, 'idreference' => $idreference, 'editmodereference' => $editmodereference));
    }
    else {

        $courseurl = new moodle_url('/mod/inventory/view.php', array('id' => $moduleid));

    }

    echo get_string('editerror', 'inventory');

    echo "
    <p>
        <a href=$courseurl><button>".get_string('redirect', 'inventory')."</button></a>
    </p>";
} else {

    $mform->display();
}

echo $OUTPUT->footer();
