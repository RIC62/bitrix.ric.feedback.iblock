<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

$arParams["USE_CAPTCHA"] = (($arParams["USE_CAPTCHA"] != "N" && !$USER->IsAuthorized()) ? "Y" : "N");
$arParams["EVENT_NAME"] = trim($arParams["EVENT_NAME"]);
if(strlen($arParams["EVENT_NAME"]) <= 0)
	$arParams["EVENT_NAME"] = "FEEDBACK_FORM";
$arParams["EMAIL_TO"] = trim($arParams["EMAIL_TO"]);
if(strlen($arParams["EMAIL_TO"]) <= 0)
	$arParams["EMAIL_TO"] = COption::GetOptionString("main", "email_from");
$arParams["OK_TEXT"] = trim($arParams["OK_TEXT"]);
if(strlen($arParams["OK_TEXT"]) <= 0)
	$arParams["OK_TEXT"] = GetMessage("MF_OK_MESSAGE");

$arParams['NEW_EXT_FIELDS'] = Array();

if (is_array($arParams['EXT_FIELDS'])) {
	foreach ($arParams["EXT_FIELDS"] as $ext_field) {
		if (strlen($ext_field) > 0) {
			$arParams['NEW_EXT_FIELDS'][] = $ext_field;
		}
	}
}
if($_SERVER["REQUEST_METHOD"] == "POST" && strlen($_POST["submit"]) > 0)
{
	if(check_bitrix_sessid())
	{
		if(empty($arParams["REQUIRED_FIELDS"]) || !in_array("NONE", $arParams["REQUIRED_FIELDS"]))
		{
			if((empty($arParams["REQUIRED_FIELDS"]) || in_array("NAME", $arParams["REQUIRED_FIELDS"])) && strlen($_POST["user_name"]) <= 1)
				$arResult["ERROR_MESSAGE"][] = GetMessage("MF_REQ_NAME");		
			if((empty($arParams["REQUIRED_FIELDS"]) || in_array("EMAIL", $arParams["REQUIRED_FIELDS"])) && strlen($_POST["user_email"]) <= 1)
				$arResult["ERROR_MESSAGE"][] = GetMessage("MF_REQ_EMAIL");
			if((empty($arParams["REQUIRED_FIELDS"]) || in_array("MESSAGE", $arParams["REQUIRED_FIELDS"])) && strlen($_POST["MESSAGE"]) <= 3)
				$arResult["ERROR_MESSAGE"][] = GetMessage("MF_REQ_MESSAGE");
		}
		if(strlen($_POST["user_email"]) > 1 && !check_email($_POST["user_email"]))
			$arResult["ERROR_MESSAGE"][] = GetMessage("MF_EMAIL_NOT_VALID");
		if($arParams["USE_CAPTCHA"] == "Y")
		{
			include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
			$captcha_code = $_POST["captcha_sid"];
			$captcha_word = $_POST["captcha_word"];
			$cpt = new CCaptcha();
			$captchaPass = COption::GetOptionString("main", "captcha_password", "");
			if (strlen($captcha_word) > 0 && strlen($captcha_code) > 0)
			{
				if (!$cpt->CheckCodeCrypt($captcha_word, $captcha_code, $captchaPass))
					$arResult["ERROR_MESSAGE"][] = GetMessage("MF_CAPTCHA_WRONG");
			}
			else
				$arResult["ERROR_MESSAGE"][] = GetMessage("MF_CAPTHCA_EMPTY");

		}

		if(empty($arResult))
		{
			$arFields = Array(
				"AUTHOR" => $_POST["user_name"],
				"AUTHOR_EMAIL" => $_POST["user_email"],
				"EMAIL_TO" => $arParams["EMAIL_TO"],
				"TEXT" => $_POST["MESSAGE"],
			);
			$arIblockFields = Array(
				"AUTHOR" => $_POST["user_name"],
				"AUTHOR_EMAIL" => $_POST["user_email"],
				"MESSAGE" => Array("VALUE" => Array ("TEXT" => $_POST["MESSAGE"], "TYPE" => "text")),
				"STATUS" => Array("VALUE" => "1" ),
			);
			$z=0;
			foreach($_POST["custom"] as $i => $custom_field) {
				$arFields["TEXT"] .= "\n\n" . $arParams['NEW_EXT_FIELDS'][$i] . ":\n" . $custom_field;
				$arIblockFields["CUSTOM_".$z] = $custom_field;
				$z++;
			}
			if($arParams['USE_IBLOCK']=="Y") {
				// ������� � ��������
				CModule::IncludeModule("iblock");
				$el = new CIBlockElement;

				$arLoadProductArray = Array(
					"IBLOCK_SECTION_ID" => $arParams['SECTION_ID'],
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"PROPERTY_VALUES" => $arIblockFields,
					"NAME" => $arParams['IBLOCK_NAME_TEXT'],
					"ACTIVE" => "Y", // �������
					"PREVIEW_TEXT" => "",
					"DETAIL_TEXT" => "",
				);

				if ($PRODUCT_ID = $el->Add($arLoadProductArray))
					$arResult["OK_MESSAGE"] .= " #" . $PRODUCT_ID;
				else
					$arResult["ERROR_MESSAGE"][] = "Error: " . $el->LAST_ERROR;

				// !--- ������� � ��������
			}

			if(!empty($arParams["EVENT_MESSAGE_ID"]))
			{
				foreach($arParams["EVENT_MESSAGE_ID"] as $v)
					if(IntVal($v) > 0)
						CEvent::Send($arParams["EVENT_NAME"], SITE_ID, $arFields, "N", IntVal($v));
			}
			else
				CEvent::Send($arParams["EVENT_NAME"], SITE_ID, $arFields);
			$_SESSION["MF_NAME"] = htmlspecialcharsEx($_POST["user_name"]);
			$_SESSION["MF_EMAIL"] = htmlspecialcharsEx($_POST["user_email"]);
			LocalRedirect($APPLICATION->GetCurPageParam("success=Y", Array("success")));
		}
		
		$arResult["MESSAGE"] = htmlspecialcharsEx($_POST["MESSAGE"]);
		$arResult["AUTHOR_NAME"] = htmlspecialcharsEx($_POST["user_name"]);
		$arResult["AUTHOR_EMAIL"] = htmlspecialcharsEx($_POST["user_email"]);
		foreach($_POST["custom"] as $i => $custom_field)
		{
			$arResult["custom_$i"] = htmlspecialcharsEx($custom_field);
		}
	}
	else
		$arResult["ERROR_MESSAGE"][] = GetMessage("MF_SESS_EXP");
}
elseif($_REQUEST["success"] == "Y")
{
	$arResult["OK_MESSAGE"] = $arParams["OK_TEXT"];
}

if(empty($arResult["ERROR_MESSAGE"]))
{
	if($USER->IsAuthorized())
	{
		$arResult["AUTHOR_NAME"] = htmlspecialcharsEx($USER->GetFullName());
		$arResult["AUTHOR_EMAIL"] = htmlspecialcharsEx($USER->GetEmail());
	}
	else
	{
		if(strlen($_SESSION["MF_NAME"]) > 0)
			$arResult["AUTHOR_NAME"] = htmlspecialcharsEx($_SESSION["MF_NAME"]);
		if(strlen($_SESSION["MF_EMAIL"]) > 0)
			$arResult["AUTHOR_EMAIL"] = htmlspecialcharsEx($_SESSION["MF_EMAIL"]);
	}
}

if($arParams["USE_CAPTCHA"] == "Y")
	$arResult["capCode"] =  htmlspecialchars($APPLICATION->CaptchaGetCode());

$this->IncludeComponentTemplate();
?>