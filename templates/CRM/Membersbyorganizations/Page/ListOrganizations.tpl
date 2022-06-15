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
          <td>{ts 1=$org.display_name} %1 {/ts}</td>
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