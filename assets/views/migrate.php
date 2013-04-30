<!DOCTYPE html>
<html>
	<head>
		<title>Migrate - PHPixie</title>
		<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.2/css/bootstrap-combined.min.css" rel="stylesheet">
		<script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.2/js/bootstrap.min.js"></script>
		<style>
			form{
				margin:0px;
			}
		</style>
	</head>
	<body lang="en">
		<div class="container">
			<div class="alert">
				<strong>Warning!</strong> Make sure to backup your data before migrating! Remember to disable this module after you are done to prevent unwanted access.
			</div>
			<?php foreach($configs as $config=>$migrate): ?>
				<h2>'<?php echo strtoupper($config);?>' Migrations</h2>
				<table class="table table-striped">
					<thead>
						<th>Migration</th>
						<th>Apply</th>
					</thead>
					<tbody>
						
						<?php 	$new=true;
								foreach(array_reverse($migrate->versions) as $version): 
									$text  = $new?'Update to Revision':'Revert to Revision';
									$class = $new?'btn-success':'btn-warning';
									$icon =  $new?'icon-arrow-up':'icon-arrow-down';
									?>
									<tr>
										<td><?php echo $version->name; ?></td>
										<td>
											<?php if($version->name!=$migrate->current_version):	?>
												<form method="POST">
													<input type="hidden" name="config" value="<?php echo $config;?>" />
													<input type="hidden" name="version" value="<?php echo $version->name;?>" />
													<button type="submit" class="btn <?php echo $class;?>">
														<i class="<?php echo $icon;?>"></i> <?php echo $text;?>
													</button>
												</form>
												
											<?php else: 
												$new=false;
											?>
												
												<span class="label label-success">Current Revision</span>
												
											<?php endif;?>
										</td>
									</tr>
						<?php endforeach;?>
						
					</tbody>
				</table>
			<?php endforeach;?>
		</div>
	</body>
</html>