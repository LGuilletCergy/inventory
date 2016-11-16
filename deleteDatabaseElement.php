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
 * File : deleteDatabaseElement.php
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

$PAGE->set_url('/mod/inventory/deleteDatabaseElement', array('id' => $id, 'p' => $p, 'inpopup' => $inpopup, 'key' => $key, 'delete' => $delete, 'table' => $table, 'building' => $building, 'room' => $room, 'oldid' => $oldid, 'editmode' => $editmode, 'categoryid' => $categoryid, 'courseid' => $courseid, 'blockid' => $blockid, 'idreference' => $idreference, 'editmodereference' => $editmodereference, 'arraykey' => $arraykey, 'sesskey' => $sesskey));

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

if($table == "rooms") {

    $currentbuilding = $DB->get_record('inventory_building', array('id' => $building));
    $PAGE->navbar->add($currentbuilding->name, new moodle_url('/mod/inventory/listRooms.php', array('id' => $id, 'building' => $currentbuilding->id)));
} else if ($table == "devices"){

    $currentroom = $DB->get_record('inventory_room', array('id' => $room));
    $currentbuilding = $DB->get_record('inventory_building', array('id' => $currentroom->buildingid));
    $PAGE->navbar->add($currentbuilding->name, new moodle_url('/mod/inventory/listRooms.php', array('id' => $id, 'building' => $currentbuilding->id)));
    $PAGE->navbar->add($currentroom->name, new moodle_url('/mod/inventory/listDevices.php', array('id' => $id, 'room' => $room)));
} else if ($table == "references" || $table == "brandsfromdevice") {

    $currentroom = $DB->get_record('inventory_room', array('id' => $room));
    $currentbuilding = $DB->get_record('inventory_building', array('id' => $currentroom->buildingid));

    $PAGE->navbar->add($currentbuilding->name, new moodle_url('/mod/inventory/listRooms.php', array('id' => $id, 'building' => $currentbuilding->id)));
    $PAGE->navbar->add($currentroom->name, new moodle_url('/mod/inventory/listDevices.php', array('id' => $id, 'room' => $currentrecord->roomid)));

    if($editmode == 0) {
        $PAGE->navbar->add(get_string('adddevice', 'inventory'), new moodle_url('/mod/inventory/editDevice.php', array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'roomid' => $roomid, 'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode)));
    } else {
        $PAGE->navbar->add(get_string('editdevice', 'inventory'), new moodle_url('/mod/inventory/editDevice.php', array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'roomid' => $roomid, 'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode)));
    }
} else if ($table == "brandsfromreference") {
    
    $currentroom = $DB->get_record('inventory_room', array('id' => $room));
    $currentbuilding = $DB->get_record('inventory_building', array('id' => $currentroom->buildingid));

    $PAGE->navbar->add($currentbuilding->name, new moodle_url('/mod/inventory/listRooms.php', array('id' => $id, 'building' => $currentbuilding->id)));
    $PAGE->navbar->add($currentroom->name, new moodle_url('/mod/inventory/listDevices.php', array('id' => $id, 'room' => $currentrecord->roomid)));

    if($editmodereference == 0) {
    $PAGE->navbar->add(get_string('addreference', 'inventory'), new moodle_url('/mod/inventory/editreference.php', array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'roomid' => $roomid, 'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode, 'idreference' => $idreference, 'editmodereference' => $editmodereference)));
    } else {
        $PAGE->navbar->add(get_string('editreference', 'inventory'), new moodle_url('/mod/inventory/editreference.php', array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid, 'roomid' => $roomid, 'categoryid' => $categoryid, 'id' => $id, 'editmode' => $editmode, 'idreference' => $idreference, 'editmodereference' => $editmodereference)));
    }
} else if ($table == "devicecategory" || $table == "fieldsfromeditdevicetype") {

    $PAGE->navbar->add(get_string('managedevicestype', 'inventory'), new moodle_url('/mod/inventory/managedevicestype.php', array('id' => $cm->id)));
}

$PAGE->navbar->add(get_string('deleteelement', 'inventory') , '/mod/inventory/deleteDatabaseElement', array('id' => $id, 'p' => $p, 'inpopup' => $inpopup, 'key' => $key, 'delete' => $delete, 'table' => $table, 'building' => $building, 'room' => $room, 'oldid' => $oldid, 'editmode' => $editmode, 'categoryid' => $categoryid, 'courseid' => $courseid, 'blockid' => $blockid, 'idreference' => $idreference, 'editmodereference' => $editmodereference, 'arraykey' => $arraykey, 'sesskey' => $sesskey));



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

$content = file_rewrite_pluginfile_urls($inventory->content, 'pluginfile.php', $context->id, 'mod_inventory', 'content', $inventory->revision);
$formatoptions = new stdClass;
$formatoptions->noclean = true;
$formatoptions->overflowdiv = true;
$formatoptions->context = $context;
$content = format_text($content, $inventory->contentformat, $formatoptions);
echo $OUTPUT->box($content, "generalbox center clearfix");

require_capability('mod/inventory:edit', $context);

if ($table == "buildings") {
    $originurl = "/mod/inventory/view.php?id=$id";
} else if ($table == "rooms") {
    $originurl = "/mod/inventory/listRooms.php?id=$id&amp;building=$building";
} else if ($table == "devices") {
    $originurl = "/mod/inventory/listDevices.php?id=$id&amp;room=$room";
} else if ($table == "references" || $table == "brandsfromdevice") {
    $originurl = "/mod/inventory/editDevice.php?id=$oldid&amp;courseid=$courseid&amp;blockid=$blockid&amp;moduleid=$id&amp;roomid=$room&amp;editmode=$editmode&amp;categoryid=$categoryid";
} else if ($table == "brandsfromreference") {
    $originurl = "/mod/inventory/editreference.php?courseid=$courseid&blockid=$blockid&moduleid=$id&id=$oldid&editmode=$editmode&categoryid=$categoryid&roomid=$room&editmodereference=$editmodereference&idreference=$idreference";
} else if ($table == "devicecategory" || $table == "fieldsfromeditdevicetype") {
    $originurl = "/mod/inventory/managedevicestype.php?id=$id";
} else {
    $originurl = "/mod/inventory/view.php?id=$id";
}

if ($delete == 1) {

    if ($sesskey == sesskey()) {

        if ($table == "buildings") {
            $deleted = deletebuilding($key, $DB, $cm);
        } else if ($table == "rooms") {
            $deleted = deleteroom($key, $DB, $cm);
        } else if ($table == "devices") {
            $deleted = deletedevice($key, $DB, $cm);
        } else if ($table == "references") {
            $deleted = deletereference($key, $DB, $cm, 0);
        } else if ($table == "brandsfromdevice" || $table == "brandsfromreference") {
            $deleted = deletebrand($key, $DB, $cm, 0);
        } else if ($table == "devicecategory") {
            $deleted = deletedevicecategory($key, $DB, $cm);
        } else if ($table == "fieldsfromeditdevicetype") {
            $deleted = deletemultiplefields($arraykey, $DB);
        }
        // DELETE Rajouter des fonctions au fur et à mesure.

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

    //Transformer ça en un formulaire moodle afin d'avoir une vérification de la sesskey

    echo get_string('confirmdelete', 'inventory');

    if ($arraykey != "") {

        $encodedarraykey = urlencode($arraykey);
    }
    

    echo
    "<p>
        <a href='deleteDatabaseElement.php?id=$id&amp;p=$p&amp;inpopup=$inpopup&amp;key=$key&amp;delete=1&amp;table=$table&amp;building=$building&amp;room=$room&amp;oldid=$oldid&amp;categoryid=$categoryid&amp;editmode=$editmode&amp;blockid=$blockid&amp;courseid=$courseid&amp;arraykey=$encodedarraykey&amp;sesskey=$sesskey'><button>".get_string('yes', 'inventory')."</button></a>
        <a href='deleteDatabaseElement.php?id=$id&amp;p=$p&amp;inpopup=$inpopup&amp;key=$key&amp;delete=0&amp;table=$table&amp;building=$building&amp;room=$room&amp;oldid=$oldid&amp;categoryid=$categoryid&amp;editmode=$editmode&amp;blockid=$blockid&amp;courseid=$courseid&amp;arraykey=$encodedarraykey&amp;sesskey=$sesskey'><button>".get_string('no', 'inventory')."</button></a>
    </p>";
} else if ($delete == 1 && $deleted == -1) {

    $courseurl = new moodle_url($originurl);

    echo get_string('deleteerror', 'inventory');

    echo "
    <p>
        <a href=$courseurl><button>".get_string('redirect', 'inventory')."</button></a>
    </p>";

} else {

    echo get_string('neverdisplayed', 'inventory');
}

$strlastmodified = get_string("lastmodified");
echo "<div class=\"modified\">$strlastmodified: ".userdate($inventory->timemodified)."</div>";

echo $OUTPUT->footer();

function deletebuilding($key, $DB, $cm) {

    if ($DB->record_exists('inventory_room', array('buildingid' => $key))) {
        $roomstodelete = $DB->get_records('inventory_room', array('buildingid' => $key));

        foreach ($roomstodelete as $roomkey => $value) {

            deleteroom($roomkey, $DB);
        }
    }

    $currentrecord = $DB->get_record('inventory_building', array('id' => $key));

    $fs = get_file_storage();
    $contextmodule = context_module::instance($cm->id);
    $filename = $currentrecord->imagename;

    // Prepare file record object
    $fileinfo = array(
        'component' => 'mod_inventory',
        'filearea' => 'image',     // usually = table name
        'itemid' => $key,               // usually = ID of row in table
        'contextid' => $contextmodule->id, // ID of context
        'filepath' => '/',           // any path beginning and ending in /
        'filename' => $filename); // any filename

    // Get file
    $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
            $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

    // Delete it if it exists
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

function deleteroom($key, $DB, $cm) {

    if ($DB->record_exists('inventory_device', array('roomid' => $key))) {
        $devicestodelete = $DB->get_records('inventory_device', array('roomid' => $key));

        foreach ($devicestodelete as $devicekey => $value) {

            deletedevice($devicekey, $DB);
        }
    }
    if ($DB->record_exists('inventory_room', array('id' => $key))) {
        
        $DB->delete_records('inventory_room', array('id' => $key));
    } else {

        return -1;
    }
    

    return 0;
}

function deletedevice($key, $DB, $cm) {

    if ($DB->record_exists('inventory_devicevalue', array('deviceid' => $key))) {
        $valuestodelete = $DB->get_records('inventory_devicevalue', array('deviceid' => $key));

        foreach ($valuestodelete as $devicevaluekey => $value) {

            deletedevicevalue($devicevaluekey, $DB);
        }
    }

    $currentrecord = $DB->get_record('inventory_device', array('id' => $key));

    if ($currentrecord->documentation != null) {

        $fs = get_file_storage();
        $contextmodule = context_module::instance($cm->id);
        $filename = $currentrecord->documentation;

        // Prepare file record object
        $fileinfo = array(
            'component' => 'mod_inventory',
            'filearea' => 'manuel',     // usually = table name
            'itemid' => $key,               // usually = ID of row in table
            'contextid' => $contextmodule->id, // ID of context
            'filepath' => '/',           // any path beginning and ending in /
            'filename' => $filename); // any filename

        // Get file
        $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

        // Delete it if it exists
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

function deletedevicevalue ($key, $DB) {

    if ($DB->record_exists('inventory_devicevalue', array('id' => $key))) {

        $DB->delete_records('inventory_devicevalue', array('id' => $key));
    } else {

        return -1;
    }

    return 0;
}

function deletereference ($key, $DB, $cm, $forcedelete) {

    $stop = 0;

    if ($forcedelete != 1) {

        $brandid = $DB->get_record('inventory_reference', array('id' => $key))->brandid;

        $listrecordbrand =$DB->get_records('inventory_reference', array('brandid' => $brandid));

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
                
                deletedevice($devicekey, $DB, $cm);
            }
        }

        $currentrecord = $DB->get_record('inventory_reference', array('id' => $key));

        if ($currentrecord->documentation != null) {

            $fs = get_file_storage();
            $contextmodule = context_module::instance($cm->id);
            $filename = $currentrecord->documentation;

            // Prepare file record object
            $fileinfo = array(
                'component' => 'mod_inventory',
                'filearea' => 'manuelreference',     // usually = table name
                'itemid' => $key,               // usually = ID of row in table
                'contextid' => $contextmodule->id, // ID of context
                'filepath' => '/',           // any path beginning and ending in /
                'filename' => $filename); // any filename

            // Get file
            $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                    $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

            // Delete it if it exists
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

function deletebrand($key, $DB, $cm, $forcedelete) {

    $stop = 0;

    if ($forcedelete != 1) {

        $categoryid = $DB->get_record('inventory_brand', array('id' => $key))->categoryid;

        $listrecordcategories =$DB->get_records('inventory_brand', array('categoryid' => $categoryid));

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

    if($stop != 1) {

        if ($DB->record_exists('inventory_reference', array('brandid' => $key))) {
            $referencestodelete = $DB->get_records('inventory_reference', array('brandid' => $key));

            foreach ($referencestodelete as $referencekey => $value) {
                deletereference($referencekey, $DB, $cm, 1);
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

function deletedevicecategory($key, $DB, $cm) {


    if ($DB->record_exists('inventory_devicefield', array('categoryid' => $key))) {
        $fieldstodelete = $DB->get_records('inventory_devicefield', array('categoryid' => $key));

        foreach ($fieldstodelete as $fieldkey => $fieldvalue) {
            deletedevicefield($fieldkey, $DB);
        }
    }

    if ($DB->record_exists('inventory_device', array('categoryid' => $key))) {
        $devicestodelete = $DB->get_records('inventory_device', array('categoryid' => $key));

        foreach ($devicestodelete as $devicekey => $devicevalue) {
            deletedevice($devicekey, $DB, $cm);
        }
    }

    if ($DB->record_exists('inventory_brand', array('categoryid' => $key))) {
        $brandstodelete = $DB->get_records('inventory_brand', array('categoryid' => $key));

        foreach ($brandstodelete as $brandkey => $brandvalue) {
            deletebrand($brandkey, $DB, $cm, 1);
        }
    }
    
    $currentrecord = $DB->get_record('inventory_devicecategory', array('id' => $key));

    if ($currentrecord->iconname != null) {

        $fs = get_file_storage();
        $contextmodule = context_module::instance($cm->id);
        $filename = $currentrecord->iconname;

        // Prepare file record object
        $fileinfo = array(
            'component' => 'mod_inventory',
            'filearea' => 'icon',     // usually = table name
            'itemid' => $key,               // usually = ID of row in table
            'contextid' => $contextmodule->id, // ID of context
            'filepath' => '/',           // any path beginning and ending in /
            'filename' => $filename); // any filename

        // Get file
        $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

        // Delete it if it exists
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

function deletedevicefield ($key, $DB) {

    if ($DB->record_exists('inventory_devicevalue', array('fieldid' => $key))) {
        
        $valuestodelete = $DB->get_records('inventory_devicevalue', array('fieldid' => $key));

        foreach ($valuestodelete as $valuekey => $value) {
            deletedevicevalue($valuekey, $DB);
        }
    }

    if ($DB->record_exists('inventory_devicefield', array('id' => $key))) {

        $DB->delete_records('inventory_devicefield', array('id' => $key));
    } else {

        return -1;
    }

    return 0;
}

function deletemultiplefields ($arraykey, $DB) {

    $newarraykey = str_replace("amp;", "", $arraykey);

    $finalarraykey = "";

    parse_str($newarraykey, $finalarraykey);

    foreach ($finalarraykey['arraykey'] as $key) {

        $error = deletedevicefield ($key, $DB);

        if ($error == -1) {

            return -1;
        }
    }

    return 0;
}