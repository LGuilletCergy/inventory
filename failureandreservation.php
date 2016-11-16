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
 * File : failureandreservation.php
 * Display all informations about the device and tell the user where he should give them
 *
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/inventory/lib.php');
require_once($CFG->dirroot.'/mod/inventory/locallib.php');
require_once($CFG->libdir.'/completionlib.php');

$id      = optional_param('id', 0, PARAM_INT); // Course Module ID.
$p       = optional_param('p', 0, PARAM_INT);  // Page instance ID.
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);
$key     = required_param('key', PARAM_INT);
$mode     = required_param('mode', PARAM_TEXT);

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


// Completion and trigger events.
inventory_view($inventory, $course, $cm, $context);

$PAGE->set_url('/mod/inventory/failureandreservation.php', array('id' => $id, 'key' => $key, 'mode' => $reservation));

//Get buildind and room id
$currentdevice = $DB->get_record('inventory_device', array('id' => $key));
$currentroom = $DB->get_record('inventory_room', array('id' => $currentdevice->roomid));
$currentbuilding = $DB->get_record('inventory_building', array('id' => $currentroom->buildingid));

$PAGE->navbar->add($currentbuilding->name, new moodle_url('/mod/inventory/listRooms.php', array('id' => $id, 'building' => $currentbuilding->id)));
$PAGE->navbar->add($currentroom->name, new moodle_url('/mod/inventory/listDevices.php', array('id' => $id, 'room' => $currentroom->id)));

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
echo $OUTPUT->header();
if (!isset($options['printheading']) || !empty($options['printheading'])) {
    echo $OUTPUT->heading(get_string('reportfailure', 'inventory'));
}

if (!empty($options['printintro'])) {
    if (trim(strip_tags($inventory->intro))) {
        echo $OUTPUT->box_start('mod_introbox', 'inventoryintro');
        echo format_module_intro('inventory', $inventory, $cm->id);
        echo $OUTPUT->box_end();
    }
}

$content = file_rewrite_pluginfile_urls($inventory->content, 'pluginfile.php', $context->id, 'mod_inventory', 'content', $inventory->revision);
$formatoptions = new stdClass;
$formatoptions->noclean = true;
$formatoptions->overflowdiv = true;
$formatoptions->context = $context;
$content = format_text($content, $inventory->contentformat, $formatoptions);
echo $OUTPUT->box($content, "generalbox center clearfix");

require_capability('mod/inventory:reportfailure', $context);

$currentcategory = $DB->get_record('inventory_devicecategory', array('id' => $currentdevice->categoryid));
$currentreference = $DB->get_record('inventory_reference', array('id' => $currentdevice->refid));
$currentbrand = $DB->get_record('inventory_brand', array('id' => $currentreference->brandid));


echo "<p>".get_string('failureandreservationintro', 'inventory')."</p>";
echo "<p>".get_string('devicetype', 'inventory')." : ".$currentcategory->name."</p>";
echo "<p>".get_string('buildingname', 'inventory')." : ".$currentbuilding->name."</p>";
echo "<p>".get_string('roomname', 'inventory')." : ".$currentroom->name."</p>";
echo "<p>".get_string('reference', 'inventory')." : ".$currentreference->name."</p>";


if($mode == "failure") {

    $newdevice = $currentdevice;
    $newdevice->isworking = "Non";

    $DB->update_record('inventory_device', $newdevice);

    echo "<p>".get_string('iddevice', 'inventory')." : ".$currentdevice->id."</p>";
    echo "<p>".get_string('brand', 'inventory')." : ".$currentbrand->name."</p>";

    $listfields = $DB->get_records('inventory_devicefield', array('categoryid' => $currentdevice->categoryid));

    foreach ($listfields as $fieldkey => $fieldvalue) {

        $currentvalue = $DB->get_record('inventory_devicevalue', array('fieldid' => $fieldkey, 'deviceid' => $key));

        echo "<p>".$fieldvalue->name." : ".$currentvalue->value."</p>";
    }

    echo $currentcategory->textforfailure;

    echo "<p><a href='$currentcategory->linkforfailure'>".$currentcategory->linkforfailure."</a></p>";

} else if ($mode == "reservation") {

    echo $currentcategory->textforreservation;

    echo "<p><a href='$currentcategory->linkforreservation'>".$currentcategory->linkforreservation."</a></p>";
} else {

    echo "Vous ne voyez jamais ça";
}

$strlastmodified = get_string("lastmodified");
echo "<div class=\"modified\">$strlastmodified: ".userdate($inventory->timemodified)."</div>";

echo $OUTPUT->footer();

