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
 * File : listrooms.php
 * List all rooms and allow interaction
 *
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/inventory/lib.php');
require_once($CFG->dirroot.'/mod/inventory/locallib.php');
require_once($CFG->libdir.'/completionlib.php');

$id      = optional_param('id', 0, PARAM_INT); // Course Module ID.
$p       = optional_param('p', 0, PARAM_INT);  // Page instance ID.
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);
$building = required_param('building', PARAM_INT);
$categoryselected = optional_param('categoryselected', 0, PARAM_INT);
$export = optional_param('export', false, PARAM_BOOL);

// If we are not in export mode, we display the page.

if ($export != true) {

    echo '<head>'
    . '<meta charset="utf-8" />'
            . '<link rel="stylesheet" href="listroomsstyle.css" />'
            . '</head>';

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

    require_capability('mod/inventory:newview', $context);

    // Completion and trigger events.
    inventory_view($inventory, $course, $cm, $context);

    $PAGE->set_url('/mod/inventory/listrooms.php',
            array('id' => $id, 'building' => $building, 'categoryselected' => $categoryselected));

    $options = empty($inventory->displayoptions) ? array() : unserialize($inventory->displayoptions);

    if ($inpopup and $inventory->display == RESOURCELIB_DISPLAY_POPUP) {
        $PAGE->set_pagelayout('popup');
    } else {
        $PAGE->set_activity_record($inventory);
    }
    $PAGE->set_title($course->shortname.': '.$inventory->name);
    $PAGE->set_heading($course->fullname);

    // If we are in all buildings, the navbar will redirect us there, otherwise, it will redirect us in the current building.

    if ($building != 0) {

        $PAGE->navbar->add($DB->get_record('inventory_building',
                array('id' => $building))->name,
                new moodle_url('/mod/inventory/listrooms.php',
                        array('id' => $id, 'building' => $building, 'categoryselected' => $categoryselected)));
    } else {

        $PAGE->navbar->add(get_string('allbuildings', 'inventory'),
                new moodle_url('/mod/inventory/listrooms.php',
                        array('id' => $id, 'building' => $building, 'categoryselected' => $categoryselected)));
    }

    echo $OUTPUT->header();
    if (!isset($options['printheading']) || !empty($options['printheading'])) {
        echo $OUTPUT->heading(format_string($DB->get_record('inventory_building', array('id' => $building))->name), 2);
    }

    if (!empty($options['printintro'])) {
        if (trim(strip_tags($inventory->intro))) {
            echo $OUTPUT->box_start('mod_introbox', 'inventoryintro');
            echo format_module_intro('inventory', $inventory, $cm->id);
            echo $OUTPUT->box_end();
        }
    }

    $content = file_rewrite_pluginfile_urls($inventory->content,
            'pluginfile.php', $context->id, 'mod_inventory', 'content', $inventory->revision);
    $formatoptions = new stdClass;
    $formatoptions->noclean = true;
    $formatoptions->overflowdiv = true;
    $formatoptions->context = $context;
    $content = format_text($content, $inventory->contentformat, $formatoptions);
    echo $OUTPUT->box($content, "generalbox center clearfix");

    // We create a select box to select the category we want to display. Changing the category will call a Javascript function.

    echo "<input type=hidden id=id value=$id>"
            . "<input type=hidden id=building value=$building>
                <div>
                <select id=selectcategory onChange=categoryChanged()>";

    $listcategories = $DB->get_records('inventory_devicecategory');

    echo '
    <option value=0>'.get_string('nocategory', 'inventory').'</option>
    ';

    foreach ($listcategories as $category) {

        if ($categoryselected == $category->id) {

            echo '
            <option value='.$category->id.' selected=selected>'.$category->name.'</option>
            ';

        } else {

            echo '
            <option value='.$category->id.'>'.$category->name.'</option>
            ';
        }


    }
    echo'
        </select>
    </div>
    <div id=listrooms>';

    // If we are in all buildings, we display all buildings. Otherwise, we'll display only the rooms of the current building.


    if ($building != 0) {

        $listbuildings = $DB->get_records('inventory_building', array('id' => $building));

    } else {

        $listbuildings = $DB->get_records('inventory_building');
    }

    $numelemcolonne = 0;

    echo "

    <form method=post action=listrooms.php?export=true>";

    foreach ($listbuildings as $buildingkey => $buildingvalue) {

        // We check whether or not there is a room with the good category of device in this building.
        // If not, we will not display the name of the building.

        $hasdevice = 0;
        $hasroom = 0;

        $listrooms = $DB->get_records('inventory_room', array('buildingid' => $buildingkey));

        foreach ($listrooms as $key => $value) {

            $hasdevice = $DB->record_exists('inventory_device', array('categoryid' => $categoryselected, 'roomid' => $key));

            if ($hasdevice) {
                break;
            }
        }

        if ($categoryselected == 0) {

            $hasroom = $DB->record_exists('inventory_room', array('buildingid' => $buildingkey));
        }

        if ($hasdevice || ($hasroom && !$hasdevice)) {

            echo "<div><h5>$buildingvalue->name</h5></div>";
        }

        foreach ($listrooms as $key => $value) {

            // We check whether or not this room have the good category of device.
            // If not, we will not display the room.

            $hasdevice = $DB->record_exists('inventory_device', array('categoryid' => $categoryselected, 'roomid' => $key));

            if ($categoryselected == 0 || $hasdevice) {

                if ($numelemcolonne == 0) {
                    echo "
                    <div class=divRooms>
                        <table>";
                }

                $checkboxname = "exportroom".$key;

                echo "
                <tr>
                    <td>
                        <ul>
                            <li class=singleRoom>";

                if (has_capability('mod/inventory:edit', $context)) {

                    echo "
                    <input type=checkbox name=$checkboxname />";
                }

                echo "
                <a href='listdevices.php?id=$id&amp;room=$key'>";

                    $roomtodisplay = $listrooms[$key]->name;

                    echo "$roomtodisplay";

                    echo "
                                </a>
                            </li>
                        </ul>
                    </td>";

                // If the category has an icon and the room have a device of this category,
                // we will display the icon next to the name of the room.

                foreach ($listcategories as $category) {

                    $iconurl = "";

                    if ($category->iconname != "" && $category->iconname != null) {

                        $listdevices = $DB->get_records('inventory_device', array('roomid' => $key));
                        $hascategory = 0;

                        foreach ($listdevices as $device) {

                            if ($device->categoryid == $category->id) {

                                $hascategory = 1;
                                break;
                            }
                        }

                        if ($hascategory == 1) {

                            $fs = get_file_storage();
                            $contextmodule = context_module::instance($id);
                            $filename = $category->iconname;
                            $fileinfo = array(
                                    'component' => 'mod_inventory',
                                    'filearea' => 'icon',     // Usually = table name.
                                    'itemid' => $category->id,               // Usually = ID of row in table.
                                    'contextid' => $contextmodule->id, // ID of context.
                                    'filepath' => '/',           // Any path beginning and ending in /.
                                    'filename' => $filename); // Any filename.
                            $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                                    $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

                            $categoryname = $category->name;

                            if ($file) {

                                $iconurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                                        $file->get_component(), $file->get_filearea(), $file->get_itemid(),
                                        $file->get_filepath(), $file->get_filename());
                            } else {

                                $iconurl = "";
                            }

                            if ($iconurl != "") {
                                echo "
                                <td>
                                        <img src=$iconurl alt=$categoryname"
                                        . " title=$categoryname style=width:20px;height:20px; />
                                </td>
                                ";
                            }
                        }

                        if ($iconurl == "") {
                                echo "
                                <td />";
                        }
                    }
                }

                // We display the buttons to edit and delete this room only if the user is allowed to edit.

                if (has_capability('mod/inventory:edit', $context)) {
                    echo "
                    <td>
                        <a href='editroom.php?courseid=$course->id&amp;blockid=$cm->p&amp;"
                            . "moduleid=$cm->id&amp;buildingid=$building&amp;editmode=1&amp;id=$key'>";
                        echo'
                            <img src="../../pix/e/document_properties.png"
                            alt="Edit Room" style="width:20px;height:20px;" />
                        </a>
                    </td>
                    <td>';
                        echo "
                        <a href='deletedatabaseelement.php?id=$cm->id&amp;"
                                . "key=$key&amp;table=rooms&amp;building=$building&amp;sesskey=".sesskey()."'>";
                        echo'
                            <img src="../../pix/i/delete.png"
                            alt="Delete Room" style="width:20px;height:20px;" />
                        </a>
                    </td>';
                }
                echo '
                </tr>';
                if ($numelemcolonne == 14) {
                    echo "</table>
                    </div>";
                    $numelemcolonne = -1;
                }

                $numelemcolonne++;
            }
        }

        if ($numelemcolonne != 0) {
            echo "</table>
            </div>
            <div class=divRooms>
                <table>";
        }
    }

    if ($numelemcolonne != 0) {
        echo "</table>
        </div>";
    }

    echo'
        </div>';

        // If we are in 'allbuildings' or if the user is not allowed to edit the database, we cannot add a new room.


    if ($building != 0 && has_capability('mod/inventory:edit', $context)) {
        echo "<a href='editroom.php?courseid=$course->id&amp;blockid=$cm->p&amp;"
                . "moduleid=$cm->id&amp;buildingid=$building&amp;"
                . "editmode=0'><button>".get_string('addroom', 'inventory')."</button></a>";
    }

    // If we are not allowed to export the csv, the button to do that will not be displayed.

    if (has_capability('mod/inventory:edit', $context)) {

        echo "
        <input type=hidden name=id value=$id />
        <input type=hidden name=p value=$p />
        <input type=hidden name=inpopup value=$inpopup />
        <input type=hidden name=building value=$building />
        <input type=hidden name=categoryselected value=$categoryselected />

        <input type=submit value='".get_string('exportroomsascsv', 'inventory')."' />";
    }
    echo "
    </form> ";
    $strlastmodified = get_string("lastmodified");
    echo "<div class=\"modified\">$strlastmodified: ".userdate($inventory->timemodified)."</div>";

    echo $OUTPUT->footer();
} else {

    // To display the csv, we need an entirely new page without html.

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

    // Just in case, we check whether or not we are allowed to be here.

    require_course_login($course, true, $cm);
    $context = context_module::instance($cm->id);
    require_capability('mod/inventory:edit', $context);

    // If we are in all buildings, we need to check all rooms.


    if ($building != 0) {

        $listbuildings = $DB->get_records('inventory_building', array('id' => $building));

    } else {

        $listbuildings = $DB->get_records('inventory_building');
    }

    // If the checkbox of this room was selected, we add this room to the list of room to print in the csv file.
    // We use listbuildings to ensure that if we are in 'allbuildings',
    // we will put the rooms in the CVS in the same order as the order on screen.

    $listroomsid = array();

    foreach ($listbuildings as $buildingrecord) {

        $listrooms = $DB->get_records('inventory_room', array('buildingid' => $buildingrecord->id));

        foreach ($listrooms as $roomkey => $roomvalue) {


            $checkboxname = "exportroom".$roomkey;

            $checkboxvalue = filter_input(INPUT_POST, $checkboxname);

            if ($checkboxvalue == "on") {

                $listroomsid[] = $roomkey;
            }

        }
    }

    // If the user tried to export without selecting at least a room, we redirect him to the page without the exportmode activated.

    if ($listroomsid != null) {

        exportcsv($listroomsid);
    } else {

        $thisurl = new moodle_url('/mod/inventory/listrooms.php',
                array('id' => $id, 'building' => $building, 'categoryselected' => $categoryselected));
        redirect($thisurl);
    }
}

function exportcsv(array $listroomsid) {

    global $DB, $CFG;

    require_once($CFG->libdir . '/csvlib.class.php');

    $csvexporter = new csv_export_writer();

    $csvexporter->set_filename('exportedrooms');

    $title = array(utf8_decode(get_string('csvtitle', 'inventory')));
    $csvexporter->add_data($title);

    // For each room currently selected, we list all its devices and put all information in the csv.

    foreach ($listroomsid as $currentroomid) {

        $csvexporter->add_data($currentroomid);

        $currentroom = $DB->get_record('inventory_room', array('id' => $currentroomid));

        $listdevices = $DB->get_records('inventory_device', array('roomid' => $currentroom->id));

        $roomname = array(utf8_decode($currentroom->name));
        $csvexporter->add_data($roomname);

        foreach ($listdevices as $currentdevice) {

            $referenceid = $currentdevice->refid;
            $currentreference = $DB->get_record('inventory_reference', array('id' => $referenceid));
            $currentbrand = $DB->get_record('inventory_brand', array('id' => $currentreference->brandid));
            $currentcategory = $DB->get_record('inventory_devicecategory', array('id' => $currentdevice->categoryid));

            $devicedata = array();

            $categoryname = $currentcategory->name;

            $devicedata[] = utf8_decode($categoryname);

            if ($currentreference->name != "undefined") {

                $devicedata[] = utf8_decode($currentreference->name);
            }

            if ($currentbrand->name != "undefined") {

                $devicedata[] = utf8_decode($currentbrand->name);
            }

            $devicedata[] = $currentdevice->isworking;

            $listfields = $DB->get_records('inventory_devicefield', array('categoryid' => $currentcategory->id));

            foreach ($listfields as $currentfield) {

                $valuetable = $DB->get_record('inventory_devicevalue',
                        array('fieldid' => $currentfield->id, 'deviceid' => $currentdevice->id));
                if ($valuetable->value != "") {

                    $devicedata[] = utf8_decode($valuetable->value);
                }
            }

            $csvexporter->add_data($devicedata);
        }
    }

    $csvexporter->download_file();
}

?>

<script type='text/javascript'>

    // If we change categories, we reload the page with the new category id as argument.

    function categoryChanged() {

        selectElement = document.getElementById('selectcategory');

        selectValue = selectElement.options[selectElement.selectedIndex].value;
        id = document.getElementById('id').value;
        building = document.getElementById('building').value;

        window.location.href = 'listrooms.php?id=' + id + '&building=' + building + '&categoryselected=' + selectValue;
    }

</script>
