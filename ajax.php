<?php
require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__).'/gptproductdescriptions.php');

if (Tools::getValue('action') == 'generateDescription') {
    $productName = Tools::getValue('productName');
    $productFeatures = Tools::getValue('productFeatures');

    $module = Module::getInstanceByName('gptproductdescriptions');
    $description = $module->generateDescription($productName, $productFeatures);

    header('Content-Type: application/json');
    echo json_encode(['description' => $description]);
    exit;
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid action']);
    exit;
}
?>