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
 * File : listdevices.php
 * List all devices and allow interaction
 *
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/inventory/lib.php');
require_once($CFG->dirroot.'/mod/inventory/locallib.php');
require_once($CFG->libdir.'/completionlib.php');

echo '<head>'
. '<meta charset="utf-8" />'
        . '<link rel="stylesheet" href="listdevicesstyle.css" />'
        . '</head>';

$id      = optional_param('id', 0, PARAM_INT); // Course Module ID.
$p       = optional_param('p', 0, PARAM_INT);  // Page instance ID.
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);
$room = required_param('room', PARAM_INT);

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

$PAGE->set_url('/mod/inventory/listdevices.php', array('id' => $cm->id, 'p' => $p, 'inpopup' => $inpopup, 'room' => $room));

$options = empty($inventory->displayoptions) ? array() : unserialize($inventory->displayoptions);

if ($inpopup and $inventory->display == RESOURCELIB_DISPLAY_POPUP) {
    $PAGE->set_pagelayout('popup');
    $PAGE->set_title($course->shortname.': '.$inventory->name);
    $PAGE->set_heading($course->fullname);
} else {
    $PAGE->set_title($course->shortname.': '.$inventory->name);
    $PAGE->set_heading($course->fullname);
}

$currentrecord = $DB->get_record('inventory_room', array('id' => $room));
$building = $currentrecord->buildingid;

$PAGE->navbar->add($DB->get_record('inventory_building', array('id' => $building))->name,
        new moodle_url('/mod/inventory/listrooms.php', array('id' => $id, 'building' => $building)));

$PAGE->navbar->add($currentrecord->name, new moodle_url('/mod/inventory/listdevices.php', array('id' => $id, 'room' => $room)));


echo $OUTPUT->header();
if (!isset($options['printheading']) || !empty($options['printheading'])) {
    echo $OUTPUT->heading(format_string($DB->get_record('inventory_room', array('id' => $room))->name), 2);
}

if (!empty($options['printintro'])) {
    if (trim(strip_tags($inventory->intro))) {
        echo $OUTPUT->box_start('mod_introbox', 'inventoryintro');
        echo format_module_intro('inventory', $inventory, $cm->id);
        echo $OUTPUT->box_end();
    }
}

echo"<div>";

$currentroom = $DB->get_record('inventory_room', array('id' => $room));

$publiccommentary = false;

if ((($currentroom->publiccommentary != "" && $currentroom->publiccommentary != null)
        || ($DB->record_exists('inventory_attachmentroom', array('roomid' => $room, 'isprivate' => 0))))) {

    $publiccommentary = true;
}

if ($publiccommentary || (has_capability('mod/inventory:edit', $context))) {

    // Display the public commentary.

    echo"<div class=headercommentary>"
    . "<div>"
            . "<strong>".get_string('publiccommentary', 'inventory')."</strong>"
            . "</div>"
            . "<div class=editbutton>";

    // Display the edit button only if the user is allowed to edit.

    if (has_capability('mod/inventory:edit', $context)) {
        echo ""
        . "<a href='editcommentary.php?id=$id&amp;room=$room&amp;mode=public'>
            <button>".get_string('editcommentary', 'inventory')."</button>
        </a>";
    }
    echo "
            </div>
        </div>
    ";

    echo $currentroom->publiccommentary;

    // Display the public attachments.

    $listpublicattachments = $DB->get_records('inventory_attachmentroom', array('roomid' => $room, 'isprivate' => 0));

    foreach ($listpublicattachments as $publicattachment) {

        $fs = get_file_storage();
        $contextmodule = context_module::instance($id);
        $attachmenturl = "";

        $filename = $publicattachment->name;
        $fileinfo = array(
                'component' => 'mod_inventory',
                'filearea' => 'publicattachment',     // Usually = table name.
                'itemid' => $room,           // Usually = ID of row in table.
                'contextid' => $contextmodule->id, // ID of context.
                'filepath' => '/',           // Any path beginning and ending in /.
                'filename' => $filename); // Any filename.
        $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

        if ($file) {

            $attachmenturl = moodle_url::make_pluginfile_url($file->get_contextid(),
                    $file->get_component(), $file->get_filearea(), $file->get_itemid(),
                    $file->get_filepath(), $file->get_filename());
        }

        echo '<a href='.$attachmenturl.'>'.$publicattachment->name.'</a> ';
    }
}

// Display the private commentary and the private attachments only if the user is allowed to edit.

if (has_capability('mod/inventory:edit', $context)) {

    echo"<div class=headercommentary>
            <div>
                <strong>".get_string('privatecommentary', 'inventory')."</strong>
            </div>
            <div class=editbutton>
                <a href='editcommentary.php?id=$id&amp;room=$room&amp;mode=private'>
                    <button>".get_string('editcommentary', 'inventory')."</button>
                </a>
            </div>
        </div>
    ";

    echo $currentroom->privatecommentary;

    $listprivateattachments = $DB->get_records('inventory_attachmentroom', array('roomid' => $room, 'isprivate' => 1));

    foreach ($listprivateattachments as $privateattachment) {

        $fs = get_file_storage();
        $contextmodule = context_module::instance($id);
        $attachmenturl = "";

        $filename = $privateattachment->name;
        $fileinfo = array(
                'component' => 'mod_inventory',
                'filearea' => 'privateattachment',     // Usually = table name.
                'itemid' => $room,           // Usually = ID of row in table.
                'contextid' => $contextmodule->id, // ID of context.
                'filepath' => '/',           // Any path beginning and ending in /.
                'filename' => $filename); // Any filename.
        $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

        if ($file) {

            $attachmenturl = moodle_url::make_pluginfile_url($file->get_contextid(),
                    $file->get_component(), $file->get_filearea(), $file->get_itemid(),
                    $file->get_filepath(), $file->get_filename());
        }

        echo '<a href='.$attachmenturl.'>'.$privateattachment->name.'</a> ';
    }
}

// Display all devices.

$listdevices = $DB->get_records('inventory_device', array('roomid' => $room));

echo"
<table id=listdevice>";

foreach ($listdevices as $key => $currentdevice) {

    $categoryid = $currentdevice->categoryid;

    /*
     * 1) Display the device type using the 'categoryid' of the current device.
     * 2) Display all general fields (reference and brand) if they are not undefined.
     * 3) Display all specific fields if they have a value.
    */

    echo "
    <tr class=linesingledevice>";
        echo"
        <div class=singledevice>
            <td class=deviceinfo>
                <table class=deviceheader>
                    <tr class=lineheader>
                        <td>
                            <div class=namedevice>";

                                $devicecategory = $DB->get_record('inventory_devicecategory', array('id' => $categoryid));

    if (has_capability('mod/inventory:edit', $context)) {
        echo"<a href='editdevice.php?id=$key&amp;courseid=$course->id&amp;"
        . "blockid=$cm->p&amp;moduleid=$cm->id&amp;"
        . "roomid=$room&amp;editmode=1&amp;categoryid=$categoryid'>$devicecategory->name</a>";
    } else {

        echo"$devicecategory->name";
    }
                                echo "
                            </div>
                        </td>";

    // If the device is broken, display this information.

    // Functionality temporarily disabled.

    /*
    if ($currentdevice->isworking == "Non") {
                        echo "
                        <td class=cellisworking>
                            <div class=isworking>";
                                echo get_string('failure', 'inventory');
                                echo "
                            </div>
                        </td>
                            ";
    }
    */
                        echo "
                        <td class=devicebuttons>";

    // We can only report the failure of an equipment if we are allowed to do it.

    if (has_capability('mod/inventory:reportfailure', $context)) {


        /* This is temporarily disabled. We use the version below instead.

        if (($devicecategory->linkforfailure != null && $devicecategory->linkforfailure != "") ||
                ($devicecategory->textforfailure != null && $devicecategory->textforfailure != "")) {
            if ($currentdevice->isworking == "Oui") {

                if ($devicecategory->textforfailure != null && $devicecategory->textforfailure != "") {

                    echo "
                            <div class=boxwithmargin>
                                <a href='failure.php?id=$id&amp;key=$key&amp;"
                                    . "mode=failure'><button>".get_string('reportfailure',
                                            'inventory')."</button></a>
                            </div>";
                } else {

                    $failureurl = $devicecategory->linkforfailure;

                    echo "
                            <div class=boxwithmargin>
                                <a href='failure.php?id=$id&amp;key=$key&amp;"
                                    . "mode=failure' onclick=window.open('".$failureurl."');><button>".get_string('reportfailure',
                                            'inventory')."</button></a>
                            </div>";
                }
            } else {
                echo "
                            <div class=boxwithmargin>
                                <a href='failure.php?id=$id&amp;key=$key&amp;"
                                    . "mode=working'><button>".get_string('reportworking',
                                            'inventory')."</button></a>
                            </div>";
            }
        }
         */

        if ($devicecategory->linkforfailure != null && $devicecategory->linkforfailure != "") {

            $failureurl = $devicecategory->linkforfailure;

            echo "
                    <div class=boxwithmargin>
                        <a onclick=window.open('".$failureurl."');><button>".get_string('reportfailure',
                                    'inventory')."</button></a>
                    </div>";
        }
    }

    // If the user can edit the database, display the edit and delete buttons.

    if (has_capability('mod/inventory:edit', $context)) {

        echo "
                            <div>
                                <a href='editdevice.php?id=$key&amp;courseid=$course->id&amp;"
                                    . "blockid=$cm->p&amp;moduleid=$cm->id&amp;"
                                    . "roomid=$room&amp;editmode=1&amp;categoryid=$categoryid'>";
                                echo '
                                    <img src="../../pix/e/document_properties.png"
                                    alt="Edit Room" style="width:20px;height:20px;" />';
                                echo "
                                </a>
                            </div>
                            <div>
                                <a href='deletedatabaseelement.php?id=$cm->id&amp;key=$key&amp;"
                                        . "table=devices&amp;room=$room&amp;sesskey=".sesskey()."'>";
                                echo'
                                    <img src="../../pix/i/delete.png" alt="Delete Room" style="width:20px;height:20px;" />
                                </a>';
                            echo "
                            </div>";
    }
    echo "
                        </td>
                    </tr>
                </table>
                <div class=infodevice>";

    $referenceid = $currentdevice->refid;
    $referencedata = $DB->get_record('inventory_reference', array('id' => $referenceid));
    $branddata = $DB->get_record('inventory_brand', array('id' => $referencedata->brandid));

    if ($referencedata->name != "undefined") {
        echo"
        <div class=boxwithmargin>";
            echo get_string('reference', 'inventory'); echo " : $referencedata->name";
        echo "
        </div>";
    }
    if ($branddata->name != "undefined") {
        echo"
        <div class=boxwithmargin>";
            echo get_string('brand', 'inventory'); echo " : $branddata->name";
        echo "
        </div>
        ";
    }
    $listefields = $DB->get_records('inventory_devicefield', array('categoryid' => $categoryid));

    foreach ($listefields as $fieldkey => $currentfield) {

        if (has_capability('mod/inventory:edit', $context)) {

            $valuetable = $DB->get_record('inventory_devicevalue',
                    array('fieldid' => $currentfield->id, 'deviceid' => $currentdevice->id));
            if ($valuetable->value != "") {
                echo "
                <div class=boxwithmargin>
                    $currentfield->name : $valuetable->value
                </div>";
            }
        }
    }

    // If the device has a specific manual, display it.
    // Otherwise, display the manual of the reference if there is one.
    // If it has no manual, display nothing.

    $fs = get_file_storage();
    $contextmodule = context_module::instance($id);
    $filename = $currentdevice->documentation;
    $fileinfo = array(
            'component' => 'mod_inventory',
            'filearea' => 'manuel',     // Usually = table name.
            'itemid' => $key,               // Usually = ID of row in table.
            'contextid' => $contextmodule->id, // ID of context.
            'filepath' => '/',           // Any path beginning and ending in /.
            'filename' => $filename); // Any filename.
    $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
            $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

    // ... file_save_draft_area_files also save the repository of files '.'
    // so we need to test if the device should have a documentation.

    if ($file && $currentdevice->documentation) {

        $manuelurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                $file->get_component(), $file->get_filearea(),
                $file->get_itemid(), $file->get_filepath(), $file->get_filename());
    } else {

        $manuelurl = "";

        $filename2 = $referencedata->documentation;
        $fileinfo2 = array(
                'component' => 'mod_inventory',
                'filearea' => 'manuelreference',     // Usually = table name.
                'itemid' => $referencedata->id,               // Usually = ID of row in table.
                'contextid' => $contextmodule->id, // ID of context.
                'filepath' => '/',           // Any path beginning and ending in /.
                'filename' => $filename2); // Any filename.
        $file = $fs->get_file($fileinfo2['contextid'], $fileinfo2['component'], $fileinfo2['filearea'],
                $fileinfo2['itemid'], $fileinfo2['filepath'], $fileinfo2['filename']);

        // ... file_save_draft_area_files also save the repository of files '.'
        // so we need to test if the reference should have a documentation.

        if ($file && $referencedata->documentation) {

            $manuelrefurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                    $file->get_component(), $file->get_filearea(), $file->get_itemid(),
                    $file->get_filepath(), $file->get_filename());
        } else {

            $manuelrefurl = "";
        }
    }


    if ($manuelurl != "") {
        echo "
                    <div class=boxwithmargin>".
                    get_string('manuelspecific', 'inventory')." : "
                . "<a href=$manuelurl>".get_string('manuelspecific', 'inventory')."</a>
                    </div>";
    } else if ($manuelrefurl != "") {

        echo "
                    <div class=boxwithmargin>".
                    get_string('manuel', 'inventory')." : <a href=$manuelrefurl>".get_string('manuel', 'inventory')."</a>
                    </div>";
    }
    echo "
                </div>
            </td>
        </div>";

    echo "
    </tr>";

}
echo "
    </table>
</div>";

// If the user can edit, display the buttons to create a device of every category.
// Display also the button to create a new category.

$listcategories = $DB->get_records('inventory_devicecategory', array('moduleid' => $id));

if (has_capability('mod/inventory:edit', $context)) {

    foreach ($listcategories as $category) {
        echo"
        <a href='editdevice.php?courseid=$course->id&amp;blockid=$cm->p&amp;moduleid=$cm->id&amp;"
                . "roomid=$room&amp;editmode=0&amp;"
                . "categoryid=$category->id'><button>".get_string('add', 'inventory')." $category->name</button></a>
        ";
    }
    echo"
        <a href='editdevicetype.php?courseid=$course->id&amp;blockid=$cm->p&amp;"
            . "moduleid=$cm->id&amp;editmode=0&amp;roomid=$room&amp;"
            . "source=listdevices'><button>".get_string('adddevicetype', 'inventory')."</button></a>
        ";

}

echo $OUTPUT->footer();
