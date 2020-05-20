<?php
/* Copyright (C) 2001-2007 Rodolphe Quiedeville  <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2017 Laurent Destailleur   <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Eric Seigne           <eric.seigne@ryxeo.com>
 * Copyright (C) 2005      Marc Barilley / Ocebo <marc@ocebo.com>
 * Copyright (C) 2005-2013 Regis Houssin         <regis.houssin@inodbox.com>
 * Copyright (C) 2006      Andre Cianfarani      <acianfa@free.fr>
 * Copyright (C) 2010-2011 Juanjo Menent         <jmenent@2byte.es>
 * Copyright (C) 2010-2019 Philippe Grand        <philippe.grand@atoo-net.com>
 * Copyright (C) 2012      Christophe Battarel   <christophe.battarel@altairis.fr>
 * Copyright (C) 2013      Cédric Salvador       <csalvador@gpcsolutions.fr>
 * Copyright (C) 2016	   Ferran Marcet         <fmarcet@2byte.es>
 * Copyright (C) 2018	   Charlene Benke        <charlie@patas-monkey.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       	htdocs/supplier_proposal/list.php
 *	\ingroup    	supplier_proposal
 *	\brief      	Page of supplier proposals card and list
 */

define("SP", 'supplier_proposal');
define("ALPHA", 'alpha');
define("SSTATUS", 'search_status');
define("ALHTML", 'alphanohtml');
define("SPDL", 'sp.date_livraison');
define("SPREF", 'sp.ref');
define("LBL", 'label');
define("CHK", 'checked');
define("ST", 's.town');
define("SZIP", 's.zip');
define("SNOM", 'state.nom');
define("CCISO", 'country.code_iso');
define("TYCODE", 'typent.code');
define("SPDV", 'sp.date_valid');
define("SPTHT", 'sp.total_ht');
define("SPTVAT", 'sp.total_vat');
define("SPTTC", 'sp.total_ttc');
define("ULOG",'u.login');
define("POSITION", 'position');
define("SPDAT", 'sp.datec');
define("SPTMS", 'sp.tms');
define("SPFKS", 'sp.fk_statut');
define("LFTJOIN", " LEFT JOIN ");
define("PHPSELF", "PHP_SELF");
define("DIVCLASS", '<div class="divsearchfield">');
define("MAXWDH_300", 'maxwidth300');
define("TD", '</td>' );
define("NBF", 'nbfield');


require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formpropal.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/supplier_proposal/class/supplier_proposal.class.php';
if (!empty($conf->projet->enabled)){
	require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
}
// Load translation files required by the page
$langs->loadLangs(array('companies', 'propal', SP, 'compta', 'bills', 'orders', 'products'));

$socid = GETPOST('socid', 'int');

$action = GETPOST('action', ALPHA);
$massaction = GETPOST('massaction', ALPHA);
$show_files = GETPOST('show_files', 'int');
$confirm = GETPOST('confirm', ALPHA);
$toselect = GETPOST('toselect', 'array');
$contextpage = GETPOST('contextpage', 'aZ') ?GETPOST('contextpage', 'aZ') : 'supplierproposallist';

$search_user = GETPOST('search_user', 'int');
$search_sale = GETPOST('search_sale', 'int');
$search_ref = GETPOST('sf_ref') ?GETPOST('sf_ref', ALPHA) : GETPOST('search_ref', ALPHA);
$search_societe = GETPOST('search_societe', ALPHA);
$search_login = GETPOST('search_login', ALPHA);
$search_town = GETPOST('search_town', ALPHA);
$search_zip = GETPOST('search_zip', ALPHA);
$search_state = trim(GETPOST("search_state"));
$search_country = GETPOST("search_country", 'int');
$search_type_thirdparty = GETPOST("search_type_thirdparty", 'int');
$search_montant_ht = GETPOST('search_montant_ht', ALPHA);
$search_montant_vat = GETPOST('search_montant_vat', ALPHA);
$search_montant_ttc = GETPOST('search_montant_ttc', ALPHA);
$search_status = GETPOST(SSTATUS, ALPHA) ?GETPOST(SSTATUS, ALPHA) : GETPOST(SSTATUS, 'int');
$object_statut = $db->escape(GETPOST('supplier_proposal_statut'));
$search_btn = GETPOST('button_search', ALPHA);
$search_remove_btn = GETPOST('button_removefilter', ALPHA);

$sall = trim((GETPOST('search_all', ALHTML) != '') ?GETPOST('search_all', ALHTML) : GETPOST('sall', ALHTML));

$mesg = (GETPOST("msg") ? GETPOST("msg") : GETPOST("mesg"));
$year = GETPOST("year");
$month = GETPOST("month");
$day = GETPOST("day");
$yearvalid = GETPOST("yearvalid");
$monthvalid = GETPOST("monthvalid");
$dayvalid = GETPOST("dayvalid");

$limit = GETPOST('limit', 'int') ?GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST("sortfield", ALPHA);
$sortorder = GETPOST("sortorder", ALPHA);
$page = GETPOST("page", 'int');
if (empty($page) || $page == -1 || !empty($search_btn) || !empty($search_remove_btn) || (empty($toselect) && $massaction === '0')) { $page = 0; }     // If $page is not defined, or '' or -1
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (!$sortfield) $sortfield = SPDL;
if (!$sortorder) $sortorder = 'DESC';

if ($object_statut != '') $search_status = $object_statut;

// Nombre de ligne pour choix de produit/service predefinis
$NBLINES = 4;

// Security check
$module = SP;
$dbtable = '';
$objectid = '';
if (!empty($user->socid))	$socid = $user->socid;
if (!empty($socid))
{
	$objectid = $socid;
	$module = 'societe';
	$dbtable = '&societe';
}
$result = restrictedArea($user, $module, $objectid, $dbtable);

$diroutputmassaction = $conf->supplier_proposal->dir_output.'/temp/massgeneration/'.$user->id;

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$object = new SupplierProposal($db);
$hookmanager->initHooks(array('supplier_proposallist'));
$extrafields = new ExtraFields($db);

// fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');


// List of fields to search into when doing a "search in all"
$fieldstosearchall = array(
	SPREF=>'Ref',
	's.nom'=>'Supplier',
	'pd.description'=>'Description',
	'sp.note_public'=>'NotePublic',
);
if (empty($user->socid)) $fieldstosearchall["p.note_private"] = "NotePrivate";

$checkedtypetiers = 0;
$arrayfields = array(
	SPREF=>array(LBL=>$langs->trans("Ref"), CHK=>1),
	's.nom'=>array(LBL=>$langs->trans("Supplier"), CHK=>1),
	ST=>array(LBL=>$langs->trans("Town"), CHK =>1),
	SZIP=>array(LBL=>$langs->trans("Zip"), CHK =>1),
	SNOM=>array(LBL=>$langs->trans("StateShort"), CHK =>0),
	CCISO=>array(LBL=>$langs->trans("Country"), CHK =>0),
	TYCODE=>array(LBL=>$langs->trans("ThirdPartyType"), CHK =>$checkedtypetiers),
	SPDV=>array(LBL=>$langs->trans("Date"), CHK =>1),
	SPDL=>array(LBL=>$langs->trans("DateEnd"), CHK =>1),
	SPTHT=>array(LBL=>$langs->trans("AmountHT"),  CHK =>1),
	SPTVAT=>array(LBL=>$langs->trans("AmountVAT"), CHK =>0),
	SPTTC=>array(LBL=>$langs->trans("AmountTTC"), CHK =>0),
	ULOG=>array(LBL=>$langs->trans("Author"), CHK =>1, POSITION =>10),
	SPDAT=>array(LBL=>$langs->trans("DateCreation"), CHK =>0, POSITION =>500),
	SPTMS=>array(LBL=>$langs->trans("DateModificationShort"), CHK =>0, POSITION =>500),
	SPFKS=>array(LBL=>$langs->trans("Status"), CHK =>1, POSITION =>1000),
);
// Extra fields
if (is_array($extrafields->attributes[$object->table_element][LBL]) && count($extrafields->attributes[$object->table_element][LBL]) > 0)
{
	foreach ($extrafields->attributes[$object->table_element][LBL] as $key => $val)
	{
		if (!empty($extrafields->attributes[$object->table_element]['list'][$key])){
			$arrayfields["ef.".$key] = array(LBL=>$extrafields->attributes[$object->table_element][LBL][$key], CHK =>(($extrafields->attributes[$object->table_element]['list'][$key] < 0) ? 0 : 1), POSITION =>$extrafields->attributes[$object->table_element]['pos'][$key], 'enabled'=>(abs($extrafields->attributes[$object->table_element]['list'][$key]) != 3 && $extrafields->attributes[$object->table_element]['perms'][$key]));

		}

	}
}	
$object->fields = dol_sort_array($object->fields, POSITION );
$arrayfields = dol_sort_array($arrayfields, POSITION );



/*
 * Actions
 */

if (GETPOST('cancel', ALPHA)) { $action = 'list'; $massaction = ''; }
if (!GETPOST('confirmmassaction', ALPHA) && $massaction != 'presend' && $massaction != 'confirm_presend') { $massaction = ''; }

$parameters = array('socid'=>$socid);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}
include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

// Do we click on purge search criteria ?
if (GETPOST('button_removefilter_x', ALPHA) || GETPOST('button_removefilter.x', ALPHA) || GETPOST('button_removefilter', ALPHA)) // All tests are required to be compatible with all browsers
{
	$search_categ = '';
	$search_user = '';
	$search_sale = '';
	$search_ref = '';
	$search_societe = '';
	$search_montant_ht = '';
	$search_montant_vat = '';
	$search_montant_ttc = '';
	$search_login = '';
	$search_product_category = '';
	$search_town = '';
	$search_zip = "";
	$search_state = "";
	$search_type = '';
	$search_country = '';
	$search_type_thirdparty = '';
	$yearvalid = '';
	$monthvalid = '';
	$dayvalid = '';
	$year = '';
	$month = '';
	$day = '';
	$search_status = '';
	$object_statut = '';
}

if (empty($reshook))
{
	$objectclass = 'SupplierProposal';
	$objectlabel = 'SupplierProposals';
	$permissiontoread = $user->rights->supplier_proposal->lire;
	$permissiontodelete = $user->rights->supplier_proposal->supprimer;
	$uploaddir = $conf->supplier_proposal->dir_output;
	include DOL_DOCUMENT_ROOT.'/core/actions_massactions.inc.php';
}



/*
 * View
 */


$now = dol_now();

$form = new Form($db);
$formother = new FormOther($db);
$formfile = new FormFile($db);
$formpropal = new FormPropal($db);
$companystatic = new Societe($db);
$formcompany = new FormCompany($db);

$help_url = 'EN:Ask_Price_Supplier|FR:Demande_de_prix_fournisseur';


$sql = 'SELECT';
if ($sall || $search_product_category > 0) $sql = 'SELECT DISTINCT';
$sql .= ' s.rowid as socid, s.nom as name, s.town, s.zip, s.fk_pays, s.client, s.code_client,';
$sql .= " typent.code as typent_code,";
$sql .= " state.code_departement as state_code, state.nom as state_name,";
$sql .= ' sp.rowid, sp.note_private, sp.total_ht, sp.tva as total_vat, sp.total as total_ttc, sp.localtax1, sp.localtax2, sp.ref, sp.fk_statut, sp.fk_user_author, sp.date_valid, sp.date_livraison as dp,';
$sql .= ' sp.datec as date_creation, sp.tms as date_update,';
$sql .= " p.rowid as project_id, p.ref as project_ref,";
if (!$user->rights->societe->client->voir && !$socid) $sql .= " sc.fk_soc, sc.fk_user,";
$sql .= " u.firstname, u.lastname, u.photo, u.login";
// Add fields from extrafields
if (!empty($extrafields->attributes[$object->table_element][LBL])) {
	foreach ($extrafields->attributes[$object->table_element][LBL] as $key => $val) $sql .= ($extrafields->attributes[$object->table_element]['type'][$key] != 'separate' ? ", ef.".$key.' as options_'.$key : '');
}
// Add fields from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListSelect', $parameters); // Note that $action and $object may have been modified by hook
$sql .= $hookmanager->resPrint;
$sql .= ' FROM '.MAIN_DB_PREFIX.'societe as s';
$sql .= LFTJOIN.MAIN_DB_PREFIX."c_country as country on (country.rowid = s.fk_pays)";
$sql .= LFTJOIN.MAIN_DB_PREFIX."c_typent as typent on (typent.id = s.fk_typent)";
$sql .= LFTJOIN.MAIN_DB_PREFIX."c_departements as state on (state.rowid = s.fk_departement)";
$sql .= ', '.MAIN_DB_PREFIX.'supplier_proposal as sp';
if (is_array($extrafields->attributes[$object->table_element][LBL]) && count($extrafields->attributes[$object->table_element][LBL])) $sql .= LFTJOIN.MAIN_DB_PREFIX.$object->table_element."_extrafields as ef on (sp.rowid = ef.fk_object)";
if ($sall || $search_product_category > 0) $sql .= LFTJOIN.MAIN_DB_PREFIX.'supplier_proposaldet as pd ON sp.rowid=pd.fk_supplier_proposal';
if ($search_product_category > 0) $sql .= LFTJOIN.MAIN_DB_PREFIX.'categorie_product as cp ON cp.fk_product=pd.fk_product';
$sql .= LFTJOIN.MAIN_DB_PREFIX.'user as u ON sp.fk_user_author = u.rowid';
$sql .= LFTJOIN.MAIN_DB_PREFIX."projet as p ON p.rowid = sp.fk_projet";
// We'll need this table joined to the select in order to filter by sale
if ($search_sale > 0 || (!$user->rights->societe->client->voir && !$socid)) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
if ($search_user > 0)
{
	$sql .= ", ".MAIN_DB_PREFIX."element_contact as c";
	$sql .= ", ".MAIN_DB_PREFIX."c_type_contact as tc";
}
$sql .= ' WHERE sp.fk_soc = s.rowid';
$sql .= ' AND sp.entity IN ('.getEntity(SP).')';
if (!$user->rights->societe->client->voir && !$socid) //restriction
{
	$sql .= " AND s.rowid = sc.fk_soc AND sc.fk_user = ".$user->id;
}
if ($search_town)  $sql .= natural_search(ST, $search_town);
if ($search_zip)   $sql .= natural_search(SZIP, $search_zip);
if ($search_state) $sql .= natural_search(SNOM, $search_state);
if ($search_country) $sql .= " AND s.fk_pays IN (".$search_country.')';
if ($search_type_thirdparty) $sql .= " AND s.fk_typent IN (".$search_type_thirdparty.')';
if ($search_ref)     $sql .= natural_search(SPREF, $search_ref);
if ($search_societe) $sql .= natural_search('s.nom', $search_societe);
if ($search_login)  $sql .= natural_search(ULOG, $search_login);
if ($search_montant_ht) $sql .= natural_search('sp.total_ht=', $search_montant_ht, 1);
if ($search_montant_vat != '') $sql .= natural_search("sp.tva", $search_montant_vat, 1);
if ($search_montant_ttc != '') $sql .= natural_search("sp.total", $search_montant_ttc, 1);
if ($sall) $sql .= natural_search(array_keys($fieldstosearchall), $sall);
if ($socid) $sql .= ' AND s.rowid = '.$socid;
if ($search_status >= 0 && $search_status != '') $sql .= ' AND sp.fk_statut IN ('.$db->escape($search_status).')';
$sql .= dolSqlDateFilter(SPDL, $day, $month, $year);
$sql .= dolSqlDateFilter("sp.date_valid", $dayvalid, $monthvalid, $yearvalid);
if ($search_sale > 0) $sql .= " AND s.rowid = sc.fk_soc AND sc.fk_user = ".$search_sale;
if ($search_user > 0)
{
	$sql .= " AND c.fk_c_type_contact = tc.rowid AND tc.element='supplier_proposal' AND tc.source='internal' AND c.element_id = sp.rowid AND c.fk_socpeople = ".$search_user;
}
// Add where from extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_sql.tpl.php';
// Add where from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListWhere', $parameters); // Note that $action and $object may have been modified by hook
$sql .= $hookmanager->resPrint;

$sql .= $db->order($sortfield, $sortorder);
$sql .= ', sp.ref DESC';

// Count total nb of records
$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST))
{
	$resql = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($resql);
	if (($page * $limit) > $nbtotalofrecords)	// if total resultset is smaller then paging size (filtering), goto and load page 0
	{
		$page = 0;
		$offset = 0;
	}
}

$sql .= $db->plimit($limit + 1, $offset);

$resql = $db->query($sql);
if ($resql)
{
	$objectstatic = new SupplierProposal($db);
	$userstatic = new User($db);

	if ($socid > 0)
	{
		$soc = new Societe($db);
		$soc->fetch($socid);
		$title = $langs->trans('ListOfSupplierProposals').' - '.$soc->name;
	}
	else
	{
		$title = $langs->trans('ListOfSupplierProposals');
	}

	$num = $db->num_rows($resql);

	$arrayofselected = is_array($toselect) ? $toselect : array();

	if ($num == 1 && !empty($conf->global->MAIN_SEARCH_DIRECT_OPEN_IF_ONLY_ONE) && $sall)
	{
		$obj = $db->fetch_object($resql);

		$id = $obj->rowid;

		header("Location: ".DOL_URL_ROOT.'/supplier_proposal/card.php?id='.$id);

		exit;
	}

	llxHeader('', $langs->trans('CommRequest'), $help_url);

	$param = '';
	if (!empty($contextpage) && $contextpage != $_SERVER[PHPSELF]) $param .= '&contextpage='.$contextpage;
	if ($limit > 0 && $limit != $conf->liste_limit) $param .= '&limit='.$limit;
	if ($sall)				 $param .= '&sall='.$sall;
	if ($month)              $param .= '&month='.$month;
	if ($year)               $param .= '&year='.$year;
	if ($search_ref)         $param .= '&search_ref='.$search_ref;
	if ($search_societe)     $param .= '&search_societe='.$search_societe;
	if ($search_user > 0)    $param .= '&search_user='.$search_user;
	if ($search_sale > 0)    $param .= '&search_sale='.$search_sale;
	if ($search_montant_ht)  $param .= '&search_montant_ht='.$search_montant_ht;
	if ($search_login)  	 $param .= '&search_login='.$search_login;
	if ($search_town)		 $param .= '&search_town='.$search_town;
	if ($search_zip)		 $param .= '&search_zip='.$search_zip;
	if ($socid > 0)          $param .= '&socid='.$socid;
	if ($search_status != '') $param .= '&search_status='.$search_status;
	if ($optioncss != '') $param .= '&optioncss='.$optioncss;
	// Add $param from extra fields
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_param.tpl.php';

	// List of mass actions available
	$arrayofmassactions = array(
		'generate_doc'=>$langs->trans("ReGeneratePDF"),
		'builddoc'=>$langs->trans("PDFMerge"),
	    //'presend'=>$langs->trans("SendByMail"),
	);
	if ($user->rights->supplier_proposal->supprimer) $arrayofmassactions['predelete'] = '<span class="fa fa-trash paddingrightonly"></span>'.$langs->trans("Delete");
	if (in_array($massaction, array('presend', 'predelete'))) $arrayofmassactions = array();
	$massactionbutton = $form->selectMassAction('', $arrayofmassactions);

	$newcardbutton = '';
	if ($user->rights->supplier_proposal->creer)
	{
        $newcardbutton .= dolGetButtonTitle($langs->trans('NewAskPrice'), '', 'fa fa-plus-circle', DOL_URL_ROOT.'/supplier_proposal/card.php?action=create');
    }

	// Fields title search
	print '<form method="POST" id="searchFormList" action="'.$_SERVER[PHPSELF].'">';
	if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
	print '<input type="hidden" name="action" value="list">';
	print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
	print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
	print '<input type="hidden" name="page" value="'.$page.'">';

	print_barre_liste($title, $page, $_SERVER[PHPSELF], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, 'commercial', 0, $newcardbutton, '', $limit);

	$topicmail = "SendSupplierProposalRef";
	$modelmail = "supplier_proposal_send";
	$objecttmp = new SupplierProposal($db);
	$trackid = 'spro'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/tpl/massactions_pre.tpl.php';

	if ($sall)
	{
		foreach ($fieldstosearchall as $key => $val) $fieldstosearchall[$key] = $langs->trans($val);
		print '<div class="divsearchfieldfilter">'.$langs->trans("FilterOnInto", $sall).join(', ', $fieldstosearchall).'</div>';
	}

	$i = 0;

	$moreforfilter = '';

 	// If the user can view prospects other than his'
 	if ($user->rights->societe->client->voir || $socid)
 	{
 		$langs->load("commercial");
	 	$moreforfilter .= DIVCLASS;
	 	$moreforfilter .= $langs->trans('ThirdPartiesOfSaleRepresentative').': ';
		$moreforfilter .= $formother->select_salesrepresentatives($search_sale, 'search_sale', $user, 0, 1, MAXWDH_300);
	 	$moreforfilter .= '</div>';
 	}
	// If the user can view prospects other than his'
	if ($user->rights->societe->client->voir || $socid)
	{
		$moreforfilter .= DIVCLASS;
		$moreforfilter .= $langs->trans('LinkedToSpecificUsers').': ';
		$moreforfilter .= $form->select_dolusers($search_user, 'search_user', 1, '', 0, '', '', 0, 0, 0, '', 0, '', MAXWDH_300);
		$moreforfilter .= '</div>';
	}
	// If the user can view products
	if ($conf->categorie->enabled && ($user->rights->produit->lire || $user->rights->service->lire))
	{
		include_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
		$moreforfilter .= DIVCLASS;
		$moreforfilter .= $langs->trans('IncludingProductWithTag').': ';
		$cate_arbo = $form->select_all_categories(Categorie::TYPE_PRODUCT, null, 'parent', null, null, 1);
		$moreforfilter .= $form->selectarray('search_product_category', $cate_arbo, $search_product_category, 1, 0, 0, '', 0, 0, 0, 0, MAXWDH_300, 1);
		$moreforfilter .= '</div>';
	}
	$parameters = array();
	$reshook = $hookmanager->executeHooks('printFieldPreListTitle', $parameters); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) $moreforfilter .= $hookmanager->resPrint;
	else $moreforfilter = $hookmanager->resPrint;

	if (!empty($moreforfilter))
	{
		print '<div class="liste_titre liste_titre_bydiv centpercent">';
		print $moreforfilter;
		print '</div>';
	}

	$varpage = empty($contextpage) ? $_SERVER[PHPSELF] : $contextpage;
	$selectedfields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage); // This also change content of $arrayfields
	if ($massactionbutton) $selectedfields .= $form->showCheckAddButtons('checkforselect', 1);

	print '<div class="div-table-responsive">';
	print '<table class="tagtable liste'.($moreforfilter ? " listwithfilterbefore" : "").'">'."\n";

	print '<tr class="liste_titre_filter">';
	if (!empty($arrayfields[SPREF][CHK]))
	{
		print '<td class="liste_titre">';
		print '<input class="flat" size="6" type="text" name="search_ref" value="'.dol_escape_htmltag($search_ref).'">';
		print TD;
	}
	if (!empty($arrayfields['s.nom'][CHK]))
	{
		print '<td class="liste_titre left">';
		print '<input class="flat" type="text" size="12" name="search_societe" value="'.dol_escape_htmltag($search_societe).'">';
		print TD;
	}
	if (!empty($arrayfields[ST][CHK])) print '<td class="liste_titre"><input class="flat" type="text" size="6" name="search_town" value="'.$search_town.'"></td>';
	if (!empty($arrayfields[SZIP][CHK])) print '<td class="liste_titre"><input class="flat" type="text" size="4" name="search_zip" value="'.$search_zip.'"></td>';
	// State
	if (!empty($arrayfields[SNOM][CHK]))
	{
		print '<td class="liste_titre">';
		print '<input class="flat" size="4" type="text" name="search_state" value="'.dol_escape_htmltag($search_state).'">';
		print TD;
	}
	// Country
	if (!empty($arrayfields[CCISO][CHK]))
	{
		print '<td class="liste_titre center">';
		print $form->select_country($search_country, 'search_country', '', 0, 'minwidth100imp maxwidth100');
		print TD;
	}
	// Company type
	if (!empty($arrayfields[TYCODE][CHK]))
	{
		print '<td class="liste_titre maxwidthonsmartphone center">';
		print $form->selectarray("search_type_thirdparty", $formcompany->typent_array(0), $search_type_thirdparty, 0, 0, 0, '', 0, 0, 0, (empty($conf->global->SOCIETE_SORT_ON_TYPEENT) ? 'ASC' : $conf->global->SOCIETE_SORT_ON_TYPEENT));
		print TD;
	}
	// Date
	if (!empty($arrayfields[SPDV][CHK]))
	{
		print '<td class="liste_titre center" colspan="1">';
		//print $langs->trans('Month').': ';
		print '<input class="flat width25 valignmiddle" type="text" maxlength="2" name="monthvalid" value="'.dol_escape_htmltag($monthvalid).'">';
		//print '&nbsp;'.$langs->trans('Year').': ';
		$syearvalid = $yearvalid;
		$formother->select_year($syearvalid, 'yearvalid', 1, 20, 5);
		print TD;
	}
	// Date
	if (!empty($arrayfields[SPDL][CHK]))
	{
		print '<td class="liste_titre center" colspan="1">';
		//print $langs->trans('Month').': ';
		print '<input class="flat width25 valignmiddle" type="text" maxlength="2" name="month" value="'.dol_escape_htmltag($month).'">';
		//print '&nbsp;'.$langs->trans('Year').': ';
		$syear = $year;
		$formother->select_year($syear, 'year', 1, 20, 5);
		print TD;
	}

	if (!empty($arrayfields[SPTHT][CHK]))
	{
		// Amount
		print '<td class="liste_titre right">';
		print '<input class="flat" type="text" size="5" name="search_montant_ht" value="'.dol_escape_htmltag($search_montant_ht).'">';
		print TD;
	}
	if (!empty($arrayfields[SPTVAT][CHK]))
	{
		// Amount
		print '<td class="liste_titre right">';
		print '<input class="flat" type="text" size="5" name="search_montant_vat" value="'.dol_escape_htmltag($search_montant_vat).'">';
		print TD;
	}
	if (!empty($arrayfields[SPTTC][CHK]))
	{
		// Amount
		print '<td class="liste_titre right">';
		print '<input class="flat" type="text" size="5" name="search_montant_ttc" value="'.dol_escape_htmltag($search_montant_ttc).'">';
		print TD;
	}
	if (!empty($arrayfields[ULOG][CHK]))
	{
		// Author
		print '<td class="liste_titre center">';
		print '<input class="flat" size="4" type="text" name="search_login" value="'.dol_escape_htmltag($search_login).'">';
		print TD;
	}
	// Extra fields
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_input.tpl.php';
	// Fields from hook
	$parameters = array('arrayfields'=>$arrayfields);
	$reshook = $hookmanager->executeHooks('printFieldListOption', $parameters); // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;
	// Date creation
	if (!empty($arrayfields[SPDAT][CHK]))
	{
		print '<td class="liste_titre">';
		print TD;
	}
	// Date modification
	if (!empty($arrayfields[SPTMS][CHK]))
	{
		print '<td class="liste_titre">';
		print TD;
	}
	// Status
	if (!empty($arrayfields[SPFKS][CHK]))
	{
		print '<td class="liste_titre maxwidthonsmartphone right">';
		$formpropal->selectProposalStatus($search_status, 1, 0, 1, 'supplier', SSTATUS);
		print TD;
	}
	// Action column
	print '<td class="liste_titre middle">';
	$searchpicto = $form->showFilterButtons();
	print $searchpicto;
	print TD;

	print "</tr>\n";

	// Fields title
	print '<tr class="liste_titre">';
	if (!empty($arrayfields[SPREF][CHK]))           print_liste_field_titre($arrayfields[SPREF][LBL], $_SERVER[PHPSELF], SPREF, '', $param, '', $sortfield, $sortorder);
	if (!empty($arrayfields['s.nom'][CHK]))            print_liste_field_titre($arrayfields['s.nom'][LBL], $_SERVER[PHPSELF], 's.nom', '', $param, '', $sortfield, $sortorder);
	if (!empty($arrayfields[ST][CHK]))           print_liste_field_titre($arrayfields[ST][LBL], $_SERVER[PHPSELF], ST, '', $param, '', $sortfield, $sortorder);
	if (!empty($arrayfields[SZIP][CHK]))            print_liste_field_titre($arrayfields[SZIP][LBL], $_SERVER[PHPSELF], SZIP, '', $param, '', $sortfield, $sortorder);
	if (!empty($arrayfields[SNOM][CHK]))        print_liste_field_titre($arrayfields[SNOM][LBL], $_SERVER[PHPSELF], SNOM, "", $param, '', $sortfield, $sortorder);
	if (!empty($arrayfields[CCISO][CHK])) print_liste_field_titre($arrayfields[CCISO][LBL], $_SERVER[PHPSELF], "country.code_iso", "", $param, '', $sortfield, $sortorder, 'center ');
	if (!empty($arrayfields[TYCODE][CHK]))      print_liste_field_titre($arrayfields[TYCODE][LBL], $_SERVER[PHPSELF], "typent.code", "", $param, '', $sortfield, $sortorder, 'center ');
	if (!empty($arrayfields[SPDV][CHK]))      print_liste_field_titre($arrayfields[SPDV][LBL], $_SERVER[PHPSELF], SPDV, '', $param, '', $sortfield, $sortorder, 'center ');
	if (!empty($arrayfields[SPDL][CHK]))  print_liste_field_titre($arrayfields[SPDL][LBL], $_SERVER[PHPSELF], SPDL, '', $param, '', $sortfield, $sortorder, 'center ');
	if (!empty($arrayfields[SPTHT][CHK]))        print_liste_field_titre($arrayfields[SPTHT][LBL], $_SERVER[PHPSELF], SPTHT, '', $param, '', $sortfield, $sortorder, 'right ');
	if (!empty($arrayfields[SPTVAT][CHK]))       print_liste_field_titre($arrayfields[SPTVAT][LBL], $_SERVER[PHPSELF], SPTVAT, '', $param, '', $sortfield, $sortorder, 'right ');
	if (!empty($arrayfields[SPTTC][CHK]))       print_liste_field_titre($arrayfields[SPTTC][LBL], $_SERVER[PHPSELF], SPTTC, '', $param, '', $sortfield, $sortorder, 'right ');
	if (!empty($arrayfields[ULOG][CHK]))            print_liste_field_titre($arrayfields[ULOG][LBL], $_SERVER[PHPSELF], ULOG, '', $param, '', $sortfield, $sortorder, 'center ');
	// Extra fields
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_title.tpl.php';
	// Hook fields
	$parameters = array('arrayfields'=>$arrayfields, 'param'=>$param, 'sortfield'=>$sortfield, 'sortorder'=>$sortorder);
	$reshook = $hookmanager->executeHooks('printFieldListTitle', $parameters); // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;
	if (!empty($arrayfields[SPDAT][CHK]))     print_liste_field_titre($arrayfields[SPDAT][LBL], $_SERVER[PHPSELF], "sp.datec", "", $param, '', $sortfield, $sortorder, 'center nowrap ');
	if (!empty($arrayfields[SPTMS][CHK]))       print_liste_field_titre($arrayfields[SPTMS][LBL], $_SERVER[PHPSELF], "sp.tms", "", $param, '', $sortfield, $sortorder, 'center nowrap');
	if (!empty($arrayfields[SPFKS][CHK])) print_liste_field_titre($arrayfields[SPFKS][LBL], $_SERVER[PHPSELF], "sp.fk_statut", "", $param, '', $sortfield, $sortorder, 'right ');
	print_liste_field_titre($selectedfields, $_SERVER[PHPSELF], "", '', '', '', $sortfield, $sortorder, 'center maxwidthsearch ');
	print '</tr>'."\n";

	$now = dol_now();
	$i = 0;
	$total = 0;
	$subtotal = 0;
	$totalarray = array();
	while ($i < min($num, $limit))
	{
		$obj = $db->fetch_object($resql);

		$objectstatic->id = $obj->rowid;
		$objectstatic->ref = $obj->ref;
		$objectstatic->note_public = $obj->note_public;
		$objectstatic->note_private = $obj->note_private;

		print '<tr class="oddeven">';

		if (!empty($arrayfields[SPREF][CHK]))
		{
			print '<td class="nowrap">';

			print '<table class="nobordernopadding"><tr class="nocellnopadd">';
			// Picto + Ref
			print '<td class="nobordernopadding nowrap">';
			print $objectstatic->getNomUrl(1, '', '', 0, -1, 1);
			print TD;
			// Warning
			$warnornote = '';
			//if ($obj->fk_statut == 1 && $db->jdate($obj->date_valid) < ($now - $conf->supplier_proposal->warning_delay)) $warnornote .= img_warning($langs->trans("Late"));
			if ($warnornote)
			{
				print '<td style="min-width: 20px" class="nobordernopadding nowrap">';
				print $warnornote;
				print TD;
			}
			// Other picto tool
			print '<td width="16" class="right nobordernopadding hideonsmartphone">';
			$filename=dol_sanitizeFileName($obj->ref);
			$filedir=$conf->supplier_proposal->dir_output . '/' . dol_sanitizeFileName($obj->ref);
			$urlsource=$_SERVER[PHPSELF].'?id='.$obj->rowid;
			print $formfile->getDocumentsLink($objectstatic->element, $filename, $filedir);
			print '</td></tr></table>';

			print "</td>\n";
			if (! $i) $totalarray[NBF]++;
		}

		$url = DOL_URL_ROOT.'/comm/card.php?socid='.$obj->socid;

		// Company
		$companystatic->id=$obj->socid;
		$companystatic->name=$obj->name;
		$companystatic->client=$obj->client;
		$companystatic->code_client=$obj->code_client;

		// Thirdparty
		if (! empty($arrayfields['s.nom'][CHK]))
		{
			print '<td class="tdoverflowmax200">';
			print $companystatic->getNomUrl(1, 'customer');
			print TD;
			if (! $i) $totalarray[NBF]++;
		}

		// Town
		if (! empty($arrayfields[ST][CHK]))
		{
			print '<td class="nocellnopadd">';
			print $obj->town;
			print TD;
			if (! $i) $totalarray[NBF]++;
		}
		// Zip
		if (! empty($arrayfields[SZIP][CHK]))
		{
			print '<td class="nocellnopadd">';
			print $obj->zip;
			print TD;
			if (! $i) $totalarray[NBF]++;
		}
		// State
		if (! empty($arrayfields[SNOM][CHK]))
		{
			print "<td>".$obj->state_name."</td>\n";
			if (! $i) $totalarray[NBF]++;
		}
		// Country
		if (! empty($arrayfields[CCISO][CHK]))
		{
			print '<td class="center">';
			$tmparray=getCountry($obj->fk_pays, 'all');
			print $tmparray[LBL];
			print TD;
			if (! $i) $totalarray[NBF]++;
		}
		// Type ent
		if (! empty($arrayfields[TYCODE][CHK]))
		{
			print '<td class="center">';
			if (count($typenArray)==0) $typenArray = $formcompany->typent_array(1);
			print $typenArray[$obj->typent_code];
			print TD;
			if (! $i) $totalarray[NBF]++;
		}

		// Date proposal
		if (! empty($arrayfields[SPDV][CHK]))
		{
			print '<td class="center">';
			print dol_print_date($db->jdate($obj->date_valid), 'day');
			print "</td>\n";
			if (! $i) $totalarray[NBF]++;
		}

		// Date delivery
		if (! empty($arrayfields[SPDL][CHK]))
		{
			print '<td class="center">';
			print dol_print_date($db->jdate($obj->dp), 'day');
			print "</td>\n";
			if (! $i) $totalarray[NBF]++;
		}

		// Amount HT
		if (! empty($arrayfields[SPTHT][CHK]))
		{
			  print '<td class="right">'.price($obj->total_ht)."</td>\n";
			  if (! $i) $totalarray[NBF]++;
			  if (! $i) $totalarray['pos'][$totalarray[NBF]]=SPTHT;
			  $totalarray['val'][SPTHT] += $obj->total_ht;
		}
		// Amount VAT
		if (! empty($arrayfields[SPTVAT][CHK]))
		{
			print '<td class="right">'.price($obj->total_vat)."</td>\n";
			if (! $i) $totalarray[NBF]++;
			if (! $i) $totalarray['pos'][$totalarray[NBF]]=SPTVAT;
			$totalarray['val'][SPTVAT] += $obj->total_vat;
		}
		// Amount TTC
		if (! empty($arrayfields[SPTTC][CHK]))
		{
			print '<td class="right">'.price($obj->total_ttc)."</td>\n";
			if (! $i) $totalarray[NBF]++;
			if (! $i) $totalarray['pos'][$totalarray[NBF]]=SPTTC;
			$totalarray['val'][SPTTC] += $obj->total_ttc;
		}

		$userstatic->id=$obj->fk_user_author;
		$userstatic->login=$obj->login;

		// Author
		if (! empty($arrayfields[ULOG][CHK]))
		{
			print '<td class="center">';
			if ($userstatic->id) print $userstatic->getLoginUrl(1);
			else print '&nbsp;';
			print "</td>\n";
			if (! $i) $totalarray[NBF]++;
		}

		// Extra fields
		include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_print_fields.tpl.php';
		// Fields from hook
		$parameters=array('arrayfields'=>$arrayfields, 'obj'=>$obj);
		$reshook=$hookmanager->executeHooks('printFieldListValue', $parameters);    // Note that $action and $object may have been modified by hook
		print $hookmanager->resPrint;
		// Date creation
		if (! empty($arrayfields[SPDAT][CHK]))
		{
			print '<td class="center nowrap">';
			print dol_print_date($db->jdate($obj->date_creation), 'dayhour', 'tzuser');
			print TD;
			if (! $i) $totalarray[NBF]++;
		}
		// Date modification
		if (! empty($arrayfields[SPTMS][CHK]))
		{
			print '<td class="center nowrap">';
			print dol_print_date($db->jdate($obj->date_update), 'dayhour', 'tzuser');
			print TD;
			if (! $i) $totalarray[NBF]++;
		}
		// Status
		if (! empty($arrayfields[SPFKS][CHK]))
		{
			print '<td class="right">'.$objectstatic->LibStatut($obj->fk_statut, 5)."</td>\n";
			if (! $i) $totalarray[NBF]++;
		}

		// Action column
		print '<td class="nowrap center">';
		if ($massactionbutton || $massaction)   // If we are in select mode (massactionbutton defined) or if we have already selected and sent an action ($massaction) defined
		{
			$selected=0;
			if (in_array($obj->rowid, $arrayofselected)) $selected=1;
			print '<input id="cb'.$obj->rowid.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->rowid.'"'.($selected?' checked="checked"':'').'>';
		}
		print TD;
		if (! $i) $totalarray[NBF]++;

		print "</tr>\n";

		$total += $obj->total_ht;
		$subtotal += $obj->total_ht;

		$i++;
	}

	// Show total line
	include DOL_DOCUMENT_ROOT.'/core/tpl/list_print_total.tpl.php';

	$db->free($resql);

	$parameters = array('arrayfields'=>$arrayfields, 'sql'=>$sql);
	$reshook = $hookmanager->executeHooks('printFieldListFooter', $parameters); // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;

	print '</table>'."\n";
	print '</div>'."\n";

	print '</form>'."\n";

	$hidegeneratedfilelistifempty = 1;
	if ($massaction == 'builddoc' || $action == 'remove_file' || $show_files) $hidegeneratedfilelistifempty = 0;

	// Show list of available documents
	$urlsource = $_SERVER[PHPSELF].'?sortfield='.$sortfield.'&sortorder='.$sortorder;
	$urlsource .= str_replace('&amp;', '&', $param);

	$filedir = $diroutputmassaction;

	$genallowed = $user->rights->supplier_proposal->lire;
	$delallowed = $user->rights->supplier_proposal->creer;

	print $formfile->showdocuments('massfilesarea_supplier_proposal', '', $filedir, $urlsource, 0, $delallowed, '', 1, 1, 0, 48, 1, $param, $title, '', '', '', null, $hidegeneratedfilelistifempty);
}
else
{
	dol_print_error($db);
}

// End of page
llxFooter();
$db->close();
