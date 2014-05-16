<?php
/**
* 2007-2014 PrestaShop
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
*  @copyright 2007-2014 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
  exit;
 
class Disqus extends Module
{
	public function __construct()
	{
		$this->name = 'disqus';
		$this->tab = 'front_office_features';
		$this->version = '1.0';
		$this->author = 'jorgevrgs';
		$this->need_instance = 1;
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
		$this->bootstrap = true;
	 
		parent::__construct();
	 
		$this->displayName = $this->l('Disqus');
		$this->description = $this->l('Disqus script for comments');
	 
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
	 
		if (!Configuration::get('DISQUS_NAME'))      
			$this->warning = $this->l('No name provided');
	}

	public function install()
	{
		if (Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);

		if (!parent::install() && !$this->registerHook('productfooter'))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall() || !Configuration::deleteByName('DISQUS_NAME'))
			return false;
		return true;
	}

	public function getContent()
	{
		$output = null;

		if (Tools::isSubmit('submit'.$this->name))
		{
			$DISQUS_NAME = strval(Tools::getValue('DISQUS_NAME'));
			if (!$DISQUS_NAME  || empty($DISQUS_NAME) || !Validate::isGenericName($DISQUS_NAME))
				$output .= $this->displayError( $this->l('Invalid Configuration value') );
			else
			{
				Configuration::updateValue('DISQUS_NAME', $DISQUS_NAME);
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}
		}
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		// Get default Language
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		// Init Fields form array
		$fields_form[0]['form'] = array(
			'legend' => array(
			'title' => $this->l('Settings'),
			),
			'input' => array(
				array(
				'type' => 'text',
				'label' => $this->l('Configuration value'),
				'name' => 'DISQUS_NAME',
				'required' => true
				)
			),
			'submit' => array(
			'title' => $this->l('Save'),
			'class' => 'button'
			)
		);

		$helper = new HelperForm();

		// Module, t    oken and currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

		// Language
		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;

		// Title and toolbar
		$helper->title = $this->displayName;
		$helper->show_toolbar = true;        // false -> remove toolbar
		$helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
		$helper->submit_action = 'submit'.$this->name;
		/*$helper->toolbar_btn = array(
			'save' =>
			array(
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
				'&token='.Tools::getAdminTokenLite('AdminModules'),
			),
			'back' => array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);*/

		// Load current value
		$helper->fields_value['DISQUS_NAME'] = Configuration::get('DISQUS_NAME');

		return $helper->generateForm($fields_form);
	}

	public function hookDisplayFooterProduct($params)
	{
		$this->context->smarty->assign(array(
			'DISQUS_NAME' => Configuration::get('DISQUS_NAME')
			));

		return $this->display(__FILE__, 'productfooter.tpl');
	}

	public function hookDisplayHome($params)
	{
		return $this->hookDisplayFooterProduct();
	}
}
