<script type="text/javascript" src="../ckeditor/ckeditor.js"></script>
<link href="css/filetree.css" type="text/css" rel="stylesheet">

<div class="container-fluid">

	{if $notification.type == 'cours'}
	<h2>Annonce pour le cours {$notification.destinataire}</h2> {/if}
	{if $notification.type == 'classes'}
	<h2>Annonce pour la classe {$notification.destinataire}</h2> {/if}
	{if $notification.type == 'niveau'}
	<h2>Annonce pour tous les élèves de {$notification.destinataire}e</h2> {/if}
	{if $notification.type == 'ecole'}
	<h2>Annonce pour tous les élèves</h2> {/if}

	<form enctype="multipart/form-data" name="notification" id="notification" method="POST" action="index.php" role="form" class="form-vertical">

		<input type="hidden" name="action" value="notification">
		<input type="hidden" name="mode" value="{$mode}">
		<input type="hidden" name="etape" value="enregistrer">
		<input type="hidden" name="notifId" value="{$notification.id}">
		<input type="hidden" name="destinataire" id="destinataire" value="{$notification.destinataire}">
		<input type="hidden" name="classe" id="classe" value="{$classe|default:''}">
		<input type="hidden" name="coursGrp" id="coursGrp" value="{$coursGrp|default:''}">
		<input type="hidden" name="niveau" id="niveau" value="{$niveau|default:''}">
		<input type="hidden" name="proprietaire" value="{$notification.proprietaire}">
		<input type="hidden" name="type" id="type" value="{$notification.type}">
		<input type="hidden" name="matricule" value="{$matricule|default:''}">
		<input type="hidden" name="edition" value="{$edition|default:Null}">

		<div class="row">

				<div class="col-md-9 col-sm-8">

					<div class="panel panel-default">

						<div class="panel-heading">
							<div class="row">
								<div class="col-xs-10">
									<input type="text" maxlength="80" name="objet" id="objet" placeholder="Objet de votre note" class="form-control" value="{$notification.objet|default:''}">
								</div>
								<div class="col-xs-2">
									<button type="button" class="btn btn-info pull-right" id="btn-join" data-coursgrp="{$coursGrp|default:''}" data-niveau="{$niveau|default:''}" data-classe="{$classe|default:''}" title="Partager et joindre un document"><i class="fa fa-file-o"></i></button>
								</div>
							</div>
						</div>
						<!-- panel-heading -->

						<div class="panel-body">

							<div class="form-group col-md-2 col-xs-4">
								<label for="dateDebut">Date début</label>
								<input type="text" name="dateDebut" id="dateDebut" placeholder="Début" class="datepicker form-control" value="{$notification.dateDebut}" title="La notification apparaît à partir de cette date">
							</div>

							<div class="form-group col-md-2 col-xs-4">
								<label for="dateFin">Date fin</label>
								<input type="text" name="dateFin" id="dateFin" placeholder="Fin" class="datepicker form-control" value="{$notification.dateFin}" title="La notification disparaît à partir de cette date">
								<div class="help-block">Déf:+1mois</div>
							</div>
							<div class="form-group col-md-2 col-xs-4">
								<label for="mail" title="Un mail d'avertissement est envoyé">Envoi<br>mail</label>
								<input type="checkbox" name="mail" id="mail" class="form-control-inline" value="1"
								{if isset($notification.mail) && $notification.mail==1 } checked='checked' {/if}
								{if $notification.type=='ecole' || $notification.type=='niveau' || isset($edition)} disabled{/if}>
								{* disabled pour l'envoi de mail à toute l'école ou à tout un niveau afin d'éviter les over quota d'envois *}
								{* disabled en cas d'édition (on ne change pas les règles en cours de route) *}
							</div>

							<div class="form-group col-md-2 col-xs-4">
								<label for="accuse" title="Un accusé de lecture est demandé">Accusé<br>lecture</label>
								<input type="checkbox" name="accuse" id="accuse" class="form-control-inline" value="1"
								{if isset($notification.accuse) && $notification.accuse==1 } checked='checked' {/if}
								{if $notification.type=='ecole' || isset($edition)} disabled{/if}>
								{* disabled pour toute l'école car ingérable *}
								{* disabled en cas d'édition (on ne change pas les règles en cours de route) *}
							</div>

							<div class="form-group col-md-2 col-xs-4">
					            <label for="freeze" title="Notification permanente, n'est pas effacée après la date finale">Perma-<br>nent</label>
					            <input type='checkbox' name='freeze' id='freeze' class='form-control-inline' value='1'
					            {if (isset($notification.freeze)) && ($notification.freeze==1 )} checked{/if}>
					        </div>

							<div class="form-group col-md-2 col-xs-4">
								<label for="parents" title="Notification par mail aux parents (qui ont accepté ces envois)">Mail<br>Parents</label>
								<input type='checkbox' name='parent' id='parent' class='form-control-inline' value='1'
								{if (isset($notification.parents)) && ($notification.parents==1 )} checked{/if}
								{if $notification.type=='ecole' || isset($edition)} disabled{/if}>
							</div>

							<div class="clearfix">
							</div>

							<textarea id="texte" name="texte" rows="25" class="ckeditor form-control" placeholder="Frappez votre texte ici" autofocus="true">{$notification.texte|default:''}</textarea>

							<ul id="PjFiles" class="list-unstyled">

								{include file="notification/edit/pjFiles.tpl"}

							</ul>

							<div class="btn-group pull-right">
								<button type="reset" class="btn btn-default">Annuler</button>
								<button type="submit" class="btn btn-primary">Envoyer</button>
							</div>

						</div>
						<!-- panel-body -->

						<div class="panel-footer">
							{if $notification.type == 'niveau' || $notification.type == 'ecole'}
					            <p>L'envoi de mails est désactivé pour les notifications à tout un niveau ou à toute l'école</p>
					        {/if}
					        {if $notification.type == 'ecole'}
					            <p>La demande d'accusé de lecture est désactivée pour les notifications à l'ensemble de l'école</p>
					        {/if}
					        {if isset($edition)}
					            Certaines options ne sont pas modifiables
					        {/if}
						</div>

					</div>
					<!-- panel -->

				</div>
				<!-- col-md-...  -->

				<div class="col-md-3 col-xs-4">
					<!-- le choix d'élèves en particulier n'est possible que pour les classes et les cours -->
					{if ($notification.type != 'niveau') && ($notification.type != 'ecole')}
					{include file="notification/listeEleves.tpl"}
					{else}
					<p class="notice">Il n'est pas possible de ne sélectionner que certains élèves dans ce mode.</p>
					{/if}
				</div>
				<!--col-md-... -->

			</div>
			<!-- row -->

	</form>

	</div>
	<!-- container -->

	<div id="modalTreeView" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalTreeViewLabel" aria-hidden="true">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
	          <span aria-hidden="true">&times;</span>
	        </button>
	        <h4 class="modal-title" id="modalTreeViewLabel">Fichiers à joindre</h4>
	      </div>
	      <div class="modal-body" style="max-height:20em; overflow: auto">
			  <form id="treeviewModal">
				  {include file='notification/treeview4PJ.tpl'}
			  </form>
	      </div>
	      <div class="modal-footer">
				<button type="button" class="btn btn-primary pull-right" data-dismiss="modal">Terminer</button>
	      </div>
	    </div>
	  </div>
	</div>

<script type="text/javascript">

	$(document).ready(function() {

		CKEDITOR.replace('texte');

		//  dans la boîte modale, cocher les cases des fichiers déjà joints à l'annonce
		$('#PjFiles li').each(function(index){
			var path = $(this).find('.delPJ').data('path');
			var fileName = $(this).find('.delPJ').data('filename')
			// cocher la case si le fichier est lié à cette annonce
			$('.file[data-filename="' + fileName + '"][data-path="' + path +'"]').find('input').prop('checked', true);
		})

		$('#btn-join').click(function(){
			$('#modalTreeView').modal('show');
		})

		$("#modalTreeView").on('click', '.dirLink', function(event) {
			$(this).next('.filetree').toggle('slow');
			$(this).closest('li').toggleClass('expanded');
			$('.dirLink').removeClass('activeDir');
			$(this).addClass('activeDir');
		})

		$('#modalTreeView').on('click', '.selectFile', function(){
			var fileName = $(this).closest('li').data('filename');
			var path = $(this).closest('li').data('path');

			if ($(this).prop('checked') == true) {
				$('#PjFiles').append('<li><a href="javascript:void(0)" class="delPJ" data-path="'+ path + '" data-filename="' + fileName + '"><i class="fa fa-times text-danger" title="Supprimer"></i></a> ' + path + fileName + '<input type="hidden" name="files[]" value="' + path + '|//|' + fileName + '"></li>');
			}
			else {
				$('#PjFiles').find('[data-path="' + path + '"][data-filename="' + fileName + '"]').parent().remove();
			}
		})

		$('body').on('click', '.delPJ', function(){
			var fileName = $(this).data('filename');
			var path = $(this).data('path');
			$('.file[data-path="' + path + '"][data-filename="' + fileName + '"]').find('input').prop('checked', false);

			$(this).parent().remove();
		})

		$("#dateDebut").datepicker({
				format: "dd/mm/yyyy",
				clearBtn: true,
				language: "fr",
				calendarWeeks: true,
				autoclose: true,
				todayHighlight: true
			})
			.off('focus')
			.click(function() {
				$(this).datepicker('show');
			});

		$("#dateFin").datepicker({
			format: "dd/mm/yyyy",
			clearBtn: true,
			language: "fr",
			calendarWeeks: true,
			autoclose: true,
			todayHighlight: true
		});

		$("#notification").validate({
			rules: {
				'objet': {
					required: true
					},
				'membres[]': {
					required: true,
					minlength: 1
					}
				},
			messages: {
				'objet': 'Veuille indiquer un objet pour votre note',
				'membres[]': 'Sélectionner au moins un élève'
				}
		});

	})

</script>
