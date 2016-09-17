<div class="modal fade" id="modalDelFile" tabindex="-1" role="dialog" aria-labelledby="titleDelFile" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="titleDelFile">Effacement d'un fichier</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fa fa-warning fa-2x pull-right text-danger"></i>
                    <p>Veuillez confirmer l'effacement définitif du fichier <strong id="delFileName"></strong>
                        <br> dans le dossier <strong id="delPath"></strong></p>
                </div>
                <div id="modalDelShareList">

                </div>
            </div>
            <div class="modal-footer">
                <div class="btn-group pull-right">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDelFile">Confirmer</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {

        $("#confirmDelFile").click(function() {
            var fileName = $('#delFileName').text();
            var path = $('#delPath').text();
            $.post('inc/files/delFile.inc.php', {
                    fileName: fileName,
                    path: path
                },
                function(resultat) {
                    if (resultat == 1) {
                        // suppression dans l'arboresence
                        $('.activeFile').remove();
                        $('#fileInfo').hide();
                        $('#partages').html('-');
                    }
                    else alert('Impossible de supprimer ce fichier');
                });
            $("#modalDelFile").modal('hide');

        })
    })
</script>
