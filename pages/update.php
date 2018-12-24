<?php
$oCategories = new nvMediapoolCategories();
$csrfToken = rex_csrf_token::factory('mediapool_categories');


if (rex_post('formsubmit', 'string') == '1' && !$csrfToken->isValid()) {
    echo rex_view::error("Ein Fehler ist aufgetreten. Bitte wenden Sie sich an den Webmaster.");

} else if (rex_post('formsubmit', 'string') == '1') {

    $bError = false;


    foreach($_POST[name] AS $iId => $sName) {
        if (!$sName) {
            $bError = true;
        }
    }


    if (!$bError) {
        foreach($_POST[name] AS $iId => $sName) {
            $aData = array(
                name => $sName,
                parent_id => $_POST["parent"][$iId],
            );
            $oCategories->updateCategory($iId,$aData);
        }
        echo rex_view::success("Einstellungen gespeichert.");
    } else {
      echo rex_view::error("Es ist ein Fehler aufgetreten.");
  }

} 

if(!isset($_POST["file"])) {

}

$aTree = $oCategories->getTree();
$sContent = '<div class="container-fluid">';
$sContent .= $oCategories->parseTreeList($aTree);
$sContent .= '</div>';


$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-save" type="submit" name="save" value="Speichern">Speichern</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');
$buttons = '
<fieldset class="rex-form-action">
' . $buttons . '
</fieldset>
';

$fragment = new rex_fragment();
$fragment->setVar("class", "edit");
$fragment->setVar('title', "Kategorien", false);
$fragment->setVar('body', $sContent, false);
$fragment->setVar("buttons", $buttons, false);
$output = $fragment->parse('core/page/section.php');

$output = '<form action="' . rex_url::currentBackendPage() . '" method="post">'
. '<input type="hidden" name="formsubmit" value="1" />'
. $csrfToken->getHiddenField() 
. $output 
. '</form>';

echo $output;