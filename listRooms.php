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
 * File : listRooms.php
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

if ($export != true) {

    echo '
        <head>
            <meta charset="utf-8" />
            <link rel="stylesheet" href="listRoomsStyle.css" />
        </head>';

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
    require_capability('mod/inventory:view', $context);

    // Completion and trigger events.
    inventory_view($inventory, $course, $cm, $context);

    $PAGE->set_url('/mod/inventory/listRooms.php', array('id' => $id, 'building' => $building, 'categoryselected' => $categoryselected));

    $options = empty($inventory->displayoptions) ? array() : unserialize($inventory->displayoptions);

    if ($inpopup and $inventory->display == RESOURCELIB_DISPLAY_POPUP) {
        $PAGE->set_pagelayout('popup');
    } else {
        $PAGE->set_activity_record($inventory);
    }
    $PAGE->set_title($course->shortname.': '.$inventory->name);
    $PAGE->set_heading($course->fullname);



    if ($building != 0) {

        $PAGE->navbar->add($DB->get_record('inventory_building', array('id' => $building))->name, new moodle_url('/mod/inventory/listRooms.php', array('id' => $id, 'building' => $building, 'categoryselected' => $categoryselected)));
    } else {

        $PAGE->navbar->add(get_string('allbuildings', 'inventory'), new moodle_url('/mod/inventory/listRooms.php', array('id' => $id, 'building' => $building, 'categoryselected' => $categoryselected)));
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

    $content = file_rewrite_pluginfile_urls($inventory->content, 'pluginfile.php', $context->id, 'mod_inventory', 'content', $inventory->revision);
    $formatoptions = new stdClass;
    $formatoptions->noclean = true;
    $formatoptions->overflowdiv = true;
    $formatoptions->context = $context;
    $content = format_text($content, $inventory->contentformat, $formatoptions);
    echo $OUTPUT->box($content, "generalbox center clearfix");

    echo "

    <input type=hidden id=id value=$id>
    <input type=hidden id=building value=$building>

    <div>
        <select id=selectcategory onChange=categoryChanged()>";

            $listcategories = $DB->get_records('inventory_devicecategory');

            echo '
            <option value=0>'.get_string('nocategory', 'inventory').'</option>
            ';

            foreach ($listcategories as $category) {

                if($categoryselected == $category->id) {

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
    <div id=listRooms>';


    if($building != 0) {

        $listbuildings = $DB->get_records('inventory_building', array('id' => $building));

    } else {

        $listbuildings = $DB->get_records('inventory_building');
    }

    $numelemcolonne = 0;

    echo "

    <form method=post action=listRooms.php?export=true>";

        foreach ($listbuildings as $buildingkey => $buildingvalue) {

            $hasdevice = 0;
            $hasroom = 0;

            $listrooms = $DB->get_records('inventory_room', array('buildingid' => $buildingkey));

            foreach ($listrooms as $key => $value) {

                $hasdevice = $DB->record_exists('inventory_device', array('categoryid' => $categoryselected, 'roomid' => $key));

                if($hasdevice) {
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

                $hasdevice = $DB->record_exists('inventory_device', array('categoryid' => $categoryselected, 'roomid' => $key));

                if($categoryselected == 0 || $hasdevice) {

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
                                    <a href='listDevices.php?id=$id&amp;room=$key'>";

                                        $roomtodisplay = $listrooms[$key]->name;

                                        echo "$roomtodisplay";

                        echo "
                                    </a>
                                </li>
                            </ul>
                        </td>";
                        foreach($listcategories as $category) {

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

                                if($hascategory == 1) {

                                    $fs = get_file_storage();
                                    $contextmodule = context_module::instance($id);
                                    $filename = $category->iconname;
                                    $fileinfo = array(
                                            'component' => 'mod_inventory',
                                            'filearea' => 'icon',     // usually = table name
                                            'itemid' => $category->id,               // usually = ID of row in table
                                            'contextid' => $contextmodule->id, // ID of context
                                            'filepath' => '/',           // any path beginning and ending in /
                                            'filename' => $filename); // any filename
                                    $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                                            $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

                                    $categoryname = $category->name;

                                    if ($file) {

                                        $iconurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
                                    } else {

                                        $iconurl = "";
                                    }

                                    if($iconurl != "") {
                                        echo "
                                        <td>
                                                <img src=$iconurl alt=$categoryname title=$categoryname style=width:20px;height:20px; />
                                        </td>
                                        ";
                                    }
                                }

                                if($iconurl == "") {
                                        echo "
                                        <td />";
                                }
                            }
                        }
                        if(has_capability('mod/inventory:edit', $context)) {
                            echo "
                            <td>
                                <a href='editRoom.php?courseid=$course->id&amp;blockid=$cm->p&amp;moduleid=$cm->id&amp;buildingid=$building&amp;editmode=1&amp;id=$key'>";
                                echo'
                                    <img src="../../pix/e/document_properties.png" alt="Edit Room" style="width:20px;height:20px;" />
                                </a>
                            </td>
                            <td>';
                                echo "
                                <a href='deleteDatabaseElement.php?id=$cm->id&amp;key=$key&amp;table=rooms&amp;building=$building&amp;sesskey=".sesskey()."'>";
                                echo'
                                    <img src="../../pix/i/delete.png" alt="Delete Room" style="width:20px;height:20px;" />
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


        if ($building != 0 && has_capability('mod/inventory:edit', $context)) {
            echo "<a href='editRoom.php?courseid=$course->id&amp;blockid=$cm->p&amp;moduleid=$cm->id&amp;buildingid=$building&amp;editmode=0'><button>".get_string('addroom','inventory')."</button></a>";
        }

        $listRoomsid = array();

        //Construire listRoomsid ici

        $listRoomsidinurl = http_build_query(array('listRoomsid' => $listRoomsid));
        $encodedlistRoomsidinurl = urlencode($listRoomsidinurl);

        if(has_capability('mod/inventory:edit', $context)) {

            echo "
            <input type=hidden name=id value=$id />
            <input type=hidden name=p value=$p />
            <input type=hidden name=inpopup value=$inpopup />
            <input type=hidden name=building value=$building />
            <input type=hidden name=categoryselected value=$categoryselected />

            <input type=submit value='".get_string('exportroomsascsv','inventory')."' />";
        }
    echo "
    </form> ";
    $strlastmodified = get_string("lastmodified");
    echo "<div class=\"modified\">$strlastmodified: ".userdate($inventory->timemodified)."</div>";

    echo $OUTPUT->footer();
} else {

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
    require_capability('mod/inventory:edit', $context);


    if($building != 0) {

        $listrooms = $DB->get_records('inventory_room', array('buildingid' => $building));

    } else {

        $listrooms = $DB->get_records('inventory_room');
    }

    $listroomsid = array();

    foreach ($listrooms as $roomkey => $roomvalue) {


        $checkboxname = "exportroom".$roomkey;

        $checkboxvalue = filter_input(INPUT_POST, $checkboxname);

        if ($checkboxvalue == "on") {

            $listroomsid[] = $roomkey;
        }

    }

    if ($listroomsid != null) {

        exportcsv($listroomsid);
    } else {

        $thisurl = new moodle_url('/mod/inventory/listRooms.php', array('id' => $id, 'building' => $building, 'categoryselected' => $categoryselected));
        redirect($thisurl);
    }
}

function exportcsv(array $listRoomsid) {

    global $DB, $CFG;

    require_once($CFG->libdir . '/csvlib.class.php');

    $csvexporter = new csv_export_writer();

    $csvexporter->set_filename('test');

    $title = array(utf8_decode(get_string('csvtitle', 'inventory')));
    $csvexporter->add_data($title);

    foreach ($listRoomsid as $currentroomid) {

        $csvexporter->add_data($currentroomid);

        $currentroom = $DB->get_record('inventory_room', array('id' => $currentroomid));

        $listDevices = $DB->get_records('inventory_device', array('roomid' => $currentroom->id));

        $roomname = array(utf8_decode($currentroom->name));
        $csvexporter->add_data($roomname);

        foreach ($listDevices as $currentdevice) {

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

            $listfields = $DB->get_records('inventory_devicefield',array('categoryid' => $currentcategory->id));

            foreach ($listfields as $currentfield) {

                $valuetable = $DB->get_record('inventory_devicevalue', array('fieldid' => $currentfield->id, 'deviceid' => $currentdevice->id));
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

    function categoryChanged() {

        selectElement = document.getElementById('selectcategory');

        selectValue = selectElement.options[selectElement.selectedIndex].value;
        id = document.getElementById('id').value;
        building = document.getElementById('building').value;

        window.location.href = 'listRooms.php?id=' + id + '&building=' + building + '&categoryselected=' + selectValue;
    }

</script>