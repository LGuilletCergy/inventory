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
 * File : editdevicetype.php
 * Page to add and edit a devicetype
 *
 */

require_once('../../config.php');
require_once('devicetype_form.php');
require_once('locallib.php');

global $DB, $OUTPUT, $PAGE, $USER;

// Check params.

$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$moduleid = required_param('moduleid', PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$editmode = required_param('editmode', PARAM_INT);
$roomid = optional_param('roomid', 0, PARAM_INT);
$deviceid = optional_param('deviceid', 0, PARAM_INT);
$editmodedevice = optional_param('editmodedevice', 0, PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$source = required_param('source', PARAM_TEXT);

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
$PAGE->set_url('/mod/inventory/editdevicetype.php',
        array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid,
            'id' => $id, 'editmode' => $editmode, 'roomid' => $roomid, 'deviceid' => $deviceid,
            'editmodedevice' => $editmodedevice, 'categoryid' => $categoryid,
            'source' => $source, 'currentstep' => $currentstep));
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
$editurl = new moodle_url('/mod/inventory/editdevicetype.php',
        array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid,
            'id' => $id, 'editmode' => $editmode, 'roomid' => $roomid, 'deviceid' => $deviceid,
            'editmodedevice' => $editmodedevice, 'categoryid' => $categoryid,
            'source' => $source, 'currentstep' => $currentstep));

require_capability('mod/inventory:edit', $context);

if ($source == "listDevices") {

    // Get buildind and room id.
    $currentrecord = $DB->get_record('inventory_room', array('id' => $roomid));
    $currentbuilding = $DB->get_record('inventory_building', array('id' => $currentrecord->buildingid));

    $PAGE->navbar->add($currentbuilding->name, new moodle_url('/mod/inventory/listRooms.php',
            array('id' => $moduleid, 'building' => $currentbuilding->id)));
    $PAGE->navbar->add($currentrecord->name, new moodle_url('/mod/inventory/listDevices.php',
            array('id' => $moduleid, 'room' => $roomid)));
} else {

    $PAGE->navbar->add(get_string('managedevicestype', 'inventory'),
            new moodle_url('/mod/inventory/managedevicestype.php', array('id' => $cm->id)));
}

$PAGE->navbar->add(get_string('editdevicetype', 'inventory'),
        new moodle_url('/mod/inventory/editdevicetype.php',
                array('courseid' => $courseid, 'blockid' => $blockid, 'moduleid' => $moduleid,
                    'id' => $id, 'editmode' => $editmode, 'roomid' => $roomid, 'deviceid' => $deviceid,
                    'editmodedevice' => $editmodedevice, 'categoryid' => $categoryid,
                    'source' => $source, 'currentstep' => $currentstep)));

if ($editmode == 1) {

    $currentrecord = $DB->get_record('inventory_devicecategory', array('id' => $categoryid));
}

$mform = new devicetype_form(null, array('categoryid' => $categoryid, 'editmode' => $editmode));
$formdata['blockid'] = $blockid;
$formdata['moduleid'] = $moduleid;
$formdata['courseid'] = $courseid;
$formdata['id'] = $id;
$formdata['editmode'] = $editmode;
$formdata['roomid'] = $roomid;
$formdata['deviceid'] = $deviceid;
$formdata['source'] = $source;

if ($editmode == 1) {

    $formdata['name'] = $currentrecord->name;
    $formdata['failurelink'] = $currentrecord->linkforfailure;
    $oldlongtext['text'] = $currentrecord->textforfailure;
    $formdata['failuretext'] = $oldlongtext;

    $listfields = $DB->get_records('inventory_devicefield', array('categoryid' => $categoryid));

    foreach ($listfields as $fieldkey => $fieldvalue) {

        $formdata['oldfield'.$fieldkey] = $fieldvalue->name;

        if ($fieldvalue->type == "shorttext") {

            $formdata['oldfieldtype'.$fieldkey] = 0;
        } else {

            $formdata['oldfieldtype'.$fieldkey] = 1;
        }
    }

    global $USER;
    $fs = get_file_storage();
    $usercontext = context_user::instance($USER->id);
    $contextmodule = context_module::instance($moduleid);

    $draftitemid = file_get_submitted_draft_itemid('icon');

    file_prepare_draft_area($draftitemid, $contextmodule->id, 'mod_inventory', 'icon', $categoryid,
                            array('maxfiles' => 1));

    $formdata['icon'] = $draftitemid;

    $formdata['categoryid'] = $categoryid;
}

$mform->set_data($formdata);

// Three possible states
if ($mform->is_cancelled()) { // First scenario : the form has been canceled.
    if (!$moduleid) {
        $moduleid = 1;
    }

    if ($source == "listDevices") {

        $courseurl = new moodle_url('/mod/inventory/listDevices.php', array('id' => $moduleid, 'room' => $roomid));
        redirect($courseurl);
    } else if ($source == "managedevicestype") {

        $courseurl = new moodle_url('/mod/inventory/managedevicestype.php', array('id' => $moduleid));
        redirect($courseurl);
    } else {

        $courseurl = new moodle_url('/mod/inventory/view.php', array('id' => $moduleid));
        redirect($courseurl);
    }
} else if ($mform->no_submit_button_pressed()) {

    /*
     * You need this section if you have a 'submit' button on your form
     * which performs some kind of subaction on the form and not a full
     * form submission.
     */

} else if ($submitteddata = $mform->get_data()) { // Second scenario : the form was validated.

    $submitteddata->uploadedat = time();
    if ($USER->id) {
        $submitteddatta->uploaderid = $USER->id;
    }

    // Store submitted data into database.
    if ($editmode) {

        global $USER;
        $fs = get_file_storage();
        $usercontext = context_user::instance($USER->id);
        $contextmodule = context_module::instance($moduleid);

        $listicons = $fs->get_area_files($usercontext->id, 'user', 'draft', $submitteddata->icon, 'id');

        foreach ($listicons as $icon) {

            if ($icon->get_filename() != ".") {
                $iconname = $icon->get_filename();
            }
        }

        $devicetypedata['iconname'] = $iconname;

        $devicetypedata['id'] = $submitteddata->categoryid;
        $devicetypedata['name'] = $submitteddata->name;

        $devicetypedata['linkforfailure'] = $submitteddata->failurelink;

        $submitteddatafailuretext = $submitteddata->failuretext;
        $devicetypedata['textforfailure'] = $submitteddatafailuretext['text'];

        $newfieldnumbers = $submitteddata->numnewfields;

        // Before update_record, we get the name of the old icon and we delete the url.

        $oldiconname = $currentrecord->iconname;

        // Prepare file record object.
        $fileinfo = array(
            'component' => 'mod_inventory',
            'filearea' => 'icon',     // Usually = table name.
            'itemid' => $key,               // Usually = ID of row in table.
            'contextid' => $contextmodule->id, // ID of context.
            'filepath' => '/',           // Any path beginning and ending in /.
            'filename' => $oldiconname); // Any filename.

        // Get file.
        $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

        // Delete it if it exists.
        if ($file) {
            $file->delete();
        }

        $error = $DB->update_record('inventory_devicecategory', $devicetypedata);

        file_save_draft_area_files($submitteddata->icon, $contextmodule->id, 'mod_inventory', 'icon',
               $submitteddata->categoryid, array('maxfiles' => 1));

        $fielddata['categoryid'] = $categoryid;

        $listfields = $DB->get_records('inventory_devicefield', array('categoryid' => $categoryid));

        $fieldstodelete = array();

        foreach ($listfields as $fieldkey => $fieldvalue) {

            $oldfieldfieldkey = oldfield.$fieldkey;
            $oldfieldtypefieldkey = oldfieldtype.$fieldkey;

            if ($submitteddata->$oldfieldfieldkey != "") {

                $fielddata['id'] = $fieldkey;

                $fielddata['name'] = $submitteddata->$oldfieldfieldkey;

                if ($submitteddata->$oldfieldtypefieldkey == 0) {

                    $fielddata['type'] = "shorttext";
                } else {

                    $fielddata['type'] = "longtext";
                }

                $fieldid = $DB->update_record('inventory_devicefield', $fielddata);

                if (!$fieldid) {

                    print_error('databaseerror', 'inventory');
                }
            } else {

                $fieldstodelete[] = $fieldkey;
            }
        }

        $i = 0;

        for ($i = 0; $i < $newfieldnumbers; $i++) {

            if ($submitteddata->repeatarray[$i]['field'] != "") {

                $fielddata['name'] = $submitteddata->repeatarray[$i]['field'];

                if ($submitteddata->repeatarray[$i]['fieldtype'] == 0) {

                    $fielddata['type'] = "shorttext";
                } else {

                    $fielddata['type'] = "longtext";
                }

                $fieldid = $DB->insert_record('inventory_devicefield', $fielddata);

                if (!$fieldid) {

                    print_error('databaseerror', 'inventory');
                }
            }
        }
    } else {

        global $USER;
        $fs = get_file_storage();
        $usercontext = context_user::instance($USER->id);
        $contextmodule = context_module::instance($moduleid);

        $listicons = $fs->get_area_files($usercontext->id, 'user', 'draft', $submitteddata->icon, 'id');

        foreach ($listicons as $icon) {

            if ($icon->get_filename() != ".") {
                $iconname = $icon->get_filename();
            }
        }

        $devicetypedata['iconname'] = $iconname;

        $devicetypedata['name'] = $submitteddata->name;

        $devicetypedata['linkforfailure'] = $submitteddata->failurelink;

        $submitteddatafailuretext = $submitteddata->failuretext;
        $devicetypedata['textforfailure'] = $submitteddatafailuretext['text'];

        $newfieldnumbers = $submitteddata->numnewfields;

        $i = 0;

        $categoryid = $DB->insert_record('inventory_devicecategory', $devicetypedata);

        // Add undefined brand to this category.

        $newbranddata['name'] = "undefined";
        $newbranddata['contact'] = "undefined";
        $newbranddata['categoryid'] = $categoryid;

        $brandid = $DB->insert_record('inventory_brand', $newbranddata);

        if (!$brandid) {

            print_error('databaseerror', 'inventory');
        }

        // Add undefined reference to this brand.

        $newreference['name'] = "undefined";
        $newreference['brandid'] = $brandid;

        $refid = $DB->insert_record('inventory_reference', $newreference);

        if (!$refid) {

            print_error('databaseerror', 'inventory');
        }

        file_save_draft_area_files($submitteddata->icon, $contextmodule->id, 'mod_inventory', 'icon',
               $categoryid, array('maxfiles' => 1));

        $fielddata['categoryid'] = $categoryid;

        for ($i = 0; $i < $newfieldnumbers; $i++) {

            if ($submitteddata->repeatarray[$i]['field'] != "") {

                $fielddata['name'] = $submitteddata->repeatarray[$i]['field'];

                if ($submitteddata->repeatarray[$i]['fieldtype'] == 0) {

                    $fielddata['type'] = "shorttext";
                } else {

                    $fielddata['type'] = "longtext";
                }

                $fieldid = $DB->insert_record('inventory_devicefield', $fielddata);

                if (!$fieldid) {

                    print_error('databaseerror', 'inventory');
                }
            }
        }
    }

    if (!$categoryid) {

        print_error('databaseerror', 'inventory');
    } else {

        if ($fieldstodelete == "" || $fieldstodelete == null) {

            if ($source == "listDevices") {

                $courseurl = new moodle_url('/mod/inventory/listDevices.php', array('id' => $moduleid, 'room' => $roomid));
                redirect($courseurl);
            } else if ($source == "managedevicestype") {

                $courseurl = new moodle_url('/mod/inventory/managedevicestype.php', array('id' => $moduleid));
                redirect($courseurl);
            } else {

                $courseurl = new moodle_url('/mod/inventory/view.php', array('id' => $moduleid));
                redirect($courseurl);
            }
        } else {

            $arraykey = http_build_query(array('arraykey' => $fieldstodelete));

            $encodedarraykey = urlencode($arraykey);

            $deletebrandurl = "deleteDatabaseElement.php?courseid=$courseid&blockid=$blockid&"
                    . "id=$moduleid&oldid=$id&editmode=$editmode&categoryid=$categoryid&"
                    . "key=0&table=fieldsfromeditdevicetype&arraykey=$encodedarraykey&sesskey=".sesskey();
            redirect($deletebrandurl);
        }
    }
}


$site = get_site();
echo $OUTPUT->header();

echo get_string('deleterule', 'inventory');

$mform->display();

echo $OUTPUT->footer();