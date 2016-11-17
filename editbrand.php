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
 * File : editbrand.php
 * Page to add and edit a brand
 *
 */

require_once('../../config.php');
require_once('brand_form.php');
require_once('locallib.php');

global $DB, $OUTPUT, $PAGE, $USER;

// Check params.

$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$moduleid = required_param('moduleid', PARAM_INT);
$editmodebrand = required_param('editmodebrand', PARAM_INT);
$idbrand = optional_param('idbrand', 0, PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$editmode = optional_param('editmode', 0, PARAM_INT);
$categoryid = required_param('categoryid', PARAM_INT);
$roomid = optional_param('roomid', 0, PARAM_INT);
$source = required_param('source', PARAM_TEXT);
$idreference = optional_param('idreference', 0, PARAM_INT);
$editmodereference = optional_param('editmodereference', 0, PARAM_INT);

// Check access.
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}
require_login($course);

$cm = get_coursemodule_from_id('inventory', $moduleid);
$inventory = $DB->get_record('inventory', array('id' => $cm->instance), '*', MUST_EXIST);

$context = context_module::instance($moduleid);



// Header code.
$PAGE->set_url('/mod/inventory/editbrand.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'editmode' => $editmode, 'roomid' => $roomid, 'categoryid' => $categoryid));
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
$editurl = new moodle_url('/mod/inventory/editDevice.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'editmode' => $editmode, 'roomid' => $roomid, 'categoryid' => $categoryid));

$PAGE->navbar->add($inventory->name, new moodle_url('/mod/inventory/view.php', array('id' => $moduleid)));

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

if ($source == "editreference") {
    
    if($editmodereference == 0) {
    $PAGE->navbar->add(get_string('addreference', 'inventory'), new moodle_url('/mod/inventory/editreference.php', array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'roomid' => $roomid, 'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode, 'idreference' => $idreference, 'editmodereference' => $editmodereference)));
    } else {
        $PAGE->navbar->add(get_string('editreference', 'inventory'), new moodle_url('/mod/inventory/editreference.php', array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'roomid' => $roomid, 'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode, 'idreference' => $idreference, 'editmodereference' => $editmodereference)));
    }
}

if($editmodebrand == 0) {
    $PAGE->navbar->add(get_string('addbrand', 'inventory'), $editurl);
} else {
    $PAGE->navbar->add(get_string('editbrand', 'inventory'), $editurl);
}

require_capability('mod/inventory:edit', $context);

if ($editmodebrand == 1) {

    $currentrecord = $DB->get_record('inventory_brand', array('id' => $idbrand));
}

$mform = new brand_form();
$formdata['blockid'] = $blockid;
$formdata['moduleid'] = $moduleid;
$formdata['courseid'] = $courseid;
$formdata['idbrand'] = $idbrand;
$formdata['editmodebrand'] = $editmodebrand;
$formdata['id'] = $id;
$formdata['editmode'] = $editmode;
$formdata['categoryid'] = $categoryid;
$formdata['roomid'] = $roomid;
$formdata['source'] = $source;
$formdata['idreference'] = $idreference;
$formdata['editmodereference'] = $editmodereference;

if ($editmodebrand == 1) {

    $formdata['idbrand'] = $currentrecord->id;
    $formdata['name'] = $currentrecord->name;
    $formdata['contact'] = $currentrecord->contact;
}

$mform->set_data($formdata);


// Three possible states
if ($mform->is_cancelled()) { // First scenario : the form has been canceled.
    if (!$moduleid) {
        $moduleid = 1;
    }

    if ($source == "editdevice") {
        
        $courseurl = new moodle_url('/mod/inventory/editDevice.php', array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'roomid' => $roomid, 'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode));
        redirect($courseurl);
    } else if ($source == "editreference") {

        $courseurl = new moodle_url('/mod/inventory/editreference.php', array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'roomid' => $roomid, 'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode, 'idreference' => $idreference, 'editmodereference' => $editmodereference));
        redirect($courseurl);
    }
    else {

        $courseurl = new moodle_url('/mod/inventory/view.php', array('id' => $moduleid));
        redirect($courseurl);
    }
} else if ($submitteddata = $mform->get_data()) { // Second scenario : the form was validated.

    $submitteddata->uploadedat = time();
    if ($USER->id) {
        $submitteddatta->uploaderid = $USER->id;
    }

    // Store submitted data into database.
    if ($editmodebrand) {

        $branddata['id'] = $submitteddata->idbrand;
        $branddata['name'] = $submitteddata->name;
        $branddata['contact'] = $submitteddata->contact;
        
        $brandid = $DB->update_record('inventory_brand', $branddata);
    } else {

        $branddata['name'] = $submitteddata->name;
        $branddata['contact'] = $submitteddata->contact;

        $brandid = $DB->insert_record('inventory_brand', $branddata);

        //Add undefined reference to this brand

        $newreference['name'] = "undefined";
        $newreference['brandid'] = $brandid;

        $refid = $DB->insert_record('inventory_reference', $newreference);

        if (!$refid) {

            print_error('databaseerror', 'inventory');
        }

    }

    if (!$brandid) {

        print_error('databaseerror', 'inventory');
    } else {

        if ($source == "editdevice") {

            $courseurl = new moodle_url('/mod/inventory/editDevice.php', array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'roomid' => $roomid, 'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode));
            redirect($courseurl);
        } else if ($source == "editreference") {

            $courseurl = new moodle_url('/mod/inventory/editreference.php', array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'roomid' => $roomid, 'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode, 'idreference' => $idreference, 'editmodereference' => $editmodereference));
            redirect($courseurl);
        } else {

            $courseurl = new moodle_url('/mod/inventory/view.php', array('id' => $moduleid));
            redirect($courseurl);
        }
    }
}


$site = get_site();
echo $OUTPUT->header();

$stop = 0;

$listrecordcategories =$DB->get_records('inventory_brand', array('categoryid' => $categoryid));

foreach ($listrecordcategories as $recordkey => $recordvalue) {

    if ($recordkey == $idbrand) {

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
