<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2012 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Julien Dombre
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Supplier class (suppliers)
 */
class Supplier extends CommonDBTM {

   // From CommonDBTM
   public $dohistory = true;


   /**
    * Name of the type
    *
    * @param $nb : number of item in the type
   **/
   static function getTypeName($nb=0) {
      return _n('Supplier', 'Suppliers', $nb);
   }


   function canCreate() {
      return Session::haveRight('contact_enterprise', 'w');
   }


   function canView() {
      return Session::haveRight('contact_enterprise', 'r');
   }


   function cleanDBonPurge() {
      global $DB;

      $job = new Ticket();

      $cs  = new Contract_Supplier();
      $cs->cleanDBonItemDelete($this->getType(), $this->fields['id']);

      $cs  = new Contact_Supplier();
      $cs->cleanDBonItemDelete($this->getType(), $this->fields['id']);

      // Ticket rules use suppliers_id_assign
      Rule::cleanForItemAction($this, 'suppliers_id%');
   }


   function defineTabs($options=array()) {

      $ong = array();
      $this->addStandardTab('Contact_Supplier', $ong, $options);
      $this->addStandardTab('Contract_Supplier', $ong, $options);
      $this->addStandardTab('Infocom', $ong, $options);
      $this->addStandardTab('Document', $ong, $options);
      $this->addStandardTab('Ticket', $ong, $options);
      $this->addStandardTab('Link', $ong, $options);
      $this->addStandardTab('Note', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }


   /**
    * Print the enterprise form
    *
    * @param $ID Integer : Id of the computer or the template to print
    * @param $options array
    *     - target form target
    *     - withtemplate boolean : template or basic item
    *
    *@return Nothing (display)
   **/
   function showForm($ID, $options=array()) {

      $this->initForm($ID, $options);
      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Name')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";
      echo "<td>".__('Third party type')."</td>";
      echo "<td>";
      SupplierType::dropdown(array('value' => $this->fields["suppliertypes_id"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>". __('Phone')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "phonenumber");
      echo "</td>";
      echo "<td rowspan='8' class='middle right'>".__('Comments')."</td>";
      echo "<td class='center middle' rowspan='8'>";
      echo "<textarea cols='45' rows='13' name='comment' >".$this->fields["comment"]."</textarea>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Fax')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "fax");
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Website')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "website");
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>"._n('Email', 'Emails', 1)."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "email");
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td class='middle'>".__('Address')."</td>";
      echo "<td class='middle'>";
      echo "<textarea cols='37' rows='3' name='address'>".$this->fields["address"]."</textarea>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Postal code')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "postcode", array('size' => 10));
      echo "&nbsp;&nbsp;". __('City'). "&nbsp;";
      Html::autocompletionTextField($this, "town", array('size' => 23));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('State')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "state");
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Country')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "country");
      echo "</td></tr>";

      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;

   }

   function getSpecificMassiveActions($linkitem=NULL) {
      $isadmin = $this->canUpdate();
      $actions = parent::getSpecificMassiveActions();
      if ($isadmin) {
         $actions['add_contact'] = _x('button', 'Add a contact');
      }
      if (Session::haveRight('transfer','r')
            && Session::isMultiEntitiesMode()
            && $isadmin) {
         $actions['add_transfer_list'] = _x('button', 'Add to transfer list');
      }
      return $actions;
   }
   
   function getSearchOptions() {

      $tab                       = array();

      $tab['common']             = __('Characteristics');

      $tab[1]['table']           = $this->getTable();
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['itemlink_type']   = $this->getType();
      $tab[1]['massiveaction']   = false;

      $tab[2]['table']           = $this->getTable();
      $tab[2]['field']           = 'id';
      $tab[2]['name']            = __('ID');
      $tab[2]['massiveaction']   = false;

      $tab[3]['table']           = $this->getTable();
      $tab[3]['field']           = 'address';
      $tab[3]['name']            = __('Address');

      $tab[10]['table']          = $this->getTable();
      $tab[10]['field']          = 'fax';
      $tab[10]['name']           = __('Fax');

      $tab[11]['table']          = $this->getTable();
      $tab[11]['field']          = 'town';
      $tab[11]['name']           = __('City');

      $tab[14]['table']          = $this->getTable();
      $tab[14]['field']          = 'postcode';
      $tab[14]['name']           = __('Postal code');

      $tab[12]['table']          = $this->getTable();
      $tab[12]['field']          = 'state';
      $tab[12]['name']           = __('State');

      $tab[13]['table']          = $this->getTable();
      $tab[13]['field']          = 'country';
      $tab[13]['name']           = __('Country');

      $tab[4]['table']           = $this->getTable();
      $tab[4]['field']           = 'website';
      $tab[4]['name']            = __('Website');
      $tab[4]['datatype']        = 'weblink';

      $tab[5]['table']           = $this->getTable();
      $tab[5]['field']           = 'phonenumber';
      $tab[5]['name']            =  __('Phone');

      $tab[6]['table']           = $this->getTable();
      $tab[6]['field']           = 'email';
      $tab[6]['name']            = _n('Email', 'Emails', 1);
      $tab[6]['datatype']        = 'email';

      $tab[9]['table']           = 'glpi_suppliertypes';
      $tab[9]['field']           = 'name';
      $tab[9]['name']            = __('Third party type');

      $tab[8]['table']           = 'glpi_contacts';
      $tab[8]['field']           = 'completename';
      $tab[8]['name']            = _n('Associated contact', 'Associated contacts', 2);
      $tab[8]['forcegroupby']    = true;
      $tab[8]['datatype']        = 'itemlink';
      $tab[8]['itemlink_type']   = 'Contact';
      $tab[8]['massiveaction']   = false;
      $tab[8]['joinparams']      = array('beforejoin'
                                          => array('table'      => 'glpi_contacts_suppliers',
                                                   'joinparams' => array('jointype' => 'child')));

      $tab[16]['table']          = $this->getTable();
      $tab[16]['field']          = 'comment';
      $tab[16]['name']           = __('Comments');
      $tab[16]['datatype']       = 'text';

      $tab[90]['table']          = $this->getTable();
      $tab[90]['field']          = 'notepad';
      $tab[90]['name']           = __('Notes');
      $tab[90]['massiveaction']  = false;

      $tab[80]['table']          = 'glpi_entities';
      $tab[80]['field']          = 'completename';
      $tab[80]['name']           = __('Entity');
      $tab[80]['massiveaction']  = false;

      $tab[86]['table']          = $this->getTable();
      $tab[86]['field']          = 'is_recursive';
      $tab[86]['name']           = __('Child entities');
      $tab[86]['datatype']       = 'bool';

      $tab[29]['table']          = 'glpi_contracts';
      $tab[29]['field']          = 'name';
      $tab[29]['name']           = _n('Associated contract', 'Associated contracts', 2);
      $tab[29]['forcegroupby']   = true;
      $tab[29]['datatype']       = 'itemlink';
      $tab[29]['itemlink_type']  = 'Contract';
      $tab[29]['massiveaction']  = false;
      $tab[29]['joinparams']     = array('beforejoin'
                                          => array('table'      => 'glpi_contracts_suppliers',
                                                   'joinparams' => array('jointype' => 'child')));
      return $tab;
   }


   /**
    * Get links for an enterprise (website / edit)
    *
    * @param $withname boolean : also display name ? (false by default)
   **/
   function getLinks($withname=false) {
      global $CFG_GLPI;

      $ret = '&nbsp;&nbsp;&nbsp;&nbsp;';

      if ($withname) {
         $ret .= $this->fields["name"];
         $ret .= "&nbsp;&nbsp;";
      }

      if (!empty($this->fields['website'])) {
         $ret .= "<a href='".formatOutputWebLink($this->fields['website'])."' target='_blank'>
                  <img src='".$CFG_GLPI["root_doc"]."/pics/web.png' class='middle' alt=\"".
                   __s('Web')."\" title=\"".__s('Web')."\"></a>&nbsp;&nbsp;";
      }

      if ($this->can($this->fields['id'],'r')) {
         $ret .= "<a href='".$CFG_GLPI["root_doc"]."/front/supplier.form.php?id=".
                   $this->fields['id']."'>
                  <img src='".$CFG_GLPI["root_doc"]."/pics/edit.png' class='middle' alt=\"".
                   _sx('button', 'Update')."\" title=\""._sx('button', 'Update')."\"></a>";
      }
      return $ret;
   }


   /**
    * Show contacts asociated to an enterprise
   **/
   function showContacts() {
      global $DB,$CFG_GLPI;

      $instID = $this->fields['id'];
      if (!$this->can($instID,'r')) {
         return false;
      }
      $canedit = $this->can($instID,'w');

      $query = "SELECT `glpi_contacts`.*,
                       `glpi_contacts_suppliers`.`id` AS ID_ent,
                       `glpi_entities`.`id` AS entity
                FROM `glpi_contacts_suppliers`, `glpi_contacts`
                LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id`=`glpi_contacts`.`entities_id`)
                WHERE `glpi_contacts_suppliers`.`contacts_id`=`glpi_contacts`.`id`
                      AND `glpi_contacts_suppliers`.`suppliers_id` = '$instID'" .
                      getEntitiesRestrictRequest(" AND", "glpi_contacts", '', '', true) ."
                ORDER BY `glpi_entities`.`completename`, `glpi_contacts`.`name`";

      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $i      = 0;

      echo "<div class='firstbloc'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='9'>";
      if ($DB->numrows($result) == 0) {
         _e('No associated contact');
      } else {
         echo _n('Associated contact', 'Associated contacts', $DB->numrows($result));
      }
      echo "</th></tr>";

      echo "<tr><th>".__('Name')."</th>";
      echo "<th>".__('Entity')."</th>";
      echo "<th>". __('Phone')."</th>";
      echo "<th>". __('Phone 2')."</th>";
      echo "<th>".__('Mobile phone')."</th>";
      echo "<th>".__('Fax')."</th>";
      echo "<th>"._n('Email', 'Emails', 1)."</th>";
      echo "<th>".__('Type')."</th>";
      echo "<th>&nbsp;</th></tr>";

      $used = array();
      if ($number) {
         Session::initNavigateListItems('Contact',
         //TRANS : %1$s is the itemtype name, %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'), $this->getTypeName(1),
                                                $this->getName()));

         while ($data=$DB->fetch_assoc($result)) {
            $ID                = $data["ID_ent"];
            $used[$data["id"]] = $data["id"];
            Session::addToNavigateListItems('Contact',$data["id"]);

            echo "<tr class='tab_bg_1".($data["is_deleted"]?"_2":"")."'>";
            echo "<td class='center'>";
            echo "<a href='".$CFG_GLPI["root_doc"]."/front/contact.form.php?id=".$data["id"]."'>".
                   sprintf(__('%1$s %2$s'), $data["name"], $data["firstname"])."</a></td>";
            echo "<td class='center' width='100'>".Dropdown::getDropdownName("glpi_entities",
                                                                             $data["entity"])."</td>";
            echo "<td class='center' width='100'>".$data["phone"]."</td>";
            echo "<td class='center' width='100'>".$data["phone2"]."</td>";
            echo "<td class='center' width='100'>".$data["mobile"]."</td>";
            echo "<td class='center' width='100'>".$data["fax"]."</td>";
            echo "<td class='center'>";
            echo "<a href='mailto:".$data["email"]."'>".
                   $DB->result($result, $i, "glpi_contacts.email")."</a></td>";
            echo "<td class='center'>".Dropdown::getDropdownName("glpi_contacttypes",
                                                                 $data["contacttypes_id"])."</td>";
            echo "<td class='center' class='tab_bg_2'>";

            if ($canedit) {
               echo "<a href='".$CFG_GLPI["root_doc"].
                     "/front/contact.form.php?deletecontactsupplier=1&amp;id=$ID&amp;contacts_id=".
                     $data["id"]."'><img src='".$CFG_GLPI["root_doc"]."/pics/delete.png' alt='".
                     __s('Delete')."'></a>";
            } else {
               echo "&nbsp;";
            }
            echo "</td></tr>";
            $i++;
         }
      }

      echo "</table></div>";

      if ($canedit) {
         if ($this->fields["is_recursive"]) {
            $nb = countElementsInTableForEntity("glpi_contacts",
                                                getSonsOf("glpi_entities",
                                                          $this->fields["entities_id"]));
         } else {
            $nb = countElementsInTableForEntity("glpi_contacts", $this->fields["entities_id"]);
         }

         if ($nb > count($used)) {
            echo "<div class='spaced'>";
            echo "<form method='post' action=\"".$CFG_GLPI["root_doc"]."/front/contact.form.php\">";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_1'><th colspan='2'>".__('Add a contact')."</tr>";
            echo "<tr><td class='tab_bg_2 center'>";
            echo "<input type='hidden' name='suppliers_id' value='$instID'>";

            Contact::dropdown(array('used'        => $used,
                                    'entity'      => $this->fields["entities_id"],
                                    'entity_sons' => $this->fields["is_recursive"]));

            echo "</td><td class='tab_bg_2 center'>";
            echo "<input type='submit' name='addcontactsupplier' value=\""._sx('button', 'Add')."\"
                   class='submit'>";
            echo "</td></tr>";
         }
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }
   }


   /**
    * Print the HTML array for infocoms linked
    *
    *@return Nothing (display)
    *
   **/
   function showInfocoms() {
      global $DB, $CFG_GLPI;

      $instID = $this->fields['id'];
      if (!$this->can($instID,'r')) {
         return false;
      }

      $query = "SELECT DISTINCT `itemtype`
                FROM `glpi_infocoms`
                WHERE `suppliers_id` = '$instID'
                      AND `itemtype` NOT IN ('ConsumableItem', 'CartridgeItem', 'Software')
                ORDER BY `itemtype`";

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      echo "<div class='spaced'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>";
      Html::printPagerForm();
      echo "</th><th colspan='3'>";
      if ($DB->numrows($result) == 0) {
         _e('No associated item');
      } else {
         echo _n('Associated item', 'Associated items', $DB->numrows($result));
      }
      echo "</th></tr>";
      echo "<tr><th>".__('Type')."</th>";
      echo "<th>".__('Entity')."</th>";
      echo "<th>".__('Name')."</th>";
      echo "<th>".__('Serial number')."</th>";
      echo "<th>".__('Inventory number')."</th>";
      echo "</tr>";

      $num = 0;
      for ($i=0 ; $i < $number ; $i++) {
         $itemtype = $DB->result($result, $i, "itemtype");

         if (!($item = getItemForItemtype($itemtype))) {
            continue;
         }

         if ($item->canView()) {
            $linktype  = $itemtype;
            $linkfield = 'id';
            $itemtable = getTableForItemType($itemtype);

            $query = "SELECT `glpi_infocoms`.`entities_id`, `name`, `$itemtable`.*
                      FROM `glpi_infocoms`
                      INNER JOIN `$itemtable` ON (`$itemtable`.`id` = `glpi_infocoms`.`items_id`) ";

            // Set $linktype for entity restriction AND link to search engine
            if ($itemtype == 'Cartridge') {
               $query .= "INNER JOIN `glpi_cartridgeitems`
                            ON (`glpi_cartridgeitems`.`id`=`glpi_cartridges`.`cartridgeitems_id`) ";

               $linktype  = 'CartridgeItem';
               $linkfield = 'cartridgeitems_id';
            }

            if ($itemtype == 'Consumable' ) {
               $query .= "INNER JOIN `glpi_consumableitems`
                            ON (`glpi_consumableitems`.`id`=`glpi_consumables`.`consumableitems_id`) ";

               $linktype  = 'ConsumableItem';
               $linkfield = 'consumableitems_id';
            }

            $linktable = getTableForItemType($linktype);

            $query .= "WHERE `glpi_infocoms`.`itemtype` = '$itemtype'
                             AND `glpi_infocoms`.`suppliers_id` = '$instID'".
                             getEntitiesRestrictRequest(" AND", $linktable) ."
                       ORDER BY `glpi_infocoms`.`entities_id`,
                                `$linktable`.`name`";

            $result_linked = $DB->query($query);
            $nb            = $DB->numrows($result_linked);

            // Set $linktype for link to search engine pnly
            if (($itemtype == 'SoftwareLicense')
                && ($nb > $_SESSION['glpilist_limit'])) {
               $linktype  = 'Software';
               $linkfield = 'softwares_id';
            }

            if ($nb > $_SESSION['glpilist_limit']) {
               echo "<tr class='tab_bg_1'>";
               $title = $item->getTypeName($nb);
               if ($nb > 0) {
                  $title = sprintf(__('%1$s: %2$s'), $title, $nb);
               }
               echo "<td class='center'>".$title."</td>";
               echo "<td class='center' colspan='2'>";
               echo "<a href='". Toolbox::getItemTypeSearchURL($linktype) . "?" .
                      rawurlencode("contains[0]") . "=" . rawurlencode('$$$$'.$instID) . "&" .
                      rawurlencode("field[0]") . "=53&sort=80&order=ASC&is_deleted=0&start=0". "'>" .
                      __('Device list')."</a></td>";

               echo "<td class='center'>-</td><td class='center'>-</td></tr>";

            } else if ($nb) {
               for ($prem=true ; $data=$DB->fetch_assoc($result_linked) ; $prem=false) {
                  $name = $data["name"];
                  if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                     $name = sprintf(__('%1$s (%2$s)'), $name, $data["id"]);
                  }
                  $link = Toolbox::getItemTypeFormURL($linktype);
                  $name = "<a href=\"".$link."?id=".$data[$linkfield]."\">".$name."</a>";

                  echo "<tr class='tab_bg_1'>";
                  if ($prem) {
                     $title = $item->getTypeName($nb);
                     if ($nb > 0) {
                        $title = sprintf(__('%1$s: %2$s'), $title, $nb);
                     }
                     echo "<td class='center top' rowspan='$nb'>".$title."</td>";
                  }
                  echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",
                                                                       $data["entities_id"])."</td>";
                  echo "<td class='center";
                  echo ((isset($data['is_deleted']) && $data['is_deleted']) ?" tab_bg_2_2'" :"'").">";
                  echo $name."</td>";
                  echo "<td class='center'>".
                         (isset($data["serial"])?"".$data["serial"]."":"-")."</td>";
                  echo "<td class='center'>".
                         (isset($data["otherserial"])? "".$data["otherserial"]."" :"-")."</td>";
                  echo "</tr>";
               }
            }
            $num += $nb;
         }
      }
      echo "<tr class='tab_bg_2'>";
      echo "<td class='center'>".(($num > 0) ? sprintf(__('%1$s = %2$s'), __('Total'), $num)
                                             : "&nbsp;")."</td>";
      echo "<td colspan='4'>&nbsp;</td></tr> ";
      echo "</table></div>";
   }


   /**
    * Print an HTML array with contracts associated to the enterprise
    *
    *@return Nothing (display)
   **/
   function showContracts() {
      global $DB, $CFG_GLPI;

      $ID = $this->fields['id'];
      if (!Session::haveRight("contract","r")
          || !$this->can($ID,'r')) {
         return false;
      }
      $canedit = $this->can($ID,'w');

      $query = "SELECT `glpi_contracts`.*,
                       `glpi_contracts_suppliers`.`id` AS assocID,
                       `glpi_entities`.`id` AS entity
                FROM `glpi_contracts_suppliers`, `glpi_contracts`
                LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id`=`glpi_contracts`.`entities_id`)
                WHERE `glpi_contracts_suppliers`.`suppliers_id` = '$ID'
                      AND `glpi_contracts_suppliers`.`contracts_id`=`glpi_contracts`.`id`".
                      getEntitiesRestrictRequest(" AND", "glpi_contracts", '', '', true)."
                ORDER BY `glpi_entities`.`completename`,
                         `glpi_contracts`.`name`";

      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $i      = 0;

      echo "<form method='post' action='".$CFG_GLPI["root_doc"]."/front/contract.form.php'>";
      echo "<div class='spaced'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='7'>";
      if ($DB->numrows($result) == 0) {
         _e('No associated contract');
      } else {
         echo _n('Associated contract', 'Associated contracts', $DB->numrows($result));
      }
      echo "</th></tr>";

      echo "<tr><th>".__('Name')."</th>";
      echo "<th>".__('Entity')."</th>";
      echo "<th>"._x('phone', 'Number')."</th>";
      echo "<th>".__('Contract type')."</th>";
      echo "<th>".__('Start date')."</th>";
      echo "<th>".__('Initial contract period')."</th>";
      echo "<th>&nbsp;</th>";
      echo "</tr>";

      $used = array();
      while ($data=$DB->fetch_assoc($result)) {
         $cID        = $data["id"];
         $used[$cID] = $cID;
         $assocID    = $data["assocID"];

         echo "<tr class='tab_bg_1".($data["is_deleted"]?"_2":"")."'>";
         $name = $data["name"];
         if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
            $name = sprintf(__('%1$s (%2$s)'), $name, $data["id"]);
         }
         echo "<td class='center b'>
               <a href='".$CFG_GLPI["root_doc"]."/front/contract.form.php?id=$cID'>".$name."</a>";
         echo "</td>";
         echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities", $data["entity"]);
         echo "</td><td class='center'>".$data["num"]."</td>";
         echo "<td class='center'>".
                Dropdown::getDropdownName("glpi_contracttypes",$data["contracttypes_id"])."</td>";
         echo "<td class='center'>".Html::convDate($data["begin_date"])."</td>";
         echo "<td class='center'>".
               sprintf(_n('%d month', '%d months', $data["duration"]), $data["duration"]);

         if (($data["begin_date"] != '') && !empty($data["begin_date"])) {
            echo " -> ".Infocom::getWarrantyExpir($data["begin_date"], $data["duration"]);
         }
         echo "</td>";
         echo "<td class='tab_bg_2 center'>";

         if ($canedit) {
            echo "<a href='".$CFG_GLPI["root_doc"]."/front/contract.form.php?deletecontractsupplier=".
                   "1&amp;id=$assocID&amp;contracts_id=$cID'>";
            echo "<img src='".$CFG_GLPI["root_doc"]."/pics/delete.png' alt='".__s('Delete')."'></a>";
         } else {
            echo "&nbsp;";
         }
         echo "</td></tr>";
         $i++;
      }

      if ($canedit) {
         if ($this->fields["is_recursive"]) {
            $nb = countElementsInTableForEntity("glpi_contracts",
                                                getSonsOf("glpi_entities",
                                                          $this->fields["entities_id"]));
         } else {
            $nb = countElementsInTableForEntity("glpi_contracts", $this->fields["entities_id"]);
         }

         if ($nb > count($used)) {
            echo "<tr class='tab_bg_1'><td class='center' colspan='5'>";
            echo "<input type='hidden' name='suppliers_id' value='$ID'>";
            Contract::dropdown(array('used'        => $used,
                                    'entity'       => $this->fields["entities_id"],
                                    'entity_sons'  => $this->fields["is_recursive"],
                                    'nochecklimit' => true));
            echo "</td><td class='center'>";
            echo "<input type='submit' name='addcontractsupplier' value=\""._sx('button', 'Add')."\"
                   class='submit'>";
            echo "</td>";
            echo "<td>&nbsp;</td></tr>";
         }
      }
      echo "</table></div>";
      Html::closeForm();
   }

}
?>
