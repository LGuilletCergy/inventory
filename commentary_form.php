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
 * File : commentary_form.php
 * Define the form to create and edit a commentary of a room
 *
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once("{$CFG->libdir}/formslib.php");

class commentary_form extends moodleform {

    public function definition() {

        $mform =& $this->_form;

        $mform->addElement('header', 'addfileheader', get_string('commentarydata', 'inventory'));

        $mform->addElement('editor', 'commentary', get_string('commentary', 'inventory'));

        $mform->addElement('filemanager', 'attachment', get_string('attachment', 'inventory'),
                null, array('subdirs' => 0));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'p');
        $mform->setType('p', PARAM_INT);

        $mform->addElement('hidden', 'inpopup');
        $mform->setType('inpopup', PARAM_INT);

        $mform->addElement('hidden', 'room');
        $mform->setType('room', PARAM_INT);

        $mform->addElement('hidden', 'mode');
        $mform->setType('mode', PARAM_TEXT);

        $this->add_action_buttons();
    }
}