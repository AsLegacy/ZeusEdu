<div class="container">

	<form name="choixCours" id="choixCours" method="POST" action="index.php" class="form-vertical" role="form">

	<div class="row">

		<div class="col-md-6 col-sm-12">

			<div class="btn-group btn-group-vertical" style="width:100%">
			{foreach from=$lesCours key=coursGrp item=data}
				<div class="input-group">
					<span class="input-group-addon"><i class="fa fa-hand-o-right"></i></span>
					<a class="btn btn-default btn-block" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" href="index.php?action=presences&amp;mode=tituCours&amp;coursGrp={$coursGrp}">
						{if isset($data.nomCours)}
							{$data.nomCours} <small>[{$coursGrp}]</small>
						{else}
							[{$coursGrp}] {$data.statut} {$data.libelle} {$data.nbheures}h
						{/if}
					</a>
				</div>

			{/foreach}
			</div>

		</div>  <!-- col-md-.. -->

		<div class="col-md-6 col-sm-12">

			{include file="$INSTALL_DIR/widgets/flashInfo/templates/index.tpl"}

		</div>  <!-- col-md-... -->

	</div>  <!-- row -->

	</form>

</div>
