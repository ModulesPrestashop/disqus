<?php
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
	 
		$this->displayName = $this->l('My module');
		$this->description = $this->l('Disqus script for comments');
	 
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
	 
		if (!Configuration::get('DISQUS_NAME'))      
			$this->warning = $this->l('No name provided');
	}

	public function install()
	{
		if (Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);

		if (!parent::install() && !$this->registerHook('displayFooterProduct'))
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
			if (!$ b  || empty($m b) || !Validate::isGenericName($ b))
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
		$helper->toolbar_btn = array(
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
		);

		// Load current value
		$helper->fields_value['DISQUS_NAME'] = Configuration::get('DISQUS_NAME');

		return $helper->generateForm($fields_form);
	}

	public function hookDisplayFooterProduct($params)
	{
		$this->context->smarty->assign(array(
			'my_module_name' => Configuration::get('DISQUS_NAME')
			));

		return $this->display(__FILE__, 'productfooter.tpl');
	}
}
