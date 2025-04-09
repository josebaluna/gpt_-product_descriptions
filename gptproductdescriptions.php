<?php
/**
* 2007-2024 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2024 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class GptProductDescriptions extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'gptproductdescriptions';
        $this->tab = 'others';
        $this->version = '1.0.0';
        $this->author = 'Sebastian Luna';
        $this->need_instance = 1;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('AI Product Descriptions');
        $this->description = $this->l('Genera descripciones de productos automáticamente usando IA.');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        Configuration::updateValue('GPTPRODUCTDESCRIPTIONS_API_KEY', false);

        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayAdminProductsExtra');
    }

    public function uninstall()
    {

        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    public function getContent()
    {

        if (((bool)Tools::isSubmit('submitGptProductDescriptionsModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitGptProductDescriptionsModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $helper->fields_value['GPTPRODUCTDESCRIPTIONS_API_KEY'] = Configuration::get('GPTPRODUCTDESCRIPTIONS_API_KEY');

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('GPT Product Descriptions'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Clave API de ChatGPT'),
                        'name' => 'GPTPRODUCTDESCRIPTIONS_API_KEY',
                        'size' => 60,
                        'label' => $this->l('API key'),
                        'required' => true,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getConfigFormValues()
    {
        return array(
            'GPTPRODUCTDESCRIPTIONS_API_KEY' => Configuration::get('GPTPRODUCTDESCRIPTIONS_API_KEY', null),
        );
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        $apiKey = Configuration::get('GPTPRODUCTDESCRIPTIONS_API_KEY');

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }

        if (Tools::getValue('ajax') && Tools::getValue('action') === 'generateDescription') {
            $this->generateDescriptionAjax();
        }
        parent::postProcess();
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/admin.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/admin.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        $product = new Product($params['id_product']);
        $description = $this->generateDescription($product->name[$this->context->language->id], $params['id_product']);

        $this->context->smarty->assign([
            'product_name' => $product->name[$this->context->language->id],
            'product_id' => $params['id_product'],
            'description' => $description,
            'token' => Tools::getAdminTokenLite('AdminModules'),
            'link' => new Link(),
            'generate_description_button_label' => $this->l('Generar descripción con GPT'),
        ]);

        return $this->display(__FILE__, 'views/templates/admin/button.tpl');
    }

    private function generateDescriptionAjax()
    {
        $product_name = Tools::getValue('product_name');
        $product_id = Tools::getValue('product_id');
        $token = Tools::getValue('token');

        if (!Validate::isLoadedObject($this->context->employee) || !$this->isValidToken($token)) {
            header('Content-Type: application/json');
            die(json_encode(array('error' => 'Invalid CSRF token')));
        }

        $description = $this->generateDescription($product_name, $product_id, $token);

        header('Content-Type: application/json');
        die(json_encode(array('description' => $description)));
    }

    public function generateDescription($productName)
    {
        $apiKey = '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';
        $endpoint = 'https://api.openai.com/v1/chat/completions';

        $product_id = Tools::getValue('product_id');

        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => "Generate a product description for: " . $productName]
            ],
            'max_tokens' => 150, 
            'temperature' => 0.7, 
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . 'Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return 'Curl error: ' . curl_error($ch);
        }
        curl_close($ch);

        $response = json_decode($result, true);
        if (isset($response['error'])) {
            return 'API error: ' . $response['error']['message'];
        }

        $generatedDescription = $response['choices'][0]['message']['content'];

        $this->saveDescription($product_id, $generatedDescription);

        return $generatedDescription;
    }

    private function isValidToken($token)
    {
        return $token === Tools::getAdminTokenLite('AdminModules');
    }

    private function saveDescription($product_id, $description)
    {
        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'gptproductdescriptions (id_product, description) VALUES (' . (int)$product_id . ', "' . pSQL($description) . '")
                ON DUPLICATE KEY UPDATE description = "' . pSQL($description) . '"';
        Db::getInstance()->execute($sql);
    }

}
