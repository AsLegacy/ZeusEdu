{if ($userStatus == admin) || ($userStatus == 'direction')}
<div class="tab-pane table-responsive" id="tabs-5">
    {assign var=liste value=$listeNotifications.ecole}
    <table class="table table-condensed tableEdit">
        <thead>

			{include file='notification/edit/entete.tpl'}

		</thead>
        <!-- Notifications à l'ensemble de l'école -->
        {foreach from=$liste key=id item=uneNote}
        {assign var=nId value=$uneNote.id}
        <tr id="tr_{$nId}" data-id="{$nId}" data-type="ecole">
            <td style="width:1em">
                <input type="checkbox" class="checkDelete" id="check{$nId}" data-id="{$nId}" data-type="ecole">
            </td>
            <td style="width:1em">
                <button type="button" class="btn btn-default btnEdit btn-xs pop" data-id="{$nId}" data-type="ecole" data-content="Modifier cette annonce">
                    <i class="fa fa-pencil-square-o fa-lg text-success"></i>
                </button>
            </td>
            <td>
                <span data-content="Nombre de PJ" class="badge pop">{$listePJ.$id|count|default:0}</span>
            </td>
            <td style="width:1em">
                <button type="button" class="btn btn-danger btn-delete btn-xs" data-id="{$nId}" data-type="ecole">
                    <i class="fa fa-times"></i>
                </button>
            </td>
            <td><span class="debut">{$uneNote.dateDebut}</span></td>
			<td><span class="fin">{$uneNote.dateFin}</span></td>
			<td><span class="objet">{$uneNote.objet}</span></td>
            <td style="width:20%;">
                <span class="destinataire">Tous</span> <i class="fa fa-circle-thin pull-right urgence{$uneNote.urgence}" title="Niveau d'urgence {$uneNote.urgence}"></i>
            </td>
            <td>
                {if $uneNote.accuse == 1}
                <meter style="float:left; margin-right:0.5em" title="Cliquer pour voir le détail" data-container="body" class="showAccuse" value="{$listeAccuses.$nId|count|default:0}" min="0" max="{$listeAttendus.ecole.$nId}" data-id="{$nId}"></meter>
                <span class="discret">{$listeAccuses.$nId|count|default:0}/{$listeAttendus.ecole.$nId}</span>
                {else}N/A
                {/if}
            </td>
            <td title="mail envoyé" data-container="body">{if $uneNote.mail == 1}<i class="fa fa-envelope fa-lg text-success"></i>{else}&nbsp;{/if}</td>
            <td title="Note permanente" data-container="body">{if $uneNote.freeze == 1}<i class="fa fa-thumb-tack fa-lg text-success"></i>{else}&nbsp;{/if}</td>
        </tr>
        {/foreach}
        <tr>
            <th colspan="10">
                {if $liste|@count > 0}
                <input type="checkbox" class="selectAll"> Sélectionner <i class="fa fa-arrow-left"></i>
                <button class="btn btn-warning delModal" type="button" data-type="ecole">
                    <i class="fa fa-times text-danger"></i> Effacer
                </button>
                {else}
                <p class="avertissement">Aucune annonce dans cette catégorie</p>
                {/if}
            </th>
        </tr>
    </table>
</div>
{/if}
