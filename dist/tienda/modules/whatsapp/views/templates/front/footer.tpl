{assign var='controllerName' value=$smarty.get.controller}
<div class="whatsappDiv {$pst|escape:'html':'UTF-8'}">
	<a onclick="window.open('http://wppredirect.tk/go/?p={$whatasppno|escape:'html':'UTF-8'}{if $controllerName == 'product' && $shareThis == 1}&m={$shareMessage|escape:'html':'UTF-8'}{else}&m={/if}')" class="tiklaAc"></a>
</div>