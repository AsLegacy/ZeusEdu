<ul class="list-unstyled">

{foreach from=$listeFlashInfos item="uneInfo"}
    <li data-id="{$uneInfo.id}" data-titre="{$uneInfo.titre}" data-texte="{$uneInfo.texte}" class="uneNews" style="padding: 0.5em 0">

        <div class="input-group">
            {if $userStatus == 'admin'}
            <span class="input-group-btn">
                <button title="Modifier cette nouvelle" type="button" class="btn btn-info btn-edit" name="button"><i class="fa fa-edit"></i></button>
                <button title="Supprimer cette nouvelle" type="button" class="btn btn-danger btn-del" name="button"><i class="fa fa-times"></i></button>
            </span>
            {else}
            <span class="input-group-btn">
                <button title="Voir cette nouvelle" type="button" class="btn btn-success" name="button" id="btn-eye">
                    <i class="fa fa-eye"></i>
                </button>
            </span>
            {/if}
            <button type="button" class="btn btn-default btn-titleNews btn-block"><span class="discret">{$uneInfo.date|date_format:"%d/%m/%Y"}</span> - {$uneInfo.titre}</button>
        </div>

    </li>
{/foreach}

</ul>
