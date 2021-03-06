<div class="buckaroo-input-wrapper">
	<label
		class="buckaroo-label"
		for="buckaroo-extra-fields-{$name}-user-buckaroo_payment_vat_num"
	>
		{s name="VATNumber" namespace="frontend/buckaroo/plugins"}VATNumber{/s}
	</label>

	<input
		type="text"
		name="buckaroo-extra-fields[{$name}][user][buckaroo_payment_vat_num]"
		id="buckaroo-extra-fields-{$name}-user-buckaroo_payment_vat_num"
		placeholder="{s name="VATNumber" namespace="frontend/buckaroo/plugins"}VATNumber{/s}"
		class="buckaroo_auto_submit buckaroo-input{if $buckarooValidationMessages[$name]['user']['buckaroo_payment_vat_num']} buckaroo-has-error{/if}"
		value="{if $buckarooExtraFields.user.buckaroo_payment_vat_num}{$buckarooExtraFields.user.buckaroo_payment_vat_num}{else}{$vatId}{/if}"
	/>
</div>
<div style="display: none">
{include file='frontend/_includes/fields/parts/error_messages.tpl' name=$name entity="user" key="buckaroo_payment_vat_num"}
</div>