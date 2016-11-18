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
 * File : device_form.php
 * Define the form to create and edit a reference
 *
 */


if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once("{$CFG->libdir}/formslib.php");

class reference_form extends moodleform {

    public function definition() {

        global $DB;

        $mform =& $this->_form;
        $categoryid = $this->_customdata['categoryid'];
        $brandid = $this->_customdata['brandid'];
        $id = $this->_customdata['id'];
        $courseid = $this->_customdata['courseid'];
        $blockid = $this->_customdata['blockid'];
        $moduleid = $this->_customdata['moduleid'];
        $roomid = $this->_customdata['roomid'];
        $editmode = $this->_customdata['editmode'];
        $idreference = $this->_customdata['idreference'];
        $editmodereference = $this->_customdata['editmodereference'];

        $mform->addElement('header', 'addfileheader', get_string('referencedata', 'inventory'));

        $mform->addElement('text', 'name', get_string('name', 'inventory'));

        $tablebrands = $DB->get_records_menu('inventory_brand', array('categoryid' => $categoryid), 'id', 'id, name');

        $addbrandurl = "editbrand.php?courseid=$courseid&blockid=$blockid&moduleid=$moduleid&"
                . "id=$id&editmode=$editmode&categoryid=$categoryid&roomid=$roomid&editmodebrand=0&"
                . "idbrand=$brandid&source=editreference&idreference=$idreference&editmodereference=$editmodereference";
        $editbrandurl = "editbrand.php?courseid=$courseid&blockid=$blockid&moduleid=$moduleid&"
                . "id=$id&editmode=$editmode&categoryid=$categoryid&roomid=$roomid&editmodebrand=1&"
                . "idbrand=$brandid&source=editreference&idreference=$idreference&editmodereference=$editmodereference";
        $deletebrandurl = "deleteDatabaseElement.php?courseid=$courseid&blockid=$blockid&"
                . "id=$moduleid&oldid=$id&editmode=$editmode&categoryid=$categoryid&room=$roomid&"
                . "key=$brandid&table=brandsfromreference&idreference=$idreference&"
                . "editmodereference=$editmodereference&sesskey=".sesskey()."";

        $brandarray = array();
        $brandarray[] =& $mform->createElement('select', 'brand', '', $tablebrands, array('onchange' => 'acquirereferences();'));
        $brandarray[] =& $mform->createElement('button', 'addbrand', get_string('addbrand', 'inventory'),
                array ('onclick' => "location.href='$addbrandurl'"));
        $brandarray[] =& $mform->createElement('button', 'editbrand', get_string('editbrand', 'inventory'),
                array ('onclick' => "location.href='$editbrandurl'"));
        $brandarray[] =& $mform->createElement('button', 'deletebrand', get_string('deletebrand', 'inventory'),
                array ('onclick' => "location.href='$deletebrandurl'"));
        $mform->addGroup($brandarray, 'brandarray', get_string('choosebrand', 'inventory'), array(''), false);

        $mform->disabledIf('brand', 'isfirstreference', 'eq', 1);

        $mform->addElement('filemanager', 'manuel', get_string('manuel', 'inventory'),
                null, array('maxbytes' => 0, 'maxfiles' => 1, 'accepted_types' => array('.pdf', 'document')));

        $mform->addElement('hidden', 'blockid');
        $mform->setType('blockid', PARAM_INT);

        $mform->addElement('hidden', 'moduleid');
        $mform->setType('moduleid', PARAM_INT);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'editmode');
        $mform->setType('editmode', PARAM_INT);

        $mform->addElement('hidden', 'categoryid');
        $mform->setType('categoryid', PARAM_INT);

        $mform->addElement('hidden', 'brandid');
        $mform->setType('brandid', PARAM_INT);

        $mform->addElement('hidden', 'roomid');
        $mform->setType('roomid', PARAM_INT);

        $mform->addElement('hidden', 'idreference');
        $mform->setType('idreference', PARAM_INT);

        $mform->addElement('hidden', 'editmodereference');
        $mform->setType('editmodereference', PARAM_INT);

        $mform->addElement('hidden', 'isfirstreference');
        $mform->setType('isfirstreference', PARAM_INT);

        $mform->addElement('hidden', 'stringeditbrand', get_string('editbrand', 'inventory'));
        $mform->setType('stringeditbrand', PARAM_TEXT);

        $mform->addElement('hidden', 'stringdeletebrand', get_string('deletebrand', 'inventory'));
        $mform->setType('stringdeletebrand', PARAM_TEXT);

        $this->add_action_buttons();
    }
}

?>

<script type='text/javascript'>

    //If we change the brand, we need to change where the 'editBrand' and 'deleteBrand' buttons will lead us.

    function acquirereferences() {

        editbrandbutton = document.getElementById('id_editbrand');
        deletebrandbutton = document.getElementById('id_deletebrand');

        blockid = document.getElementsByName('blockid').item(0).value;
        moduleid = document.getElementsByName('moduleid').item(0).value;
        courseid = document.getElementsByName('courseid').item(0).value;
        id = document.getElementsByName('id').item(0).value;
        editmode = document.getElementsByName('editmode').item(0).value;
        categoryid = document.getElementsByName('categoryid').item(0).value;
        brand = document.getElementById('id_brand');
        brandid = brand.options[brand.selectedIndex].value;
        roomid = document.getElementsByName('roomid').item(0).value;
        idreference = document.getElementsByName('idreference').item(0).value;
        editmodereference = document.getElementsByName('editmodereference').item(0).value;
        sesskey = document.getElementsByName('sesskey').item(0).value;

        stringeditbrand = document.getElementsByName('stringeditbrand').item(0).value;
        stringdeletebrand = document.getElementsByName('stringdeletebrand').item(0).value;

        urleditbrand = "editbrand.php?courseid=" + courseid + "&blockid=" + blockid + "&moduleid=" + moduleid +
                "&id=" + id + "&editmode=" + editmode + "&categoryid=" + categoryid + "&roomid=" + roomid +
                "&editmodebrand=1&idbrand=" + brandid + "&source=editreference&idreference=" + idreference +
                "&editmodereference=" + editmodereference;

        editbrandbutton.outerHTML = '<input onclick=location.href="' + urleditbrand + '" name=editbrand value="' + stringeditbrand +
                '" type=button id=id_editbrand />';

        urldeletebrand = "deleteDatabaseElement.php?courseid=" + courseid + "&blockid=" + blockid +
                "&id=" + moduleid + "&oldid=" + id + "&editmode=" + editmode + "&categoryid=" + categoryid +
                "&room=" + roomid + "&key=" + brandid +"&table=brandsfromreference" + idreference +
                "&editmodereference=" + editmodereference + "&sesskey=" + sesskey;

        deletebrandbutton.outerHTML = '<input onclick=location.href="' + urldeletebrand +
                '" name=deletebrand value="' + stringdeletebrand + '" type=button id=id_deletebrand />';
    }
</script>