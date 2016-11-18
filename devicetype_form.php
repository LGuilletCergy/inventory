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
 * File : devicetype_form.php
 * Define the form to create and edit a devicetype
 *
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once("{$CFG->libdir}/formslib.php");

class devicetype_form extends moodleform {

    public function definition() {

        global $DB;

        $mform =& $this->_form;

        $categoryid = $this->_customdata['categoryid'];
        $editmode = $this->_customdata['editmode'];

        $mform->addElement('header', 'addfileheader', get_string('devicetypedata', 'inventory'));

        $mform->addElement('text', 'name', get_string('name', 'inventory'));

        $mform->addElement('text', 'failurelink', get_string('failurelink', 'inventory'));

        $mform->addElement('editor', 'failuretext', get_string('failuretext', 'inventory'));

        $listfields = $DB->get_records('inventory_devicefield', array('categoryid' => $categoryid));

        $i = 1;

        foreach ($listfields as $field) {

            $oldfieldsarray = array();

            $oldfieldsarray[] = $mform->createElement('text', 'oldfield'.$field->id);
            $oldfieldsarray[] = $mform->createElement('select', 'oldfieldtype'.$field->id, '', array('Texte court', 'Texte long'));

            $mform->addGroup($oldfieldsarray, 'oldfieldsarray', get_string('oldfield', 'inventory').' '.$i, array(''), false);

            $i++;
        }

        $repeatarray = array();

        $repeatarray[] = $mform->createElement('text', 'field');
        $repeatarray[] = $mform->createElement('select', 'fieldtype', '', array('Texte court', 'Texte long'));

        $repeatgroup = array();
        $repeatgroup[] = $mform->createElement('group', 'repeatarray', get_string('field', 'inventory').' {no}', $repeatarray);

        $repeatoptions = array();

        if ($editmode == 0) {

            $numrepeatgroupinitial = 1;
        } else {

            $numrepeatgroupinitial = 0;
        }

        $this->repeat_elements($repeatgroup, $numrepeatgroupinitial, $repeatoptions, 'numnewfields', 'addfields', 3);

        $mform->addElement('filemanager', 'icon', get_string('icon', 'inventory'),
                null, array('maxfiles' => 1, 'accepted_types' => array('image')));

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

        $mform->addElement('hidden', 'idbrand');
        $mform->setType('idbrand', PARAM_INT);

        $mform->addElement('hidden', 'roomid');
        $mform->setType('roomid', PARAM_INT);

        $mform->addElement('hidden', 'editmodebrand');
        $mform->setType('editmodebrand', PARAM_INT);

        $mform->addElement('hidden', 'source');
        $mform->setType('source', PARAM_TEXT);

        $mform->addElement('hidden', 'idreference');
        $mform->setType('idreference', PARAM_INT);

        $mform->addElement('hidden', 'editmodereference');
        $mform->setType('editmodereference', PARAM_INT);

        $this->add_action_buttons();
    }
}