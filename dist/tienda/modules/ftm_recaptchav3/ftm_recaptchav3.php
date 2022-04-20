<?php
/**
* 2020 F. Tardío
* FTM RECAPTCHA V3
*
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ftm_Recaptchav3 extends Module
{

	public function __construct()
	{
		$this->name = 'ftm_recaptchav3';
    $this->tab = 'Administration';
		$this->version = '1.1.0';
		$this->author = 'F. Tardío';
		$this->need_instance = 0;

    $this->bootstrap = true;
    parent::__construct();

    $this->displayName = $this->l('FTM Recaptcha v3');
    $this->description = $this->l('Integrates Google Recaptcha v3 in your contact and registration form');

    $this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => _PS_VERSION_);
    $this->recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
  }

  public function install()
  {
    $res = (
      parent::install() &&
      $this->registerHook('header') &&
      $this->registerHook('displayGDPRConsent') &&
      $this->registerHook('actionContactFormSubmitBefore') &&
      $this->registerHook('additionalCustomerFormFields') &&
      $this->registerHook('validateCustomerFormFields') &&
      $this->registerHook('displayNewsletterRegistration') &&
      $this->registerHook('actionNewsletterRegistrationBefore') &&
      $this->installFixtures()
    );
    return $res;
  }
  
  protected function installFixtures()
  {
    Configuration::updateValue('FTM_RECAPTCHA_V3_ACTIVE', 0);
    Configuration::updateValue('FTM_RECAPTCHA_V3_SITE_KEY', '');
    Configuration::updateValue('FTM_RECAPTCHA_V3_SECRET_KEY', '');
    Configuration::updateValue('FTM_RECAPTCHA_V3_IN_REGISTRATION_FORM', 0);
    Configuration::updateValue('FTM_RECAPTCHA_V3_IN_CONTACT_FORM', 0);
    Configuration::updateValue('FTM_RECAPTCHA_V3_IN_NEWSLETTER_SUBSCRIPTION', 0);
    Configuration::updateValue('FTM_RECAPTCHA_V3_NEWSLETTER_SUBSCRIPTION_ONLY_AT_HOME', 1);
    Configuration::updateValue('FTM_RECAPTCHA_V3_MINIMUM_SCORE', 7);
    return true;
  }

  public function uninstall()
  {
    Configuration::deleteByName('FTM_RECAPTCHA_V3_ACTIVE');
    Configuration::deleteByName('FTM_RECAPTCHA_V3_SITE_KEY');
    Configuration::deleteByName('FTM_RECAPTCHA_V3_SECRET_KEY');
    Configuration::deleteByName('FTM_RECAPTCHA_V3_IN_REGISTRATION_FORM');
    Configuration::deleteByName('FTM_RECAPTCHA_V3_IN_CONTACT_FORM');
    Configuration::deleteByName('FTM_RECAPTCHA_V3_IN_NEWSLETTER_SUBSCRIPTION');
    Configuration::deleteByName('FTM_RECAPTCHA_V3_NEWSLETTER_SUBSCRIPTION_ONLY_AT_HOME');
    Configuration::deleteByName('FTM_RECAPTCHA_V3_MINIMUM_SCORE');
    return parent::uninstall();
  }
  
  public function hookHeader($params)
  {
    if (Configuration::get('FTM_RECAPTCHA_V3_ACTIVE')) {
      $controller = Tools::getValue('controller');
      if (
        ($controller=='authentication' && Configuration::get('FTM_RECAPTCHA_V3_IN_REGISTRATION_FORM')) ||
        ($controller=='order' && !$this->context->customer->isLogged() && Configuration::get('FTM_RECAPTCHA_V3_IN_REGISTRATION_FORM')) ||
        ($controller=='contact' && Module::isEnabled('contactform') && Configuration::get('FTM_RECAPTCHA_V3_IN_CONTACT_FORM')) ||
        ($controller=='index' && Module::isEnabled('ps_emailsubscription') && Configuration::get('FTM_RECAPTCHA_V3_IN_NEWSLETTER_SUBSCRIPTION') && Configuration::get('FTM_RECAPTCHA_V3_NEWSLETTER_SUBSCRIPTION_ONLY_AT_HOME')) ||
        (Module::isEnabled('ps_emailsubscription') && Configuration::get('FTM_RECAPTCHA_V3_IN_NEWSLETTER_SUBSCRIPTION') && !Configuration::get('FTM_RECAPTCHA_V3_NEWSLETTER_SUBSCRIPTION_ONLY_AT_HOME'))
      ) {
        $this->context->smarty->assign(array('ftmRecaptchav3SiteKey' => Configuration::get('FTM_RECAPTCHA_V3_SITE_KEY'), 'ftmRecaptchav3Controller'=> $controller));
        return $this->fetch('module:'.$this->name.'/views/templates/hook/ftm_recaptchav3_header.tpl');
      }
    }
  }
  
  public function hookDisplayGDPRConsent($params) {
    if (Configuration::get('FTM_RECAPTCHA_V3_ACTIVE') && Configuration::get('FTM_RECAPTCHA_V3_IN_CONTACT_FORM')) {
      if (array_key_exists('id_module', $params) && is_numeric($params['id_module']) && $params['id_module']>0) {
        $targetModule = Module::getInstanceById($params['id_module']);
        if ($targetModule->name=='contactform') {
          return $this->fetch('module:'.$this->name.'/views/templates/hook/ftm_recaptchav3_forms.tpl');
        }
      }
    }
  }
  
  public function hookActionContactFormSubmitBefore()
  {
    if (Configuration::get('FTM_RECAPTCHA_V3_ACTIVE') && Configuration::get('FTM_RECAPTCHA_V3_IN_CONTACT_FORM')) {
      $recaptchaResponse = Tools::getValue('recaptcha_response'); 
      return $this->recaptchaVerification($recaptchaResponse);
    }
  }
  
  public function hookAdditionalCustomerFormFields($params)
  {
    if (Configuration::get('FTM_RECAPTCHA_V3_ACTIVE') && Configuration::get('FTM_RECAPTCHA_V3_IN_REGISTRATION_FORM')) {
      $formField = new FormField();
      $formField->setName('recaptcha_response');
      $formField->setType('hidden');
      $formField->setRequired(false);
      $formField->setValue('');

      return array($formField);
    }
  }
  
  public function hookvalidateCustomerFormFields($params)
  {
    if (Configuration::get('FTM_RECAPTCHA_V3_ACTIVE') && Configuration::get('FTM_RECAPTCHA_V3_IN_REGISTRATION_FORM')) {
      $fields = $params['fields'];
      $recaptchaResponse = $fields[0]->getValue();
      if (!$this->recaptchaVerification($recaptchaResponse)) {
        $fields[0]->addError($this->l('Recaptcha not verified. Are you a bot?'));
      }
      return array($fields);
    }
  }
  
  public function hookDisplayNewsletterRegistration($params)
  {
    if (Configuration::get('FTM_RECAPTCHA_V3_ACTIVE') && Configuration::get('FTM_RECAPTCHA_V3_IN_NEWSLETTER_SUBSCRIPTION')) {
      return $this->fetch('module:'.$this->name.'/views/templates/hook/ftm_recaptchav3_forms.tpl');
    }
  }
  
  public function hookActionNewsletterRegistrationBefore($params)
  {
    if (Configuration::get('FTM_RECAPTCHA_V3_ACTIVE') && Configuration::get('FTM_RECAPTCHA_V3_IN_NEWSLETTER_SUBSCRIPTION')) {
      $recaptchaResponse = Tools::getValue('recaptcha_response');
      if (!$this->recaptchaVerification($recaptchaResponse)) {
        $params['hookError'] = $this->l('Recaptcha not verified. Are you a bot?');
      }
    }
  }
  
  
  protected function recaptchaVerification($recaptchaResponse)
  {
    $recaptchaSecret = Configuration::get('FTM_RECAPTCHA_V3_SECRET_KEY');
    $recaptcha = file_get_contents($this->recaptchaUrl . '?secret=' . $recaptchaSecret . '&response=' . $recaptchaResponse); 
    $recaptchaVerification = json_decode($recaptcha);
    if($recaptchaVerification->score >= ((int)Configuration::get('FTM_RECAPTCHA_V3_MINIMUM_SCORE')/10)) {
      return true;
    }else{
      $context = Context::getContext();
      $context->controller->errors[] = $this->l('Recaptcha not verified. Are you a bot?');
      return false;
    }
  }
  
  /* Stores configuration values */
  public function postProcess()
  {
    $output = '';
    $errors = array();
    if (Tools::isSubmit('submitStoreConf')) {
      /* Previous comprobations */
      $siteKey = Tools::getValue('FTM_RECAPTCHA_V3_SITE_KEY');
      $secretKey = Tools::getValue('FTM_RECAPTCHA_V3_SECRET_KEY');
      if ((bool)Tools::getValue('FTM_RECAPTCHA_V3_ACTIVE') && (!$siteKey || !$secretKey)) {
        $errors[] = $this->l('You must specify Site Key and Secret Key to be able to activate Recaptcha on your site');
      }
      if ($siteKey == $secretKey) {
        $errors[] = $this->l('Site Key and Secret Key can\'t be the same');
      }
      
      if (count($errors)) {
        $output = $this->displayError(implode('<br />', $errors));
      } else {
        Configuration::updateValue('FTM_RECAPTCHA_V3_ACTIVE', (bool)Tools::getValue('FTM_RECAPTCHA_V3_ACTIVE'));
        Configuration::updateValue('FTM_RECAPTCHA_V3_SITE_KEY', trim($siteKey));
        Configuration::updateValue('FTM_RECAPTCHA_V3_SECRET_KEY', trim($secretKey));
        Configuration::updateValue('FTM_RECAPTCHA_V3_IN_CONTACT_FORM', (bool)Tools::getValue('FTM_RECAPTCHA_V3_IN_CONTACT_FORM'));
        Configuration::updateValue('FTM_RECAPTCHA_V3_IN_REGISTRATION_FORM', (bool)Tools::getValue('FTM_RECAPTCHA_V3_IN_REGISTRATION_FORM'));
        Configuration::updateValue('FTM_RECAPTCHA_V3_IN_NEWSLETTER_SUBSCRIPTION', (bool)Tools::getValue('FTM_RECAPTCHA_V3_IN_NEWSLETTER_SUBSCRIPTION'));
        Configuration::updateValue('FTM_RECAPTCHA_V3_NEWSLETTER_SUBSCRIPTION_ONLY_AT_HOME', (bool)Tools::getValue('FTM_RECAPTCHA_V3_NEWSLETTER_SUBSCRIPTION_ONLY_AT_HOME'));
        Configuration::updateValue('FTM_RECAPTCHA_V3_MINIMUM_SCORE', (int)Tools::getValue('FTM_RECAPTCHA_V3_MINIMUM_SCORE'));
        $output = $this->displayConfirmation($this->l('The settings have been updated'));
      }
    }
    return $output;
  }
  
  /* Gets content from configuration form */
  public function getContent()
	{
		return $this->postProcess().$this->renderForm();
	}

  /* Renders administration configuration form */
  public function renderForm()
  {
    $fields_form = array();
    $switchEnable = array(
      array(
        'id' => 'active_on',
        'value' => 1,
        'label' => $this->l('Enabled', 'Admin.Global')
      ),
      array(
        'id' => 'active_off',
        'value' => 0,
        'label' => $this->l('Disabled', 'Admin.Global')
      )
    );
    $sensibilityQuery = array();
    foreach (range(1,10) as $val) {
      $sensibilityQuery[]= array('id_option'=>$val, 'name'=>(strval($val)));
    }
    $locationTypes = array(
      array(
        'id' => 'type',
        'value' => '1',
        'label' => $this->l('Only at index')
      ),
      array(
        'id' => 'type',
        'value' => '0',
        'label' => $this->l('In every page of your site')
      )
    );
    $fields_form[] = array(
      'form' => array(
        'legend' => array(
          'title' => $this->l('General Settings'),
          'icon' => 'icon-cogs',
        ),
        'input' => array(
          array(
            'type' => 'switch',
            'is_bool' => true, //retro compat 1.5
            'label' => $this->l('Recaptcha module active'),
            'name' => 'FTM_RECAPTCHA_V3_ACTIVE',
            'desc' => $this->l('Enables or disables Recaptcha for this site'),
            'required' => true,
            'values' => $switchEnable
          ),
          array(
            'type' => 'text',
            'label' => $this->l('Google Site Key'),
            'name' => 'FTM_RECAPTCHA_V3_SITE_KEY',
            'desc' => $this->l('Google Site Key for this site, you can get it at https://www.google.com/recaptcha/admin/create'),
            'lang' => false,
            'class' => 'fixed-width-xxxl',
            'required' => false,
          ),
          array(
            'type' => 'text',
            'label' => $this->l('Google Secret Key'),
            'name' => 'FTM_RECAPTCHA_V3_SECRET_KEY',
            'desc' => $this->l('Google Secret Key for this site, you can get it at https://www.google.com/recaptcha/admin/create'),
            'lang' => false,
            'class' => 'fixed-width-xxxl',
            'required' => false,
          ),
          array(
            'type' => 'switch',
            'is_bool' => true, //retro compat 1.5
            'label' => $this->l('Enable at Registration From'),
            'name' => 'FTM_RECAPTCHA_V3_IN_REGISTRATION_FORM',
            'desc' => $this->l('Enables recaptcha at registration form'),
            'required' => true,
            'values' => $switchEnable
          ),
          array(
            'type' => 'switch',
            'is_bool' => true, //retro compat 1.5
            'label' => $this->l('Enable at Contact From'),
            'name' => 'FTM_RECAPTCHA_V3_IN_CONTACT_FORM',
            'desc' => $this->l('Enables recaptcha at contact form'),
            'required' => true,
            'values' => $switchEnable
          ),
          array(
            'type' => 'switch',
            'is_bool' => true, //retro compat 1.5
            'label' => $this->l('Enable at Newsletter Subscription'),
            'name' => 'FTM_RECAPTCHA_V3_IN_NEWSLETTER_SUBSCRIPTION',
            'desc' => $this->l('Enables recaptcha at newsletter subscription form'),
            'required' => true,
            'values' => $switchEnable
          ),
          array(
            'type' => 'radio',
            'label' => $this->l('Newsletter susbscription location'),
            'name' => 'FTM_RECAPTCHA_V3_NEWSLETTER_SUBSCRIPTION_ONLY_AT_HOME',
            'desc' => $this->l('Note if newsletter subscription form is located only at index page or in every page of your site'),
            'required' => true,
            'values' => $locationTypes
          ),
          array(
            'type' => 'select',
            'is_bool' => true, //retro compat 1.5
            'label' => $this->l('Recaptcha sensibility'),
            'name' => 'FTM_RECAPTCHA_V3_MINIMUM_SCORE',
            'desc' => $this->l('Adjust recaptcha sensibility (highter values, more difficult to pass). Recommended value: 7'),
            'required' => true,
            'options' => array(
              'query' => $sensibilityQuery,
              'id' => 'id_option',
              'name'=> 'name'
            )
          ),
        ),
        'submit' => array(
          'title' => $this->l('Save', 'Admin.Actions')
        )
      )
    );
    $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));

    $helper = new HelperForm();
    $helper->show_toolbar = false;
    $helper->table = $this->table;
    $helper->default_form_language = $lang->id;
    $helper->module = $this;
    $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
    $helper->identifier = $this->identifier;
    $helper->submit_action = 'submitStoreConf';
    $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->tpl_vars = array(
        'uri' => $this->getPathUri(),
        'fields_value' => $this->getConfigFieldsValues(),
        'languages' => $this->context->controller->getLanguages(),
        'id_language' => $this->context->language->id
    );

    return $helper->generateForm($fields_form);
  }
  
  protected function getConfigFieldsValues()
  {
    $ret = array();
    $ret['FTM_RECAPTCHA_V3_ACTIVE'] = (bool)Configuration::get('FTM_RECAPTCHA_V3_ACTIVE');
    $ret['FTM_RECAPTCHA_V3_SITE_KEY'] = Configuration::get('FTM_RECAPTCHA_V3_SITE_KEY');
    $ret['FTM_RECAPTCHA_V3_SECRET_KEY'] = Configuration::get('FTM_RECAPTCHA_V3_SECRET_KEY');
    $ret['FTM_RECAPTCHA_V3_IN_CONTACT_FORM'] = (bool)Configuration::get('FTM_RECAPTCHA_V3_IN_CONTACT_FORM');
    $ret['FTM_RECAPTCHA_V3_IN_REGISTRATION_FORM'] = (bool)Configuration::get('FTM_RECAPTCHA_V3_IN_REGISTRATION_FORM');
    $ret['FTM_RECAPTCHA_V3_IN_NEWSLETTER_SUBSCRIPTION'] = (bool)Configuration::get('FTM_RECAPTCHA_V3_IN_NEWSLETTER_SUBSCRIPTION');
    $ret['FTM_RECAPTCHA_V3_NEWSLETTER_SUBSCRIPTION_ONLY_AT_HOME'] = (bool)Configuration::get('FTM_RECAPTCHA_V3_NEWSLETTER_SUBSCRIPTION_ONLY_AT_HOME');
    $ret['FTM_RECAPTCHA_V3_MINIMUM_SCORE'] = (int)Configuration::get('FTM_RECAPTCHA_V3_MINIMUM_SCORE');
    return $ret;
  }
  
}
