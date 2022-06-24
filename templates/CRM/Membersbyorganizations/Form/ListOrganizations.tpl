<div class="crm-block crm-form-block crm-basic-criteria-form-block">
	<div class="crm-accordion-wrapper crm-case_search-accordion {if $rows}collapsed{/if}">
		<div class="crm-accordion-header crm-master-accordion-header">
			{ts}Search Organization{/ts}
		</div>
		<!-- /.crm-accordion-header -->
		<div class="crm-accordion-body">
			<div class="crm-section sort_name-section">
				<div class="label">
					{$form.display_name.label}
				</div>
				<div class="content">
					{$form.display_name.html}
				</div>
				<div class="clear"></div>
			</div>

			{if $form.tag_id}
			<div class="crm-section tag-section">
				<div class="label">
					{$form.tag_id.label}
				</div>
				<div class="content">
					{$form.tag_id.html|crmAddClass:medium}
				</div>
				<div class="clear"></div>
			</div>
			{/if}

			<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl"}</div>

    </div>
	</div>
</div>

{if $orgs}
  {include file="CRM/common/pager.tpl" location="top"}
  <table class="selector row-highlight">
    <thead class="sticky">
      <tr>
        <th>{ts} Organization Name {/ts}</th>
        <th>{ts} Action {/ts}</th>
      </tr>
    </thead>
    <tbody>
      {foreach from=$orgs item=org}
        <tr>
          <td><a href="/civicrm/contact/view?reset=1&cid={$org.contact_id}"><div class="icon crm-icon Organization-icon"></div>{ts 1=$org.display_name} %1 {/ts}</a></td>
          <td>
            <span class="action-item crm-hover-button no-popup" title="Genarate PDF file of all organization's member.">
              <a href="/civicrm/genarate-pdf?org_id={$org.contact_id}">{ts} Genarate PDF {/ts}</a>
            </span>
          </td>
        </tr>
      {/foreach}
    </tbody>
  </table>
  {include file="CRM/common/pager.tpl" location="bottom"}
{/if}