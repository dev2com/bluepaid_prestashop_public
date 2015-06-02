{literal}
<script>
function popuprnp1xrnp(){
var win2 = window.open("http://www.bluepaid.com",'popup','height=705,width=610,status=no,scrollbars=no,menubar=no,resizable=no');
}
</script>
{/literal}
{if $comptant}
	<p class="payment_module">
		<b><a href="{$modules_dir}bluepaid/sendtoBPI.php?payment=1"><img width="50" src="{$modules_dir}bluepaid/cadenas_mini.png" /> 
			{l s='Bluepaid, payez tout simplement, par carte bancaire.' mod='bluepaid'}
			<!--<span style="display: block;margin-left: 125px;margin-top: 5px;" onClick="popuprnp1xrnp();return false;"><u>{l s='Learn more' mod='bluepaid'}</u></span>-->
		</a>
		</b>
	</p>
{/if}
{if $credit}
	<p class="payment_module">
		<b><a href="{$modules_dir}bluepaid/sendabotoBPI.php?payment=1"><img width="50" src="{$modules_dir}bluepaid/cadenas_mini.png" /> 
			{l s='Payez en plusieurs fois par carte bancaire avec Bluepaid.' mod='bluepaid'}
			<!--<span style="display: block;margin-left: 125px;margin-top: 5px;" onClick="popuprnp1xrnp();return false;"><u>{l s='Learn more' mod='bluepaid'}</u></span>-->
		</a>
		</b>
	</p>
{/if}
