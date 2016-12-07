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
 * File : managedevicestype.php
 * Manage the type of devices
 *
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/inventory/lib.php');
require_once($CFG->dirroot.'/mod/inventory/locallib.php');
require_once($CFG->libdir.'/completionlib.php');

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

// Completion and trigger events.

$PAGE->set_url('/mod/inventory/view.php', array('id' => $cm->id));

$PAGE->navbar->add(get_string('managedevicestype', 'inventory'),
        new moodle_url('/mod/inventory/managedevicestype.php', array('id' => $cm->id)));

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
if (!isset($options['printheading']) || !empty($options['printheading'])) {
    echo $OUTPUT->heading(format_string(get_string('managedevicestype', 'inventory')), 2);
}

// We can only come here if we can edit the database.

require_capability('mod/inventory:edit', $context);

$listcategories = $DB->get_records('inventory_devicecategory', array('moduleid' => $id));

// We display all current categories with buttons to edit or delete them.

$numelemcolonne = 0;
echo '<div id=listCategories>';
foreach ($listcategories as $key => $value) {

    if ($numelemcolonne == 0) {
        echo "<div class=divCategories>
            <table>";
    }

    echo "
    <tr>
        <td>
            <ul>
                <li class=singleCategory>";

                    $categorytodisplay = $listcategories[$key]->name;

                    echo "$categorytodisplay";

        echo "
                </li>
            </ul>
        </td>
        <td>
            <a href='editdevicetype.php?courseid=$course->id&amp;blockid=$cm->p&amp;"
                . "moduleid=$cm->id&amp;editmode=1&amp;categoryid=$key&amp;source=managedevicestype'>";
            echo'
                <img src="../../pix/e/document_properties.png" alt="Edit category" style="width:20px;height:20px;" />
            </a>
        </td>
        <td>';
            echo "
            <a href='deletedatabaseelement.php?id=$id&amp;"
                    . "table=devicecategory&amp;key=$key&amp;sesskey=".sesskey()."'>";
            echo'
                <img src="../../pix/i/delete.png" alt="Delete category" style="width:20px;height:20px;" />
            </a>
        </td>
    </tr>';
    if ($numelemcolonne == 14) {
        echo "</table>
        </div>";
        $numelemcolonne = -1;
    }

    $numelemcolonne++;
}

if ($numelemcolonne != 0) {
    echo "</table>
    </div>";
}

echo'
</div>';

// We add a button to create a new type of device.

echo"<a href='editdevicetype.php?courseid=$course->id&amp;blockid=$cm->p&amp;moduleid=$cm->id&amp;"
        . "editmode=0&amp;"
        . "source=managedevicestype'><button>".get_string('adddevicetype', 'inventory')."</button></a>";

echo $OUTPUT->footer();