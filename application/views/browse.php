<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
		<meta name="description" content="">
		<meta name="author" content="">
		<title>Empire Archival Discovery Cooperative | Finding Aids at Your Fingertips</title>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/3.3.1//jquery.min.js"></script>
		<script src="<?php echo base_url("/js/empireadc.js"); ?>"></script>
		<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
		<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js"></script>
		<script src="<?php echo base_url("/js/isotope.pkgd.min.js"); ?>"></script>
</head>
	<body>

		<div id="headerContainer">
			<a href="https://beta.empireadc.org/" target="_self"> <div id="header"></div> </a>
		</div>

		<!-- Main jumbotron for a primary marketing message or call to action -->
		<div id="main-container" class="container">
			<div class="jumbotron" style="background: #ffffff;">
				<div class="container" style="margin-top: -36px;">
					<!-- Example row of columns -->
					<div class="row">
						<div class="col-md-12">
							<div id="logo" style="width: 300px; margin-left: auto; margin-right: auto;"><a href='/'><img src='https://www.empireadc.org/sites/www.empireadc.org/files/ead_logo.gif' style='width:300px;'/></div></a>
							<!--input type="text" id="searchBox" placeholder="Search Honor's Thesis Repository" /-->
							<h2>Sort by: </h2>
							<div id="sorts" class="button-group">
								<button class="button is-checked" data-sort-by="original-order">Total Finding Aids</button>
  								<button class="button" data-sort-by="name">Organization Name</button>
								<!--button class="button" data-sort-by="number">Total EADs - Ascending</button-->
							</div>

							<div id="browseList" class="grid">
								<?php
                                    $facets = $results->facet_counts->facet_fields->agency_facet;
                                        $facetList = " ";
                                        $i = 0;
                                        foreach ($facets as $row) {
                                            if ($i % 2 == 0) {
                                                $facetList = $row;
                                            } else {
                                                $facetList = $facetList;
                                                //$facetList = trim($facetList);
                                                //$link = base_url("eaditorsearch/agency") . "/" . rawurlencode($facetList);?>
											<div class="element-item"><h3 class='name'><a href='#' id='browseLink'><?php echo $facetList ; ?></a></h3><p class="number"><?php echo $row ; ?></p></div><?php
                                            }
                                            $i += 1;
                                        }
                                ?>
							</div>
						</div>
					</div><!-- row -->
				</div><!-- container -->

			</div>
			<!-- jumbotron -->

			</br>

		</div></br>
		<!-- main-container -->
		<div class="container">
			<p  class = "foot">

			</p>

		</div>
</body>
</html>
