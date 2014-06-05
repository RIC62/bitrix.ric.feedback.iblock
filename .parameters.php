<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

// Подключение выбора инфоблока

if(!CModule::IncludeModule("iblock"))
	return;

$arTypesEx = CIBlockParameters::GetIBlockTypes(Array("-"=>" "));

$arIBlocks=Array();
$db_iblock = CIBlock::GetList(Array("SORT"=>"ASC"), Array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE"]!="-"?$arCurrentValues["IBLOCK_TYPE"]:"")));
while($arRes = $db_iblock->Fetch())
	$arIBlocks[$arRes["ID"]] = $arRes["NAME"];

$arSorts = Array("ASC"=>GetMessage("T_IBLOCK_DESC_ASC"), "DESC"=>GetMessage("T_IBLOCK_DESC_DESC"));
$arSortFields = Array(
	"ID"=>GetMessage("T_IBLOCK_DESC_FID"),
	"NAME"=>GetMessage("T_IBLOCK_DESC_FNAME"),
	"ACTIVE_FROM"=>GetMessage("T_IBLOCK_DESC_FACT"),
	"SORT"=>GetMessage("T_IBLOCK_DESC_FSORT"),
	"TIMESTAMP_X"=>GetMessage("T_IBLOCK_DESC_FTSAMP")
);

// ---- Подключение выбора инфоблока

$site = ($_REQUEST["site"] <> ''? $_REQUEST["site"] : ($_REQUEST["src_site"] <> ''? $_REQUEST["src_site"] : false));
$arFilter = Array("TYPE_ID" => "FEEDBACK_FORM", "ACTIVE" => "Y");
if($site !== false)
	$arFilter["LID"] = $site;

$arEvent = Array();
$dbType = CEventMessage::GetList($by="ID", $order="DESC", $arFilter);
while($arType = $dbType->GetNext())
	$arEvent[$arType["ID"]] = "[".$arType["ID"]."] ".$arType["SUBJECT"];

$arComponentParameters = array(
	"GROUPS" => array(
		"IBLOCK" => array(
			"NAME" => GetMessage("SETTINGS_IBLOCK")
		),
	),
	"PARAMETERS" => array(
		"USE_CAPTCHA" => Array(
			"NAME" => GetMessage("MFP_CAPTCHA"), 
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y", 
			"PARENT" => "BASE",
		),
		"OK_TEXT" => Array(
			"NAME" => GetMessage("MFP_OK_MESSAGE"), 
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("MFP_OK_TEXT"), 
			"PARENT" => "BASE",
		),
		"USE_IBLOCK" => Array(
			"NAME" => GetMessage("MFP_IBLOCK"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "IBLOCK",
		),
		"IBLOCK_TYPE" => Array(
			"PARENT" => "IBLOCK",
			"NAME" => GetMessage("T_IBLOCK_DESC_LIST_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arTypesEx,
			"DEFAULT" => "news",
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => Array(
			"PARENT" => "IBLOCK",
			"NAME" => GetMessage("T_IBLOCK_DESC_LIST_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlocks,
			"DEFAULT" => '',
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		),
		"SECTION_ID" => array(
			"PARENT" => "IBLOCK",
			"NAME" => GetMessage("IBLOCK_SECTION_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '',
		),
		"IBLOCK_NAME_TEXT" => Array(
			"NAME" => GetMessage("MFP_IBLOCK_NAME_TEXT"),
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("MFP_IBLOCK_NAME_DEFAULT"),
			"PARENT" => "IBLOCK",
		),
		"EXT_FIELDS" => Array(
			"NAME" => GetMessage("MFP_EXT_FIELDS"),
			"TYPE" => "STRING",
			"MULTIPLE"=>"Y", 
			"PARENT" => "BASE",
		),
		"EMAIL_TO" => Array(
			"NAME" => GetMessage("MFP_EMAIL_TO"), 
			"TYPE" => "STRING",
			"DEFAULT" => htmlspecialchars(COption::GetOptionString("main", "email_from")), 
			"PARENT" => "BASE",
		),
		"REQUIRED_FIELDS" => Array(
			"NAME" => GetMessage("MFP_REQUIRED_FIELDS"), 
			"TYPE"=>"LIST", 
			"MULTIPLE"=>"Y", 
			"VALUES" => Array("NONE" => GetMessage("MFP_ALL_REQ"), "NAME" => GetMessage("MFP_NAME"), "EMAIL" => "E-mail", "MESSAGE" => GetMessage("MFP_MESSAGE")),
			"DEFAULT"=>"", 
			"COLS"=>25, 
			"PARENT" => "BASE",
		),

		"EVENT_MESSAGE_ID" => Array(
			"NAME" => GetMessage("MFP_EMAIL_TEMPLATES"), 
			"TYPE"=>"LIST", 
			"VALUES" => $arEvent,
			"DEFAULT"=>"", 
			"MULTIPLE"=>"Y", 
			"COLS"=>25, 
			"PARENT" => "BASE",
		),

	)
);


?>