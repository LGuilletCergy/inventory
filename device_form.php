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
 * Define the form to create and edit a device
 *
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once("{$CFG->libdir}/formslib.php");

class device_form extends moodleform {

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
        $referenceid = $this->_customdata['referenceid'];
        
        $mform->addElement('header', 'addfileheader', get_string('devicedata', 'inventory'));

        $mform->addElement('text', 'type', get_string('type', 'inventory'), 'disabled');

        $tablebrands =  $DB->get_records_menu('inventory_brand', array('categoryid' => $categoryid), 'id', 'id, name');

        $addbrandurl = "editbrand.php?courseid=$courseid&blockid=$blockid&moduleid=$moduleid&id=$id&editmode=$editmode&categoryid=$categoryid&roomid=$roomid&editmodebrand=0&idbrand=$brandid&source=editdevice";
        $editbrandurl = "editbrand.php?courseid=$courseid&blockid=$blockid&moduleid=$moduleid&id=$id&editmode=$editmode&categoryid=$categoryid&roomid=$roomid&editmodebrand=1&idbrand=$brandid&source=editdevice";
        $deletebrandurl = "deleteDatabaseElement.php?courseid=$courseid&blockid=$blockid&id=$moduleid&oldid=$id&editmode=$editmode&categoryid=$categoryid&room=$roomid&key=$brandid&table=brandsfromdevice&sesskey=".sesskey();

        $brandarray = array();
        $brandarray[] =& $mform->createElement('select', 'brand', '', $tablebrands, array('onchange' => 'acquirereferences();'));
        $brandarray[] =& $mform->createElement('button', 'addbrand', get_string('addbrand', 'inventory'), array ('onclick' => "location.href='$addbrandurl'"));
        $brandarray[] =& $mform->createElement('button', 'editbrand', get_string('editbrand', 'inventory'), array ('onclick' => "location.href='$editbrandurl'"));
        $brandarray[] =& $mform->createElement('button', 'deletebrand', get_string('deletebrand', 'inventory'), array ('onclick' => "location.href='$deletebrandurl'"));
        $mform->addGroup($brandarray, 'brandarray', get_string('choosebrand', 'inventory'), array(''), false);
        
        $tablereferences =  $DB->get_records_menu('inventory_reference', array('brandid' => $brandid), 'id', 'id, name');

            
        $addreferenceurl = "editreference.php?courseid=$courseid&blockid=$blockid&moduleid=$moduleid&id=$id&editmode=$editmode&categoryid=$categoryid&roomid=$roomid&editmodereference=0&idreference=$referenceid";
        $editreferenceurl = "editreference.php?courseid=$courseid&blockid=$blockid&moduleid=$moduleid&id=$id&editmode=$editmode&categoryid=$categoryid&roomid=$roomid&editmodereference=1&idreference=$referenceid";
        $deletereferenceurl = "deleteDatabaseElement.php?courseid=$courseid&blockid=$blockid&id=$moduleid&oldid=$id&editmode=$editmode&categoryid=$categoryid&room=$roomid&key=$referenceid&table=references&sesskey=".sesskey();

        $referencearray = array();
        $referencearray[] =& $mform->createElement('select', 'reference', '', $tablereferences, array('onchange' => 'changereference();'));
        $referencearray[] =& $mform->createElement('button', 'addreference', get_string('addreference', 'inventory'), array ('onclick' => "location.href='$addreferenceurl'"));
        $referencearray[] =& $mform->createElement('button', 'editreference', get_string('editreference', 'inventory'), array ('onclick' => "location.href='$editreferenceurl'"));
        $referencearray[] =& $mform->createElement('button', 'deletereference', get_string('deletereference', 'inventory'), array ('onclick' => "location.href='$deletereferenceurl'"));
        $mform->addGroup($referencearray, 'referencearray', get_string('choosereference', 'inventory'), array(''), false);

        $listfields = $DB->get_records('inventory_devicefield', array('categoryid' => $categoryid));

        foreach ($listfields as $field) {

            if ($field->type == "shorttext") {

                $mform->addElement('text', 'numerofield'.$field->id, $field->name);
            } else {

                $mform->addElement('editor', 'numerofield'.$field->id, $field->name);
            }
        }

        $mform->addElement('filemanager', 'manuel', get_string('manuel', 'inventory'),
                null, array('maxbytes' => 0, 'maxfiles' => 1, 'accepted_types' => array('.pdf', 'document')));

        $mform->addElement('select', 'isworking', get_string('isworking', 'inventory'), array('Oui', 'Non'));

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

        $mform->addElement('hidden', 'roomid');
        $mform->setType('roomid', PARAM_INT);

        $mform->addElement('hidden', 'categoryid');
        $mform->setType('categoryid', PARAM_INT);

        $mform->addElement('hidden', 'referenceid');
        $mform->setType('referenceid', PARAM_INT);

        $mform->addElement('hidden', 'stringeditbrand', get_string('editbrand', 'inventory'));
        $mform->setType('stringeditbrand', PARAM_TEXT);

        $mform->addElement('hidden', 'stringdeletebrand', get_string('deletebrand', 'inventory'));
        $mform->setType('stringdeletebrand', PARAM_TEXT);

        $mform->addElement('hidden', 'stringeditreference', get_string('editreference', 'inventory'));
        $mform->setType('stringeditreference', PARAM_TEXT);

        $mform->addElement('hidden', 'stringdeletereference', get_string('deletereference', 'inventory'));
        $mform->setType('stringdeletereference', PARAM_TEXT);

        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_TEXT);

        $mform->disable_form_change_checker();


        $this->add_action_buttons();
    }
}

?>

<script type='text/javascript'>

    var xhr = null;

    function getXhr() {

        if(window.XMLHttpRequest) // Firefox et autres
           xhr = new XMLHttpRequest();
        else if(window.ActiveXObject){ // Internet Explorer
           try {
                    xhr = new ActiveXObject("Msxml2.XMLHTTP");
                } catch (e) {
                    xhr = new ActiveXObject("Microsoft.XMLHTTP");
                }
        }
        else { // XMLHttpRequest non supporte par le navigateur
           alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
           xhr = false;
        }
    }

    function acquirereferences() {

        getXhr();
        // On definit ce qu'on va faire quand on aura la reponse.
        xhr.onreadystatechange = function(){
            // On ne fait quelque chose que si on a tout recu et que le serveur est ok.
            if(xhr.readyState == 4 && xhr.status == 200){

                listreferences = xhr.responseText;
                // On se sert de innerHTML pour rajouter les options a la liste.
                document.getElementById('id_reference').innerHTML = listreferences;
                changereference();
            }
        }

        // Ici on va voir comment faire du post.
        xhr.open("POST","getreference.php",true);
        // Ne pas oublier ca pour le post.
        xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        // Ne pas oublier de poster les arguments.
        // Ici, l'id de l'auteur.
        brand = document.getElementById('id_brand');
        brandid = brand.options[brand.selectedIndex].value;
        xhr.send("brandid="+brandid);

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
        sesskey = document.getElementsByName('sesskey').item(0).value;
        
        stringeditbrand = document.getElementsByName('stringeditbrand').item(0).value;
        stringdeletebrand = document.getElementsByName('stringdeletebrand').item(0).value;

        urleditbrand = "editbrand.php?courseid=" + courseid + "&blockid=" + blockid + "&moduleid=" + moduleid + "&id=" + id + "&editmode=" + editmode + "&categoryid=" + categoryid + "&roomid=" + roomid + "&editmodebrand=1&idbrand=" + brandid + "&source=editdevice";

        editbrandbutton.outerHTML = '<input onclick=location.href="' + urleditbrand + '" name=editbrand value="' + stringeditbrand + '" type=button id=id_editbrand />';

        urldeletebrand = "deleteDatabaseElement.php?courseid=" + courseid + "&blockid=" + blockid + "&id=" + moduleid + "&oldid=" + id + "&editmode=" + editmode + "&categoryid=" + categoryid + "&room=" + roomid + "&key=" + brandid +"&table=brandsfromdevice&sesskey=" + sesskey;

        deletebrandbutton.outerHTML = '<input onclick=location.href="' + urldeletebrand + '" name=editbrand value="' + stringdeletebrand + '" type=button id=id_deletebrand />';
    }

    function changereference() {

        reference = document.getElementById('id_reference');
        referenceid = reference.options[reference.selectedIndex].value;

        referenceidelement = document.getElementsByName('referenceid').item(0);

        referenceidelement.outerHTML = '<input name=referenceid type=hidden value=' + referenceid + '>';

        editreferencebutton = document.getElementById('id_editreference');
        deletereferencebutton = document.getElementById('id_deletereference');

        blockid = document.getElementsByName('blockid').item(0).value;
        moduleid = document.getElementsByName('moduleid').item(0).value;
        courseid = document.getElementsByName('courseid').item(0).value;
        id = document.getElementsByName('id').item(0).value;
        editmode = document.getElementsByName('editmode').item(0).value;
        categoryid = document.getElementsByName('categoryid').item(0).value;
        roomid = document.getElementsByName('roomid').item(0).value;
        sesskey = document.getElementsByName('sesskey').item(0).value;

        stringeditreference = document.getElementsByName('stringeditreference').item(0).value;
        stringdeletereference = document.getElementsByName('stringdeletereference').item(0).value;

        urleditreference = "editreference.php?courseid=" + courseid + "&blockid=" + blockid + "&moduleid=" + moduleid + "&id=" + id + "&editmode=" + editmode + "&categoryid=" + categoryid + "&roomid=" + roomid + "&editmodereference=1&idreference=" + referenceid;

        editreferencebutton.outerHTML = '<input onclick=location.href="' + urleditreference + '" name=editreference value="' + stringeditreference + '" type=button id=id_editreference />';

        urldeletereference = "deleteDatabaseElement.php?courseid=" + courseid + "&blockid=" + blockid + "&id=" + moduleid + "&oldid=" + id + "&editmode=" + editmode + "&categoryid=" + categoryid + "&room=" + roomid + "&key=" + referenceid +"&table=references&sesskey=" + sesskey;

        deletereferencebutton.outerHTML = '<input onclick=location.href="' + urldeletereference + '" name=deletereference value="' + stringdeletereference + '" type=button id=id_deletereference />';
    }
</script>