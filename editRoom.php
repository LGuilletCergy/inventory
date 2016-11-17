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
 * File : editroom.php
 * Page to add and edit a room
 *
 */



require_once('../../config.php');
require_once('room_form.php');
require_once('locallib.php');

global $DB, $OUTPUT, $PAGE, $USER;

// Check params.


$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$moduleid = required_param('moduleid', PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$buildingid = required_param('buildingid', PARAM_INT);
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
$PAGE->set_url('/mod/inventory/editroom.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'editmode' => $editmode, 'buildingid' => $buildingid));
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
$editurl = new moodle_url('/mod/inventory/editroom.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'editmode' => $editmode, 'buildingid' => $buildingid));;

$PAGE->navbar->add($inventory->name, new moodle_url('/mod/inventory/view.php', array('id' => $moduleid)));
$PAGE->navbar->add($DB->get_record('inventory_building', array('id' => $buildingid))->name, new moodle_url('/mod/inventory/listRooms.php', array('id' => $moduleid, 'building' => $buildingid)));

if($editmode == 0) {
    $PAGE->navbar->add(get_string('addroom', 'inventory'), $editurl);
} else {
    $PAGE->navbar->add(get_string('editroom', 'inventory'), $editurl);
}

$site = get_site();
echo $OUTPUT->header();
require_capability('mod/inventory:edit', $context);

// Form instanciation.
$mform = new room_form();
$formdata['blockid'] = $blockid;
$formdata['moduleid'] = $moduleid;
$formdata['courseid'] = $courseid;
$formdata['editmode'] = $editmode;
$formdata['buildingid'] = $buildingid;

if ($editmode == 1) {

    $formdata['id'] = $id;
    $currentrecord = $DB->get_record('inventory_room', array('id' => $id));
    $formdata['name'] = $currentrecord->name;

    if ($currentrecord->isamphi == "Oui") {

        $formdata['isamphi']  = 1;
    } else {

        $formdata['isamphi']  = 0;
    }
}

$mform->set_data($formdata);

// Three possible states
if ($mform->is_cancelled()) { // First scenario : the form has been canceled.
    if (!$moduleid) {
        $moduleid = 1;
    }
    $courseurl = new moodle_url('/mod/inventory/listRooms.php', array('id' => $moduleid, 'building' => $buildingid));
    redirect($courseurl);
} else if ($submitteddata = $mform->get_data()) { // Second scenario : the form was validated.

    $submitteddata->uploadedat = time();
    if ($USER->id) {
        $submitteddatta->uploaderid = $USER->id;
    }

    // Store submitted data into database.
    if ($submitteddata->id) {

        $finaldata['id'] = $submitteddata->id;
        $finaldata['name'] = $submitteddata->name;
        $finaldata['buildingid'] = $submitteddata->buildingid;
        $finaldata['isamphi'] = $submitteddata->isamphi;
        $fileid = $DB->update_record('inventory_room', $finaldata);
    } else {

        $finaldata['name'] = $submitteddata->name;
        $finaldata['buildingid'] = $submitteddata->buildingid;
        $finaldata['isamphi'] = $submitteddata->isamphi;

        $fileid = $DB->insert_record('inventory_room', $finaldata);
    }
    if (!$fileid) {

        print_error('databaseerror', 'inventory');
    } else {

        $courseurl = new moodle_url('/mod/inventory/listRooms.php', array('id' => $moduleid, 'building' => $buildingid, 'vartest' => $vartest));
        redirect($courseurl);
    }
}

$mform->display();

echo $OUTPUT->footer();
