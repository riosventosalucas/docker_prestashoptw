<!-- FTM Recaptcha v3 -->
<script src="https://www.google.com/recaptcha/api.js?render={$ftmRecaptchav3SiteKey}"> </script>
<script>
{literal}
grecaptcha.ready(function() {
  grecaptcha.execute('{/literal}{$ftmRecaptchav3SiteKey}{literal}', {action: '{/literal}{$ftmRecaptchav3Controller}{literal}'}).then(function(token) {
    var recaptchaResponses = document.getElementsByName('recaptcha_response');
    recaptchaResponses.forEach(function(e) {
      e.value = token;
    });
  });
});
{/literal}
</script>