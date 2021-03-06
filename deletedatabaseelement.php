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
 * File : deletedatabaseelement.php
 * Delete an element and all elements that depend on it in the database.
 *
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/inventory/lib.php');
require_once($CFG->dirroot.'/mod/inventory/locallib.php');
require_once($CFG->libdir.'/completionlib.php');


$id         = optional_param('id', 0, PARAM_INT); // Course Module ID.
$p          = optional_param('p', 0, PARAM_INT);  // Page instance ID.
$inpopup    = optional_param('inpopup', 0, PARAM_BOOL);
$key        = required_param('key', PARAM_INT);
$delete     = optional_param('delete', 2, PARAM_INT);
$table      = required_param('table', PARAM_TEXT);
$building   = optional_param('building', 0, PARAM_INT);
$room       = optional_param('room', 0, PARAM_INT);
$oldid      = optional_param('oldid', 0, PARAM_INT);
$editmode   = optional_param('editmode', 0, PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$courseid   = optional_param('courseid', 0, PARAM_INT);
$blockid    = optional_param('blockid', 0, PARAM_INT);
$idreference = optional_param('idreference', 0, PARAM_INT);
$editmodereference = optional_param('editmodereference', 0, PARAM_INT);
$arraykey   = optional_param('arraykey', "", PARAM_RAW);
$sesskey = required_param('sesskey', PARAM_TEXT);


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

$PAGE->set_url('/mod/inventory/deletedatabaseelement', array('id' => $id, 'p' => $p, 'inpopup' => $inpopup, 'key' => $key,
    'delete' => $delete, 'table' => $table, 'building' => $building, 'room' => $room, 'oldid' => $oldid, 'editmode' => $editmode,
    'categoryid' => $categoryid, 'courseid' => $courseid, 'blockid' => $blockid, 'idreference' => $idreference,
    'editmodereference' => $editmodereference, 'arraykey' => $arraykey, 'sesskey' => $sesskey));

$options = empty($inventory->displayoptions) ? array() : unserialize($inventory->displayoptions);

if ($inpopup and $inventory->display == RESOURCELIB_DISPLAY_POPUP) {
    $PAGE->set_pagelayout('popup');
    $PAGE->set_title($course->shortname.': '.$inventory->name);
    $PAGE->set_heading($course->fullname);
} else {
    $PAGE->set_title($course->shortname.': '.$inventory->name);
    $PAGE->set_heading($course->fullname);
}

// The navigation bar depends on where we come from.

if ($table == "rooms") {

    $currentbuilding = $DB->get_record('inventory_building', array('id' => $building));
    $PAGE->navbar->add($currentbuilding->name, new moodle_url('/mod/inventory/listrooms.php',
            array('id' => $id, 'building' => $currentbuilding->id)));
} else if ($table == "devices") {

    $currentroom = $DB->get_record('inventory_room', array('id' => $room));
    $currentbuilding = $DB->get_record('inventory_building', array('id' => $currentroom->buildingid));
    $PAGE->navbar->add($currentbuilding->name, new moodle_url('/mod/inventory/listrooms.php',
            array('id' => $id, 'building' => $currentbuilding->id)));
    $PAGE->navbar->add($currentroom->name, new moodle_url('/mod/inventory/listdevices.php',
            array('id' => $id, 'room' => $room)));
} else if ($table == "references" || $table == "brandsfromdevice") {

    $currentroom = $DB->get_record('inventory_room', array('id' => $room));
    $currentbuilding = $DB->get_record('inventory_building', array('id' => $currentroom->buildingid));

    $PAGE->navbar->add($currentbuilding->name, new moodle_url('/mod/inventory/listrooms.php',
            array('id' => $id, 'building' => $currentbuilding->id)));
    $PAGE->navbar->add($currentroom->name, new moodle_url('/mod/inventory/listdevices.php',
            array('id' => $id, 'room' => $currentrecord->roomid)));

    if ($editmode == 0) {
        $PAGE->navbar->add(get_string('adddevice', 'inventory'), new moodle_url('/mod/inventory/editdevice.php',
                array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'roomid' => $roomid,
                    'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode)));
    } else {
        $PAGE->navbar->add(get_string('editdevice', 'inventory'), new moodle_url('/mod/inventory/editdevice.php',
                array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'roomid' => $roomid,
                    'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode)));
    }
} else if ($table == "brandsfromreference") {

    $currentroom = $DB->get_record('inventory_room', array('id' => $room));
    $currentbuilding = $DB->get_record('inventory_building', array('id' => $currentroom->buildingid));

    $PAGE->navbar->add($currentbuilding->name, new moodle_url('/mod/inventory/listrooms.php',
            array('id' => $id, 'building' => $currentbuilding->id)));
    $PAGE->navbar->add($currentroom->name, new moodle_url('/mod/inventory/listdevices.php',
            array('id' => $id, 'room' => $currentrecord->roomid)));

    if ($editmodereference == 0) {
        $PAGE->navbar->add(get_string('addreference', 'inventory'),
            new moodle_url('/mod/inventory/editreference.php',
                    array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid,
                        'roomid' => $roomid, 'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode,
                        'idreference' => $idreference, 'editmodereference' => $editmodereference)));
    } else {
        $PAGE->navbar->add(get_string('editreference', 'inventory'), new moodle_url('/mod/inventory/editreference.php',
                array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'roomid' => $roomid,
                    'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode, 'idreference' => $idreference,
                    'editmodereference' => $editmodereference)));
    }
} else if ($table == "devicecategory" || $table == "fieldsfromeditdevicetype") {

    $PAGE->navbar->add(get_string('managedevicestype', 'inventory'), new moodle_url('/mod/inventory/managedevicestype.php',
            array('id' => $cm->id)));
}

$PAGE->navbar->add(get_string('deleteelement', 'inventory') , '/mod/inventory/deletedatabaseelement',
        array('id' => $id, 'p' => $p, 'inpopup' => $inpopup, 'key' => $key, 'delete' => $delete, 'table' => $table,
            'building' => $building, 'room' => $room, 'oldid' => $oldid, 'editmode' => $editmode, 'categoryid' => $categoryid,
            'courseid' => $courseid, 'blockid' => $blockid, 'idreference' => $idreference,
            'editmodereference' => $editmodereference, 'arraykey' => $arraykey, 'sesskey' => $sesskey));



echo $OUTPUT->header();
if (!isset($options['printheading']) || !empty($options['printheading'])) {
    echo $OUTPUT->heading(format_string($inventory->name), 2);
}

if (!empty($options['printintro'])) {
    if (trim(strip_tags($inventory->intro))) {
        echo $OUTPUT->box_start('mod_introbox', 'inventoryintro');
        echo format_module_intro('inventory', $inventory, $cm->id);
        echo $OUTPUT->box_end();
    }
}

require_capability('mod/inventory:edit', $context);

// Depending on where we come from, we will go back to a different page.

if ($table == "buildings") {
    $originurl = "/mod/inventory/view.php?id=$id";
} else if ($table == "rooms") {
    $originurl = "/mod/inventory/listrooms.php?id=$id&amp;building=$building";
} else if ($table == "devices") {
    $originurl = "/mod/inventory/listdevices.php?id=$id&amp;room=$room";
} else if ($table == "references" || $table == "brandsfromdevice") {
    $originurl = "/mod/inventory/editdevice.php?id=$oldid&amp;courseid=$courseid&amp;blockid=$blockid&amp;"
            . "moduleid=$id&amp;roomid=$room&amp;editmode=$editmode&amp;categoryid=$categoryid";
} else if ($table == "brandsfromreference") {
    $originurl = "/mod/inventory/editreference.php?courseid=$courseid&blockid=$blockid&moduleid=$id&"
            . "id=$oldid&editmode=$editmode&categoryid=$categoryid&roomid=$room&"
            . "editmodereference=$editmodereference&idreference=$idreference";
} else if ($table == "devicecategory" || $table == "fieldsfromeditdevicetype") {
    $originurl = "/mod/inventory/managedevicestype.php?id=$id";
} else {
    $originurl = "/mod/inventory/view.php?id=$id";
}

// Delete only after the user have confirmed.

if ($delete == 1) {

    // Check the sesskey to ensure the user hasn't been tricked into coming here.

    if ($sesskey == sesskey()) {

        // Depending on what we are supposed to delete, we will use a different function.

        if ($table == "buildings") {
            $deleted = local_deletebuilding($key, $DB, $cm);
        } else if ($table == "rooms") {
            $deleted = local_deleteroom($key, $DB, $cm);
        } else if ($table == "devices") {
            $deleted = local_deletedevice($key, $DB, $cm);
        } else if ($table == "references") {
            $deleted = local_deletereference($key, $DB, $cm, 0);
        } else if ($table == "brandsfromdevice" || $table == "brandsfromreference") {
            $deleted = local_deletebrand($key, $DB, $cm, 0);
        } else if ($table == "devicecategory") {
            $deleted = local_deletedevicecategory($key, $DB, $cm);
        } else if ($table == "fieldsfromeditdevicetype") {
            $deleted = local_deletemultiplefields($arraykey, $DB);
        }

        // If there was no error, we redirect the user to its origin page.

        if ($deleted != -1) {

            $courseurl = new moodle_url($originurl);
            redirect($courseurl);
        }
    }

} else if ($delete == 0) {
    $courseurl = new moodle_url($originurl);
    redirect($courseurl);
}

if ($delete == 2) {

    if ($table == "buildings") {

        echo get_string('confirmdeletebuilding', 'inventory');
    } else if ($table == "rooms") {

        echo get_string('confirmdeleteroom', 'inventory');
    } else if ($table == "devices") {

        echo get_string('confirmdeletedevice', 'inventory');
    } else if ($table == "references") {

        echo get_string('confirmdeletereference', 'inventory');
    } else if ($table == "brandsfromdevice" || $table == "brandsfromreference") {

        echo get_string('confirmdeletebrand', 'inventory');
    } else if ($table == "devicecategory") {

        echo get_string('confirmdeletedevicecategory', 'inventory');
    } else if ($table == "fieldsfromeditdevicetype") {

        echo get_string('confirmdeletefields', 'inventory');
    }

    if ($arraykey != "") {

        $encodedarraykey = urlencode($arraykey);
    }


    echo
    "<p>"
    . "<a href='deletedatabaseelement.php?id=$id&amp;p=$p&amp;inpopup=$inpopup&amp;key=$key&amp;"
            . "delete=1&amp;table=$table&amp;building=$building&amp;room=$room&amp;oldid=$oldid&amp;"
            . "categoryid=$categoryid&amp;editmode=$editmode&amp;blockid=$blockid&amp;courseid=$courseid&amp;"
            . "arraykey=$encodedarraykey&amp;sesskey=$sesskey'><button>".get_string('yes', 'inventory')."</button></a>"
    . "<a href='deletedatabaseelement.php?id=$id&amp;"
        . "p=$p&amp;inpopup=$inpopup&amp;key=$key&amp;delete=0&amp;table=$table&amp;"
        . "building=$building&amp;room=$room&amp;oldid=$oldid&amp;categoryid=$categoryid&amp;editmode=$editmode&amp;"
        . "blockid=$blockid&amp;courseid=$courseid&amp;arraykey=$encodedarraykey&amp;"
        . "sesskey=$sesskey'><button>".get_string('no', 'inventory')."</button></a>"
    ."</p>";
} else if ($delete == 1 && $deleted == -1) {

    // If the delete function failed, we inform the user.

    $courseurl = new moodle_url($originurl);

    echo get_string('deleteerror', 'inventory');

    echo "<p><a href=$courseurl><button>".get_string('redirect', 'inventory')."</button></a></p>";

} else {

    // Should only happen if the user was tricked to come here.

    echo get_string('neverdisplayed', 'inventory');
}

echo $OUTPUT->footer();

// We delete the building, its image and all its rooms.

function local_deletebuilding($key, $DB, $cm) {

    if ($DB->record_exists('inventory_room', array('buildingid' => $key))) {
        $roomstodelete = $DB->get_records('inventory_room', array('buildingid' => $key));

        foreach ($roomstodelete as $roomkey => $value) {

            local_deleteroom($roomkey, $DB, $cm);
        }
    }

    $currentrecord = $DB->get_record('inventory_building', array('id' => $key));

    $fs = get_file_storage();
    $contextmodule = context_module::instance($cm->id);
    $filename = $currentrecord->imagename;

    // Prepare file record object.
    $fileinfo = array(
        'component' => 'mod_inventory',
        'filearea' => 'image',     // Usually = table name.
        'itemid' => $key,               // Usually = ID of row in table.
        'contextid' => $contextmodule->id, // ID of context.
        'filepath' => '/',           // Any path beginning and ending in /.
        'filename' => $filename); // Any filename.

    // Get file.
    $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
            $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

    // Delete it if it exists.
    if ($file) {
        $file->delete();
    }
    if ($DB->record_exists('inventory_building', array('id' => $key))) {

        $DB->delete_records('inventory_building', array('id' => $key));
    } else {

        return -1;
    }

    return 0;
}

// We delete the room, its attachments and all its devices.

function local_deleteroom($key, $DB, $cm) {

    if ($DB->record_exists('inventory_device', array('roomid' => $key))) {
        $devicestodelete = $DB->get_records('inventory_device', array('roomid' => $key));

        foreach ($devicestodelete as $devicekey => $value) {

            local_deletedevice($devicekey, $DB, $cm);
        }
    }

    if ($DB->record_exists('inventory_attachmentroom', array('roomid' => $key))) {

        $attachmentstodelete = $DB->get_records('inventory_attachmentroom', array('roomid' => $key));

        $fs = get_file_storage();
        $contextmodule = context_module::instance($cm->id);

        foreach ($attachmentstodelete as $attachmentkey => $attachmentvalue) {

            $filename = $attachmentvalue->name;

            if ($attachmentvalue->isprivate == 1) {

                $filearea = "privateattachment";
            } else {

                $filearea = "publicattachment";
            }

            // Prepare file record object.
            $fileinfo = array(
                'component' => 'mod_inventory',
                'filearea' => $filearea,     // Usually = table name.
                'itemid' => $key,               // Usually = ID of row in table.
                'contextid' => $contextmodule->id, // ID of context.
                'filepath' => '/',           // Any path beginning and ending in /.
                'filename' => $filename); // Any filename.

            // Get file.
            $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                    $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

            // Delete it if it exists.
            if ($file) {
                $file->delete();
            }
        }

        $DB->delete_records('inventory_attachmentroom', array('roomid' => $key));
    }

    if ($DB->record_exists('inventory_room', array('id' => $key))) {

        $DB->delete_records('inventory_room', array('id' => $key));
    } else {

        return -1;
    }

    return 0;
}

// We delete the device, its specific manual and all its values.

function local_deletedevice($key, $DB, $cm) {

    if ($DB->record_exists('inventory_devicevalue', array('deviceid' => $key))) {
        $valuestodelete = $DB->get_records('inventory_devicevalue', array('deviceid' => $key));

        foreach ($valuestodelete as $devicevaluekey => $value) {

            local_deletedevicevalue($devicevaluekey, $DB);
        }
    }

    $currentrecord = $DB->get_record('inventory_device', array('id' => $key));

    if ($currentrecord->documentation != null) {

        $fs = get_file_storage();
        $contextmodule = context_module::instance($cm->id);
        $filename = $currentrecord->documentation;

        // Prepare file record object .
        $fileinfo = array(
            'component' => 'mod_inventory',
            'filearea' => 'manuel',     // Usually = table name.
            'itemid' => $key,               // Usually = ID of row in table.
            'contextid' => $contextmodule->id, // ID of context.
            'filepath' => '/',           // Any path beginning and ending in /.
            'filename' => $filename); // Any filename.

        // Get file.
        $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

        // Delete it if it exists.
        if ($file) {
            $file->delete();
        }
    }

    if ($DB->record_exists('inventory_device', array('id' => $key))) {

        $DB->delete_records('inventory_device', array('id' => $key));
    } else {

        return -1;
    }

    return 0;
}

// We delete a value of a device.

function local_deletedevicevalue ($key, $DB) {

    if ($DB->record_exists('inventory_devicevalue', array('id' => $key))) {

        $DB->delete_records('inventory_devicevalue', array('id' => $key));
    } else {

        return -1;
    }

    return 0;
}

// We delete a reference, the manual of this reference and all devices of this reference.
// The first reference of a brand cannot be deleted, except when the brand is deleted.

function local_deletereference ($key, $DB, $cm, $forcedelete) {

    $stop = 0;

    if ($forcedelete != 1) {

        $brandid = $DB->get_record('inventory_reference', array('id' => $key))->brandid;

        $listrecordbrand = $DB->get_records('inventory_reference', array('brandid' => $brandid));

        foreach ($listrecordbrand as $recordkey => $recordvalue) {

            if ($recordkey == $key) {

                $stop = 1;
                break;
            } else {

                $stop = 0;
                break;
            }
        }
    }

    if ($stop != 1) {

        if ($DB->record_exists('inventory_device', array('refid' => $key))) {
            $devicestodelete = $DB->get_records('inventory_device', array('refid' => $key));

            foreach ($devicestodelete as $devicekey => $value) {

                local_deletedevice($devicekey, $DB, $cm);
            }
        }

        $currentrecord = $DB->get_record('inventory_reference', array('id' => $key));

        if ($currentrecord->documentation != null) {

            $fs = get_file_storage();
            $contextmodule = context_module::instance($cm->id);
            $filename = $currentrecord->documentation;

            // Prepare file record object.
            $fileinfo = array(
                'component' => 'mod_inventory',
                'filearea' => 'manuelreference',     // Usually = table name.
                'itemid' => $key,               // Usually = ID of row in table.
                'contextid' => $contextmodule->id, // ID of context.
                'filepath' => '/',           // Any path beginning and ending in /.
                'filename' => $filename); // Any filename.

            // Get file.
            $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                    $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

            // Delete it if it exists.
            if ($file) {
                $file->delete();
            }
        }

        if ($DB->record_exists('inventory_reference', array('id' => $key))) {

            $DB->delete_records('inventory_reference', array('id' => $key));
        } else {

            return -1;
        }

        return 0;
    } else {

        return -1;
    }
}

// We delete a brand and all of its references.
// We cannot delete rhe first brand of a category, except when we delete the category.

function local_deletebrand($key, $DB, $cm, $forcedelete) {

    $stop = 0;

    if ($forcedelete != 1) {

        $categoryid = $DB->get_record('inventory_brand', array('id' => $key))->categoryid;

        $listrecordcategories = $DB->get_records('inventory_brand', array('categoryid' => $categoryid));

        foreach ($listrecordcategories as $recordkey => $recordvalue) {

            if ($recordkey == $key) {

                $stop = 1;
                break;
            } else {

                $stop = 0;
                break;
            }
        }
    }

    if ($stop != 1) {

        if ($DB->record_exists('inventory_reference', array('brandid' => $key))) {
            $referencestodelete = $DB->get_records('inventory_reference', array('brandid' => $key));

            foreach ($referencestodelete as $referencekey => $value) {
                local_deletereference($referencekey, $DB, $cm, 1);
            }
        }

        if ($DB->record_exists('inventory_brand', array('id' => $key))) {

            $DB->delete_records('inventory_brand', array('id' => $key));
        } else {

            return -1;
        }

        return 0;
    } else {

        return -1;
    }
}

// We delete a category of device, all fields of this category, all devices of this category and all brand of this category.
// We also delete its icon.

function local_deletedevicecategory($key, $DB, $cm) {

    if ($DB->record_exists('inventory_devicefield', array('categoryid' => $key))) {
        $fieldstodelete = $DB->get_records('inventory_devicefield', array('categoryid' => $key));

        foreach ($fieldstodelete as $fieldkey => $fieldvalue) {
            local_deletedevicefield($fieldkey, $DB);
        }
    }

    if ($DB->record_exists('inventory_device', array('categoryid' => $key))) {
        $devicestodelete = $DB->get_records('inventory_device', array('categoryid' => $key));

        foreach ($devicestodelete as $devicekey => $devicevalue) {
            local_deletedevice($devicekey, $DB, $cm);
        }
    }

    if ($DB->record_exists('inventory_brand', array('categoryid' => $key))) {
        $brandstodelete = $DB->get_records('inventory_brand', array('categoryid' => $key));

        foreach ($brandstodelete as $brandkey => $brandvalue) {
            local_deletebrand($brandkey, $DB, $cm, 1);
        }
    }

    $currentrecord = $DB->get_record('inventory_devicecategory', array('id' => $key));

    if ($currentrecord->iconname != null) {

        $fs = get_file_storage();
        $contextmodule = context_module::instance($cm->id);
        $filename = $currentrecord->iconname;

        // Prepare file record object.
        $fileinfo = array(
            'component' => 'mod_inventory',
            'filearea' => 'icon',     // Usually = table name.
            'itemid' => $key,               // Usually = ID of row in table.
            'contextid' => $contextmodule->id, // ID of context.
            'filepath' => '/',           // Any path beginning and ending in /.
            'filename' => $filename); // Any filename.

        // Get file.
        $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

        // Delete it if it exists.
        if ($file) {
            $file->delete();
        }
    }

    if ($DB->record_exists('inventory_devicecategory', array('id' => $key))) {

        $DB->delete_records('inventory_devicecategory', array('id' => $key));
    } else {

        return -1;
    }

    return 0;
}

// We delete a field of the database and all values that refer to this field.

function local_deletedevicefield ($key, $DB) {

    if ($DB->record_exists('inventory_devicevalue', array('fieldid' => $key))) {

        $valuestodelete = $DB->get_records('inventory_devicevalue', array('fieldid' => $key));

        foreach ($valuestodelete as $valuekey => $value) {
            local_deletedevicevalue($valuekey, $DB);
        }
    }

    if ($DB->record_exists('inventory_devicefield', array('id' => $key))) {

        $DB->delete_records('inventory_devicefield', array('id' => $key));
    } else {

        return -1;
    }

    return 0;
}

// We delete multiple fields from the database.
// We parse the parameter to get the list of ids to delete.

function local_deletemultiplefields ($arraykey, $DB) {

    $newarraykey = str_replace("amp;", "", $arraykey);

    $finalarraykey = "";

    parse_str($newarraykey, $finalarraykey);

    foreach ($finalarraykey['arraykey'] as $key) {

        $error = local_deletedevicefield ($key, $DB);

        if ($error == -1) {

            return -1;
        }
    }

    return 0;
}
