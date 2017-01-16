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
 * File : view.php
 * Inventory module view of buildings
 *
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/inventory/lib.php');
require_once($CFG->dirroot.'/mod/inventory/locallib.php');
require_once($CFG->libdir.'/completionlib.php');

echo '
<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="listbuildingsstyle.css" />
</head>';

$id      = optional_param('id', 0, PARAM_INT); // Course Module ID.
$p       = optional_param('p', 0, PARAM_INT);  // Page instance ID.
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);

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

$PAGE->set_url('/mod/inventory/view.php', array('id' => $cm->id));

$options = empty($inventory->displayoptions) ? array() : unserialize($inventory->displayoptions);

if ($inpopup and $inventory->display == RESOURCELIB_DISPLAY_POPUP) {
    $PAGE->set_pagelayout('popup');
    $PAGE->set_title($course->shortname.': '.$inventory->name);
    $PAGE->set_heading($course->fullname);
} else {
    $PAGE->set_title($course->shortname.': '.$inventory->name);
    $PAGE->set_heading($course->fullname);
}

echo $OUTPUT->header();

echo $OUTPUT->heading(format_string($inventory->name), 2);


if (!empty($options['printintro'])) {
    if (trim(strip_tags($inventory->intro))) {
        echo $OUTPUT->box_start('mod_introbox', 'inventoryintro');
        echo format_module_intro('inventory', $inventory, $cm->id);
        echo $OUTPUT->box_end();
    }
}

$listebuilding = $DB->get_records('inventory_building', array('moduleid' => $id));

// We display all buildings. Clicking on a building will display the rooms in this building.

echo '
<div id=buildings>';
foreach ($listebuilding as $key => $value) {

    $buildingtodisplay = $listebuilding[$key]->name;

    echo '
        <table class=singleBuilding>
            <tr>
                <td>';
                    echo "
                        <a href='listrooms.php?id=$id&amp;building=$key'>";

                        // We get the image of this building by taking the name from the database
                        // and we fetch the url with get_file.

                        $fs = get_file_storage();
                        $contextmodule = context_module::instance($id);
                        $filename = $listebuilding[$key]->imagename;
                        $fileinfo = array(
                                'component' => 'mod_inventory',
                                'filearea' => 'image',     // Usually = table name.
                                'itemid' => $key,               // Usually = ID of row in table.
                                'contextid' => $contextmodule->id, // ID of context.
                                'filepath' => '/',           // Any path beginning and ending in /.
                                'filename' => $filename); // Any filename.
                        $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

    if ($file) {

        $url = moodle_url::make_pluginfile_url($file->get_contextid(),
                $file->get_component(), $file->get_filearea(), $file->get_itemid(),
                $file->get_filepath(), $file->get_filename());
    } else {

        $url = "";
    }
    echo"
                        <img src=$url alt=$buildingtodisplay style=width:150px;height:150px; />
                    </a>
                </td>
                <td>";

                $context = context_module::instance($cm->id);

    // We display the edit and remove buttons only if the user has edit rights.

    if (has_capability('mod/inventory:edit', $context)) {

        echo "
        <table class=iconesBuilding>
            <tr>
                <td>";
                echo "
                    <a href='deletedatabaseelement.php?id=$cm->id&amp;"
                        . "key=$key&amp;table=buildings&amp;sesskey=".sesskey()."'>";
                    echo '
                        <img src="../../pix/i/delete.png" alt="Delete" style="width:20px;height:20px;" />
                    </a>
                </td>
            </tr>
            <tr>
                <td>';
                echo "
                    <a href='editbuilding.php?courseid=$course->id&amp;"
                        . "blockid=$p&amp;moduleid=$cm->id&amp;id=$key&amp;editmode=1'>";
                    echo '
                        <img src="../../pix/e/document_properties.png" alt="Edit" style="width:20px;height:20px;" />
                    </a>
                </td>
            </tr>
        </table>';
    }
    echo "
                </td>
            </tr>
            <tr class=lineName>
                <td class=cellName>
                    <a class=nameBuilding href='listrooms.php?id=$id&amp;building=$key'>";
                        echo "$buildingtodisplay";
    echo '          </a>
                </td>
            </tr>
        </table>';
}

// We add the 'AllBuildings' building. Clicking on it will display a list of all rooms in all the buildings.

echo'
<table class=singleBuilding>
            <tr>
                <td>';
                    echo "
                        <a href='listrooms.php?id=$id&amp;building=0'>";

                        echo"
                            <img src=pix/logo_cergy.jpg alt='All buildings' style=width:150px;height:150px; />
                        </a>
                </td>
            </tr>
            <tr class=lineName>
                <td class=cellName>
                    <a class=nameBuilding href='listrooms.php?id=$id&amp;building=0'>";
                        echo get_string('allbuildings', 'inventory');
    echo '          </a>
                </td>
            </tr>
        </table>';

echo'
    </div>';


// If we can edit the database, we can see these buttons.
// They allow us to add a new building and to manage the type of devices.

if (has_capability('mod/inventory:edit', $context)) {
    echo "<a href='editbuilding.php?courseid=$course->id&amp;blockid=$p&amp;moduleid=$cm->id&amp;"
            . "id=0&amp;editmode=0'><button>".get_string('addbuilding', 'inventory')."</button></a>
    <a href='managedevicestype.php?id=$id'><button>".get_string('managedevicestype', 'inventory')."</button></a>
    ";
}

echo $OUTPUT->footer();
