<h4>Gestion des partages</h4>

<p class="notice">Cliquer dans le tableau à gauche pour voir les partages des dossiers et des fichiers</p>

<div id="listePartages" style="height: 25em; overflow: auto;">

{if isset($listePartages)}
    <span class="form-group">

        <p class="active form-control-static">{if $type == 'dir'}<i class="fa fa-folder-open-o"></i>{else}<i class="fa fa-file-o"></i>{/if}
        <strong id="sharedFileName" data-filename="{$fileName}" title="{$fileName}">
            {$fileName|truncate:40:'...'}
        </strong>
        {if $listePartages|@count > 0} <br>(partagé {$listePartages|@count}x) {/if}
    </span>

    <button type="button"
        class="btn btn-sm pull-right btn-primary"
        id="btn-share"
        data-filename="{$fileName}"
        data-type="{$type}"
        data-arborescence="{$arborescence}"
        title="Nouveau partage">
        <i class="fa fa-plus"></i> <i class="fa fa-share-alt"></i>
    </button>
    </p>

    <table class="table table-condensed table-striped">

        {foreach from=$listePartages key=shareId item=share}
        <tr data-shareid="{$share.shareId}">
            {if $share.type == 'ecole'}
            <td class="shared {$share.type} pop">
                <button type="button"
                    class="btn btn-success btn-xs shareEdit"
                    title="Édition"
                    data-shareid = "{$share.shareId}">
                    <i class="fa fa-edit"></i>
                </button>
            </td>
            <td class="cible">
                Tous les élèves de l'école<br>
                <span class="help-block pull-right">{$share.commentaire}</span>
            </td>
            <td style="width:2em;">
                <button type="button"
                    class="btn btn-danger btn-xs unShare"
                    data-fileId="{$share.fileId}"
                    data-shareid = "{$share.shareId}"
                    data-libelle="Tous les élèves"
                    title="Arrêter le partage">
                <i class="fa fa-share-alt"></i>
            </button>
            </td>
                {elseif $share.type == 'niveau'}
                <td class="shared {$share.type} pop">
                    <button type="button"
                        class="btn btn-success btn-xs shareEdit"
                        title="Édition"
                        data-fileId="{$share.fileId}"
                        data-shareid = "{$share.shareId}"
                        data-libelle="Tous les élèves de {$share.destinataire}e"
                        data-commentaire="{$share.commentaire}">
                    <i class="fa fa-edit"></i>
                    </button>
                </td>
                <td class="cible">
                    Tous les élèves de {$share.destinataire}e<br>
                    <span class="help-block pull-right">{$share.commentaire}</span>
                </td>
                <td style="width:2em;">
                    <button
                        type="button"
                        class="btn btn-danger btn-xs unShare"
                        data-fileId="{$share.fileId}"
                        data-shareid = "{$share.shareId}"
                        data-libelle="Tous les élèves de {$share.destinataire}e"
                        title="Arrêter le partage">
                    <i class="fa fa-share-alt"></i>
                </button>
                </td>
                {elseif $share.type == 'classes'}
                    {if $share.destinataire == 'all'}
                        <td class="shared {$share.type} pop">
                            <button
                                type="button"
                                class="btn btn-success btn-xs shareEdit"
                                title="Édition"
                                data-fileId="{$share.fileId}"
                                data-shareid = "{$share.shareId}"
                                data-libelle="Tous les élèves de {$share.groupe}"
                                data-commentaire="{$share.commentaire}">
                                <i class="fa fa-edit"></i>
                            </button>
                        </td>
                        <td class="cible">
                            Élèves de {$share.groupe}: TOUS<br>
                            <span class="help-block pull-right">{$share.commentaire}</span>
                        </td>
                        <td style="width:2em;">
                            <button
                                type="button"
                                class="btn btn-danger btn-xs unShare"
                                data-fileId="{$share.fileId}"
                                data-shareid = "{$share.shareId}"
                                data-libelle="Tous les élèves de {$share.groupe}"
                                title="Arrêter le partage">
                            <i class="fa fa-share-alt"></i>
                        </button>
                        </td>
                    {else}
                        <td class="shared {$share.type} pop">
                            <button
                                type="button"
                                class="btn btn-xs btn-success shareEdit"
                                title="Édition"
                                data-fileId="{$share.fileId}"
                                data-shareid = "{$share.shareId}"
                                data-libelle="{$share.nomEleve|escape:'htmlall'} {$share.prenomEleve|escape:'htmlall'} de {$share.classe}"
                                data-commentaire="{$share.commentaire}">
                                <i class="fa fa-edit"></i>
                            </button>
                        </td>
                        <td class="cible">
                            Élèves de {$share.classe}: {$share.nomEleve} {$share.prenomEleve}<br>
                            <span class="help-block pull-right">{$share.commentaire}</span>
                        </td>
                        <td style="width:2em;">
                            <button
                                type="button"
                                class="btn btn-danger btn-xs unShare"
                                data-fileId="{$share.fileId}"
                                data-shareid = "{$share.shareId}"
                                data-libelle="{$share.nomEleve|escape:'htmlall'} {$share.prenomEleve|escape:'htmlall'} de {$share.classe}"
                                title="Arrêter le partage">
                            <i class="fa fa-share-alt"></i>
                        </button>
                        </td>
                    {/if}
                {elseif $share.type == 'coursGrp'}
                    {if $share.destinataire == 'all'}
                        <td class="shared {$share.type} pop">
                            <button
                                type="button"
                                class="btn btn-xs btn-success shareEdit"
                                title="Édition"
                                data-fileId="{$share.fileId}"
                                data-shareid = "{$share.shareId}"
                                data-libelle="Tous les élèves du cours {$share.groupe}"
                                data-commentaire="{$share.commentaire}">
                                <i class="fa fa-edit"></i>
                            </button>
                        </td>
                        <td class="cible">
                            Tous les élèves de {$share.groupe}<br>
                            <span class="help-block pull-right">{$share.commentaire}</span>
                        </td>
                        <td style="width:2em;">
                            <button
                                type="button"
                                class="btn btn-danger btn-xs unShare"
                                data-fileId="{$share.fileId}"
                                data-shareid = "{$share.shareId}"
                                data-libelle="Tous les élèves du cours {$share.groupe}"
                                title="Arrêter le partage">
                            <i class="fa fa-share-alt"></i>
                        </button>
                        </td>
                    {else}
                        <td class="shared {$share.type} pop" title="{$share.groupe}">
                            <button
                                type="button"
                                class="btn btn-xs btn-success shareEdit"
                                title="Édition"
                                data-fileId="{$share.fileId}"
                                data-shareid = "{$share.shareId}"
                                data-libelle="{$share.nomEleve|escape:'htmlall'} {$share.prenomEleve|escape:'htmlall'} du cours  {$share.libelle|escape:'htmlall'}"
                                data-commentaire="{$share.commentaire}">
                                <i class="fa fa-edit"></i>
                            </button>
                        </td>
                        <td class="cible">
                            {$share.nomEleve} {$share.prenomEleve} de
                            {if $share.nomCours != ''}
                                {$share.nomCours|escape:'htmlall'}
                                {else}
                                {$share.libelle|escape:'htmlall'}
                            {/if}
                            <br>
                            <span class="help-block pull-right">{$share.commentaire}</span>
                        </td>
                        <td style="width:2em;">
                            <button
                                type="button"
                                class="btn btn-danger btn-xs unShare"
                                data-fileId="{$share.fileId}"
                                data-shareid = "{$share.shareId}"
                                data-libelle="{$share.nomEleve|escape:'htmlall'} {$share.prenomEleve|escape:'htmlall'} du cours  {$share.libelle|escape:'htmlall'}"
                                title="Arrêter le partage">
                                <i class="fa fa-share-alt"></i>
                            </button>
                        </td>
                    {/if}
                {elseif $share.type == 'prof'}
                    {if $share.destinataire == 'all'}
                        <td class="shared {$share.type}">
                            <button
                                type="button"
                                class="btn btn-xs btn-success shareEdit"
                                title="Édition"
                                data-fileId="{$share.fileId}"
                                data-shareid = "{$share.shareId}"
                                data-libelle="Tous les collègues"
                                data-commentaire="{$share.commentaire}">
                                <i class="fa fa-edit"></i>
                            </button>
                        </td>
                        <td class="cible">
                            Tous les collègues<br>
                            <span class="help-block pull-right">{$share.commentaire}</span>
                        </td>
                        <td style="width:2em;">
                            <button
                                type="button"
                                class="btn btn-danger btn-xs unShare"
                                data-fileId="{$share.fileId}"
                                data-shareid = "{$share.shareId}"
                                data-libelle="Tous les collègues"
                                title="Arrêter le partage">
                            <i class="fa fa-share-alt"></i>
                        </button>
                        </td>
                    {else}
                        <td class="shared {$share.type} pop">
                            <button
                                type="button"
                                class="btn btn-xs btn-success shareEdit"
                                title="Édition"
                                data-fileId="{$share.fileId}"
                                data-shareid = "{$share.shareId}"
                                data-libelle="{$share.prenomProf|escape:'htmlall'} {$share.nomProf|escape:'htmlall'}"
                                data-commentaire="{$share.commentaire}">
                                <i class="fa fa-edit"></i>
                            </button>
                        </td>
                        <td class="cible">
                            Collègue: {$share.prenomProf} {$share.nomProf}<br>
                            <span class="help-block pull-right">{$share.commentaire}</span>
                        </td>
                        <td style="width:2em;">
                            <button
                                type="button"
                                class="btn btn-danger btn-xs unShare"
                                data-fileId="{$share.fileId}"
                                data-shareid = "{$share.shareId}"
                                data-libelle="{$share.prenomProf|escape:'htmlall'} {$share.nomProf|escape:'htmlall'}"
                                title="Arrêter le partage">
                            <i class="fa fa-share-alt"></i>
                        </button>
                        </td>
                    {/if}
                {/if}
        </tr>

        {/foreach}
    </table>

{/if}

</div>
