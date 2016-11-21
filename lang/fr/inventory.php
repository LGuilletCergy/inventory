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
 * File : inventory.php
 * Contain the strings displayed on screen
 *
 */
$string['configdisplayoptions'] = 'Sélectionner toutes les options qui devraient êtres disponibles. Maintenez enfoncé la touche CTRL pour sélectionner plusieur champs';
$string['createinventory'] = 'Crée une nouvelle ressource Inventaire';
$string['displayoptions'] = 'Options d\'affichage disponible';
$string['displayselect'] = 'Affichage';
$string['displayselectexplain'] = 'Sélectionner le type d\'affichage.';
$string['legacyfiles'] = 'Migration des vieux fichiers de cours';
$string['legacyfilesactive'] = 'Acitf';
$string['legacyfilesdone'] = 'Fin';
$string['modulename'] = 'Inventaire';
$string['modulename_help'] = 'Le module inventaire est une activité permettant de lister tous les équipements dont dispose l\'université';
$string['modulename_link'] = 'mod/inventory/view';
$string['modulenameplural'] = 'Inventaires';
$string['optionsheader'] = 'Options d\'affichage';
$string['page-mod-inventory-x'] = 'N\'importe quelle page du module inventaire';
$string['inventory:addinstance'] = 'Ajouter une nouvelle ressource inventaire';
$string['inventory:newview'] = 'Voir le contenu de l\'inventaire';
$string['pluginadministration'] = 'Administration du module inventaire';
$string['pluginname'] = 'Inventaire';
$string['popupheight'] = 'Taille de la pop-up (en pixels)';
$string['popupheightexplain'] = 'Spécifie la taille par défaut des pop-ups.';
$string['popupwidth'] = 'Largeur de la pop-up (en pixels)';
$string['popupwidthexplain'] = 'Spécifie la largeur par défaut des pop-ups.';
$string['printheading'] = 'Affiche le nom de l\'inventaire';
$string['printheadingexplain'] = 'Afficher le nom de l\'inventaire au dessus du contenu ?';
$string['printintro'] = 'Affiche la description de l\'inventaire';
$string['printintroexplain'] = 'Affiche la description de l\'inventaire au dessus du contenu?';
$string['addfileheader'] = 'Ajouter/Modifier un élément à/de la base de données'; // Check if still relevant later on.
$string['name'] = 'Nom';
$string['city'] = 'Ville';
$string['department'] = 'Département';
$string['address'] = 'Adresse';
$string['phone'] = 'Numéro de téléphone';
$string['image'] = 'Image';
$string['commentary'] = 'Commentaire';
$string['attachment'] = 'Pièce jointe';
$string['type'] = 'Type d\'équipement';
$string['addbrand'] = 'Ajouter une marque';
$string['editbrand'] = 'Editer la marque sélectionnée';
$string['deletebrand'] = 'Supprimer la marque sélectionnée';
$string['choosebrand'] = 'Choisir une marque';
$string['addreference'] = 'Ajouter une référence';
$string['editreference'] = 'Editer la référence sélectionnée';
$string['deletereference'] = 'Supprimer la référence sélectionnée';
$string['choosereference'] = 'Choisir une référence';
$string['manuel'] = 'Manuel utilisateur';
$string['isworking'] = 'Cet équipement fonctionne t-il ?';
$string['failurelink'] = 'Lien en cas de problème';
$string['failuretext'] = 'Texte à afficher à l\'utilisateur si il rapporte un problème';;
$string['deleteoldfield'] = 'Supprimer ce champ de la base de données à l\'enregistrement';
$string['oldfield'] = 'Ancien champ';
$string['field'] = 'Champ';
$string['icon'] = 'Icône';
$string['addfile'] = 'Mise à jour de la base de données'; // Check if relevant later on.
$string['editdevice'] = 'Modifier l\'équipement'; // Check if relevant later on.
$string['editroom'] = 'Modifier la salle'; // Check if relevant later on.
$string['editbrand'] = 'Modifier la marque'; // Check if relevant later on.
$string['editbuilding'] = 'Modifier le batiment'; // Check if relevant later on.
$string['editcommentary'] = 'Modifier le commentaire'; // Check if relevant later on.
$string['editdevicetype'] = 'Modifier le type d\'équipement'; // Check if relevant later on.
$string['editreference'] = 'Modifier la référence'; // Check if relevant later on.
$string['failureintro'] = 'Veuillez trouvez ci-dessous les informations nécessaires sur l\'équipement :';
$string['devicetype'] = 'Type de l\'équipement';
$string['buildingname'] = 'Nom du batiment';
$string['roomname'] = 'Nome de la salle';
$string['reference'] = 'Référence de l\'équipement';
$string['iddevice'] = 'ID de l\'équipement dans la base de données';
$string['brand'] = 'Marque de l\'équipement';
$string['manuelreference'] = 'Manuel de la référence'; // Check if relevant later on.
$string['publicattachment'] = 'Pièce jointes publiques'; // Check if relevant later on.
$string['privateattachment'] = 'Pièces jointes privées'; // Check if relevant later on.
$string['createinventory'] = 'Créer un module inventaire';
$string['page-mod-inventory-x'] = 'Page'; // Check if relevant later on.
$string['publiccommentary'] = 'Commentaire public';
$string['privatecommentary'] = 'Commentaire privé';
$string['nocategory'] = 'Toutes catégories';
$string['isamphi'] = 'Est-ce un amphi ?';
$string['configdisplayoptions'] = 'Configurer les options d\'affichage';
$string['allbuildings'] = 'Afficher tous les batiments';
$string['contact'] = 'Contact';
$string['databaseerror'] = 'Problème avec la base de données';
$string['deleteerror'] = 'L\'élément n\'a pas pu être supprimé. Vous ne pouvez pas supprimer la première référence d\'une marque (undefined) ou la première marque d\'une catégorie (undefined).';
$string['neverdisplayed'] = 'Ceci ne devrait être affiché que si vous avez essayé de supprimer un élément de la base de données avec la mauvaise clé de session. Cela peut se produire si quelqu\'un vous a fourni un lien malveillant.';
$string['notfound'] = 'NON TROUVE';
$string['addbuilding'] = 'Ajouter un nouveau batiment';
$string['adddevicetype'] = 'Ajouter un nouveau type d\'équipement';
$string['addroom'] = 'Ajouter une nouvelle salle';
$string['add'] = 'Ajouter un(e) ';
$string['reportfailure'] = 'Signaler une panne';
$string['managedevicestype'] = 'Gérer les types d\'équipements';
$string['deleterule'] = 'Si l\'un des nouveaux champs est vide, il ne sera pas ajouté au type d\'équipement lors de l\'enregistrement et sera supprimé de la base de données s\'il appartenait précédemment à ce type d\'équipement';
$string['yes'] = 'Oui';
$string['no'] = 'Non';
$string['redirect'] = 'Retour à la page d\'origine';
$string['buildingdata'] = 'Données du batiment';
$string['roomdata'] = 'Données de la salle';
$string['commentarydata'] = 'Données du commentaire';
$string['devicedata'] = 'Données de l\'équipement';
$string['referencedata'] = 'Données de la référence';
$string['branddata'] = 'Données de la marque';
$string['devicetypedata'] = 'Données pour les équipements de ce type';
$string['reportfailure'] = 'Signaler une panne';
$string['deleteerror'] = 'L\'élément n\'a pas pu être édité. Vous ne pouvez pas éditer la première référence d\'une marque (undefined) ou la première marque d\'une catégorie (undefined).';
$string['inventory:edit'] = 'Modifier l\'inventaire';
$string['inventory:reportfailure'] = 'Signaler la panne d\'un appareil';
$string['deleteelement'] = 'Supprimer des éléments de la base de données';
$string['adddevice'] = 'Ajouter un équipement';
$string['failure'] = 'En panne';
$string['csvtitle'] = 'Liste des équipements dans les salles';
$string['exportroomsascsv'] = 'Exporter les équipements des salles sélectionnées dans un fichier csv';
$string['confirmdeletebuilding'] = 'Etes-vous sûr de vouloir supprimer ce batiment ? Celà supprimera également toutes ses salles et les équipements de ces dernières.';
$string['confirmdeleteroom'] = 'Etes-vous sûr de vouloir supprimer cette salle ? Celà supprimera également tous les équipements de cette salle.';
$string['confirmdeletedevice'] = 'Etes-vous sûr de vouloir supprimer cet équipement ?';
$string['confirmdeletereference'] = 'Etes-vous sûr de vouloir supprimer cette référence ? Celà supprimera également tous les équipements ayant cette référence.';
$string['confirmdeletebrand'] = 'Etes-vous sûr de vouloir supprimer cette marque ? Celà supprimera également toutes les références liés à cette marque et tout les équipements de cette marque.';
$string['confirmdeletedevicecategory'] = 'Etes-vous VRAIMENT sûr de vouloir supprimer cette catégorie d\'équipement ? Celà supprimera également tous les équipements de cette catégorie et toutes les marques et références de cette catégorie.';
$string['confirmdeletefields'] = 'Etes-vous sûr de vouloir supprimer ces champs de la base de données ? Tous les équipements de cette catégorie perdront toutes les informations liées à ces champs.';
