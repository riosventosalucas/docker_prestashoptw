{if $showOffline == 0}
	{if $time > $openSt && $time < $closeSt}
		{$attac = 1}
	{else}
		{$attac = 0}
	{/if}
{else}
	{if $time > $openSt && $time < $closeSt}
		{$attac = 1}
	{else}
		{$attac = 1}
		{$status = 2}
	{/if}
{/if}
{if $hook == 'footer'}
	<div class="asagiSabit whatsappBlock {if $attac == 0}hidden{/if}">
		<a class="{whatsapp::getStatus($status)|escape:'html':'UTF-8'}" href="{if $deviceType != 'computer'}whatsapp://send?{else}https://web.whatsapp.com/send?{/if}{if $page.page_name == 'product' && $shareThis == 1}text={$shareMessage|escape:'html':'UTF-8'}&{/if}phone=+{$whatasppno|escape:'html':'UTF-8'}">
			<img src="{$whataspp_module_dir|escape:'html':'UTF-8'}/views/img/whatsapp.jpg" alt="{$userName|escape:'html':'UTF-8'}" width="50px" height="50px" /> 
			{if $showEfect == 1}
			<span id="kutu" class="kutucuklar">
				<div class="kutucuk k1"></div>
				<div class="kutucuk k2"></div>
				<div class="kutucuk k3"></div>
			</span>
			{/if}
			<div class="message">{whatsapp::getValueLang($status, $lang)|escape:'html':'UTF-8'}</div>
		</a>
	</div>
{else}
	{if $deviceType == 'computer'}
		<div class="whatsappBlock hidden-xs"><a href="https://web.whatsapp.com/send?{if $page.page_name == 'product' && $shareThis == 1}text={$shareMessage|escape:'html':'UTF-8'}&{/if}phone=+{$whatasppno|escape:'html':'UTF-8'}"><img src="{$whataspp_module_dir|escape:'html':'UTF-8'}/views/img/whataspp_icon.png" alt="Whataspp" width="24px" height="24px" /> {l s='Whataspp Live Chat' mod='whatsapp'}</a></div>
	{else}
		<div class="whatsappBlock hidden-xs"><a href="whatsapp://send?{if $page.page_name == 'product' && $shareThis == 1}text={$shareMessage|escape:'html':'UTF-8'}&{/if}phone=+{$whatasppno|escape:'html':'UTF-8'}"><img src="{$whataspp_module_dir|escape:'html':'UTF-8'}/views/img/whataspp_icon.png" alt="Whataspp" width="24px" height="24px" /> {l s='Whataspp Live Chat' mod='whatsapp'}</a></div>
	{/if}
{/if}