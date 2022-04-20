<?php

/* Overrides contactform to add actionContactFormSubmitBefore hook
  Added by ftm_recaptchav3 module */

class ContactformOverride extends Contactform
{
  public function sendMessage() {
    Hook::exec('actionContactFormSubmitBefore');
    if (empty($this->context->controller->errors)) {
      parent::sendMessage();
    }
  }
}