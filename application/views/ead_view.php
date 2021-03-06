<!DOCTYPE html>
<html lang="en">
<head prefix="dcterms: http://purl.org/dc/terms/">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css">
  <!--link rel="stylesheet" href="styles/bootstrap.css"-->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js"></script>
  <!--EmpireADC Drupal CSS -->
  <link href="https://empireadc.org/sites/empireadc.org/themes/esln_ead/css/style.css" rel="stylesheet">
  <link href="https://empireadc.org/sites/empireadc.org/themes/esln_ead/css/media.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url("/styles/main.css"); ?>"/>
  <link rel="stylesheet" type="text/css" href="<?php echo base_url("/styles/chronlogy.css"); ?>"/>
  <link rel="stylesheet" type="text/css" href="<?php echo base_url("/styles/768.css"); ?>"/>

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-74987537-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-74987537-1');
</script>

  <style>
    ul{list-style-type:none;}
    li.Subseries{margin-left: 20px;}
     #tocResponsive {
        display: none;
      }
    @media only screen and (max-width: 600px) {
      #toc {
        visibility: hidden;
      }
      #tocResponsive {
        display:block;
        margin-top: 30px;
      }
    }
  </style>

  <?php
    #Define URL for solr and exist in config.php
    $exist_url= $this->config->item('exist_url');
    $exist_url_ext= $this->config->item('exist_url_ext');
    $solr_url = $this->config->item('solr_url');

    $this->load->helper('url');
     #make sure it does not have  an extension
    $eadId = preg_replace('/\\.[^.\\s]{3,4}$/', '', $eadId);
    #$link = "https://www.empireadc.org/ead/". strtolower($collId) ."/id/".$eadId.".xml";
    #Link directly to exist to help with large size xml
    $link =$exist_url."/exist/rest/db/empireADC/". strtolower($collId) ."/guides/".$eadId.".xml";
$link_ext =$exist_url_ext."/exist/rest/db/empireADC/". strtolower($collId) ."/guides/".$eadId.".xml";
    $rdf = "https://www.empireadc.org/ead/". $collId ."/id/".$eadId.".rdf";
    $is_chron_available = false;

    $GLOBALS['tree'] = ' ';
    $reader = new XMLReader();
    $reader->open($link);
    while ($reader -> read()) {
        if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'ead') {
            $doc = new DOMDocument('1.0', 'UTF-8');
            $xml = simplexml_import_dom($doc->importNode($reader->expand(), true));
            $title = $xml->archdesc->did->unittitle;
            $repository = (isset($xml->archdesc->did->repository->corpname)? $xml->archdesc->did->repository->corpname : $xml->archdesc->did->repository);
            $subarea = (isset($xml->archdesc->did->repository->subarea)? $xml->archdesc->did->repository->subarea : $xml->archdesc->did->subarea);
            $rURL = " ";
            $repo = (isset($xml->archdesc->did->repository->extptr)? true : false);
            if ($repo == true) {
                $repo = $xml->archdesc->did->repository->extptr;
                $repoAttr = $repo ->  attributes('http://www.w3.org/1999/xlink');
                $rURL = $repoAttr['href'];
            }

            $addressline = array();
            $address = (isset($xml->archdesc->did->repository->address)? true : false);
            if ($address == true) {
                foreach ($xml->archdesc->did->repository->address->addressline as $a) {
                    array_push($addressline, $a);
                }
            }
            $extent = (isset($xml->archdesc->did->physdesc->extent)? $xml->archdesc->did->physdesc->extent : 'Unspecified');

            $creatorList = array();
            $cnt =  0;
            $x = 0;
            $creator =  (isset($xml->archdesc->did->origination)? true : false);
            if ($creator != false) {
                foreach ($xml->archdesc->did->origination as $c) {
                    if ($c->children()->getname() == 'persname') {
                        // array_push($creatorList, $c->persname);
                        $creatorList[$x][0] = $c->persname;
                        $creatorList[$x][1] = "persname_facet";
                        $x++;
                    } elseif ($c->children()->getname() == 'famname') {
                        //array_push($creatorList, $c->famname);
                        $creatorList[$x][0] = $c->famname;
                        $creatorList[$x][1] = "famname_facet";
                        $x++;
                    } elseif ($c->children()->getname() == 'corpname') {
                        //array_push($creatorList, $c->corpname);
                        $creatorList[$x][0] = $c->corpname;
                        $creatorList[$x][1] = "corpname_facet";
                        $x++;
                    }
                }
            }

            $location = (isset($xml->archdesc->did->physloc)? $xml->archdesc->did->physloc : 'Unspecified');

            $languageList = array();
            $multiLanguage = (isset($xml->archdesc->did->langmaterial-> language)? $xml->archdesc->did->langmaterial-> language : false);
            if ($multiLanguage == false) {
                array_push($languageList, $xml->archdesc->did->langmaterial);
            } elseif ($multiLanguage != false) {
                foreach ($xml->archdesc->did->langmaterial->language as $lang) {
                    array_push($languageList, $lang);
                }
            }

            $abstract = (isset($xml->archdesc->did->abstract)? $xml->archdesc->did->abstract : 'Unspecified');
            if ($abstract != 'Unspecified') {
                foreach ($xml->archdesc->did->abstract as $a) {
                    if ($a->getname() == 'abstract') {
                        $abstract = $a . "<br />\n" ;
                    }
                }
            }
            if (isset($xml->archdesc->descgrp->processinfo->p)) {
                $processInfo = (isset($xml->archdesc->descgrp->processinfo->p)? $xml->archdesc->descgrp->processinfo->p : 'Unspecified');
            } else {
                $processInfo = (isset($xml->archdesc->processinfo->p)? $xml->archdesc->processinfo->p : 'Unspecified');
            }
            if (isset($xml->archdesc->descgrp->prefercite->p)) {
                $prefercite = (isset($xml->archdesc->descgrp->prefercite->p)?  $xml->archdesc->descgrp->prefercite->p : 'Unspecified');
                if ($prefercite != 'Unspecified') {
                    foreach ($xml->archdesc->descgrp->prefercite->children() as $p) {
                        if ($p->getname() == 'p') {
                            $prefercite =  $p . "<br />\n" ;
                        }
                    }
                }
            } else {
                $prefercite = (isset($xml->archdesc->prefercite->p)?  $xml->archdesc->prefercite->p : 'Unspecified');
                if ($prefercite != 'Unspecified') {
                    foreach ($xml->archdesc->prefercite->children() as $p) {
                        if ($p->getname() == 'p') {
                            $prefercite =  $p . "<br />\n" ;
                        }
                    }
                }
            }
            if (isset($xml->archdesc->descgrp->accessrestrict)) {
                $access = (isset($xml->archdesc->descgrp->accessrestrict)? $xml->archdesc->descgrp->accessrestrict : 'Unspecified');
                if ($access != 'Unspecified') {
                    foreach ($xml->archdesc->descgrp->accessrestrict->children() as $p) {
                        if ($p->getname() == 'p') {
                            $access = $access . dom_import_simplexml($p)->textContent . "<br />\n" ;
                        }
                    }
                }
            } else {
                $access = (isset($xml->archdesc->accessrestrict)? $xml->archdesc->accessrestrict : 'Unspecified');
                if ($access != 'Unspecified') {
                    foreach ($xml->archdesc->accessrestrict->children() as $p) {
                        if ($p->getname() == 'p') {
                            $access = $access . dom_import_simplexml($p)->textContent . "<br />\n" ;
                        }
                    }
                }
            }
            if (isset($xml->archdesc->descgrp->userestrict)) {
                $copyright = (isset($xml->archdesc->descgrp->userestrict)? $xml->archdesc->descgrp->userestrict : 'Unspecified');
                if ($copyright!= 'Unspecified') {
                    foreach ($xml->archdesc->descgrp->userestrict->children() as $p) {
                        if ($p->getname() == 'p') {
                            $copyright = $copyright . dom_import_simplexml($p)->textContent . "<br />\n" ;
                        }
                    }
                }
            } else {
                $copyright = (isset($xml->archdesc->userestrict)? $xml->archdesc->userestrict : 'Unspecified');
                if ($copyright!= 'Unspecified') {
                    foreach ($xml->archdesc->userestrict->children() as $p) {
                        if ($p->getname() == 'p') {
                            $copyright = $copyright . dom_import_simplexml($p)->textContent . "<br />\n" ;
                        }
                    }
                }
            }

            $acqInfo = (isset($xml->archdesc->descgrp->acqinfo)? $xml->archdesc->descgrp->acqinfo : 'Unspecified');
            if ($acqInfo != 'Unspecified') {
                foreach ($xml->archdesc->descgrp->acqinfo->children() as $p) {
                    if ($p->getname() == 'p') {
                        if (isset($p->extref)) {
                            $acqInfo = $acqInfo . dom_import_simplexml($p)->textContent . "<br />\n" ;
                        } else {
                            $acqInfo = $acqInfo . $p . "<br />\n" ;
                        }
                    }
                }
            } else {
                $acqInfo = (isset($xml->archdesc->acqinfo)? $xml->archdesc->acqinfo : 'Unspecified');
                if ($acqInfo!= 'Unspecified') {
                    foreach ($xml->archdesc->acqinfo->children() as $p) {
                        if ($p->getname() == 'p') {
                            if (isset($p->extref)) {
                                $acqInfo = $acqInfo . dom_import_simplexml($p)->textContent . "<br />\n" ;
                            } else {
                                $acqInfo = $acqInfo . $p . "<br />\n" ;
                            }
                        }
                    }
                }
            }

            $accruals  = (isset($xml->archdesc->descgrp->accruals)? $xml->archdesc->descgrp->accruals : 'Unspecified');
            if ($accruals != 'Unspecified') {
                foreach ($xml->archdesc->descgrp->accruals->children() as $p) {
                    if ($p->getname() == 'p') {
                        $accruals = $accruals . $p . "<br />\n" ;
                    }
                }
            }

            $prefCitation = (isset($xml->archdesc->prefercite->p[1])? $xml->archdesc->prefercite->p[1] : 'Unspecified');

            $histNote = (isset($xml->archdesc->bioghist)? $xml->archdesc->bioghist : 'Unspecified');
            if ($histNote != 'Unspecified') {
                $chronList = array();
                foreach ($xml->archdesc->bioghist->children() as $p) {
                    if ($p->getname() == 'p') {
                        $histNote = $histNote .  dom_import_simplexml($p)->textContent . "<br /><br />\n" ;
                    } elseif ($p ->getname() == 'chronlist') {
                        $is_chron_available = true;
                    }
                }
            }

            $scopeContent = (isset($xml->archdesc->scopecontent)? $xml->archdesc->scopecontent : 'Unspecified');
            if ($scopeContent != 'Unspecified') {
                foreach ($xml->archdesc->scopecontent->children() as $p) {
                    if ($p->getname() == 'p') {
                        $scopeContent = $scopeContent  . $p . "<br /><br />\n" ;
                    } elseif ($p->getname() == 'list') {
                        foreach ($xml->archdesc->scopecontent->list->children() as $c) {
                            if ($c -> getname() == 'head') {
                                $scopeContent = $scopeContent . "<h4>" . $c . "</h4>";
                            } else {
                                $scopeContent = $scopeContent . dom_import_simplexml($c)->textContent  . "<br />";
                            }
                        }
                    }
                }
            }

            $arrangement = (isset($xml->archdesc->arrangement)? $xml->archdesc->arrangement : 'Unspecified');
            if ($arrangement != 'Unspecified') {
                foreach ($xml->archdesc->arrangement->children() as $p) {
                    if ($p->getname() == 'p') {
                        $arrangement = $arrangement . $p . "<br />\n" ;
                        if (isset($p->list->item)) {
                            foreach ($p->list->children() as $c) {
                                $arrangement = $arrangement . $c->ref . "<br />\n" ;
                            }
                        }
                    } elseif ($p->getname() == 'note') {
                        $arrangement = $arrangement . $p . "<br />\n" ;
                    } elseif ($p->getname() == 'list') {
                        foreach ($xml->archdesc->arrangement->list->children() as $c) {
                            if ($c -> getname() == 'head') {
                                $arrangement = $arrangement . "<h4>" . $c . "</h4>";
                            } else {
                                $arrangement = $arrangement . dom_import_simplexml($c)->textContent  . "<br />";
                            }
                        }
                    }
                }
            }


            // $relatedMaterialList = array();
            $relatedMaterialLink = array();
            $relatedMaterial = (isset($xml->archdesc->relatedmaterial)? true : false);
            if ($relatedMaterial == true) {
                $relatedMaterialChild = (isset($xml->archdesc->relatedmaterial->p->extref)? true : false);
                if ($relatedMaterialChild == true) {
                    $linksAvailable = true;
                    $i = 0;
                    foreach ($xml->archdesc->relatedmaterial->p->extref as $rm) {
                        $rmLinkAttr = $rm -> attributes('http://www.w3.org/1999/xlink');
                        $rmLink = $rmLinkAttr['href'];
                        $relatedMaterialLink[$i][0] = $rm;
                        $relatedMaterialLink[$i][1] = $rmLink;
                        $i = $i + 1;
                    }
                } else { //to deal with two variations in relatedmaterial encoding
                    $linksAvailable = false;
                    $i = 0;
                    foreach ($xml->archdesc->relatedmaterial->p as $rm) {
                        if (isset($rm->emph)) {
                            $relatedMaterialLink[$i][0] = dom_import_simplexml($rm)->textContent;
                            $i = $i + 1;
                        } elseif (isset($rm->extref)) {
                            $relatedMaterialLink[$i][0] = dom_import_simplexml($rm)->textContent;
                            $i = $i + 1;
                        } else {
                            $relatedMaterialLink[$i][0] = $rm;
                            $i = $i + 1;
                        }
                    }
                }
            }

            $bibliography = (isset($xml->archdesc->bibliography)? $xml->archdesc->bibliography : 'Unspecified');
            if ($bibliography != 'Unspecified') {
                foreach ($xml->archdesc->bibliography->children() as $p) {
                    if ($p->getname() == 'p') {
                        if (isset($p->emph)) {
                            $bibliography = $bibliography . dom_import_simplexml($p)->textContent. "<br /><br />\n" ;
                        } else {
                            $bibliography = '<li > '.$bibliography  . $p . '</li>\n' ;
                        }
                    } elseif ($p->getname() == 'list') {
                        foreach ($xml->archdesc->bibliography->list->children() as $c) {
                            if ($c -> getname() == 'head') {
                                $bibliography = $bibliography . "<h4>" . $c . "</h4>";
                            } else {
                                $bibliography = $bibliography . dom_import_simplexml($c)->textContent  . "<br />";
                            }
                        }
                    }
                }
            }

            $seperateMaterial = (isset($xml->archdesc->separatedmaterial)? $xml->archdesc->separatedmaterial : 'Unspecified');
            if ($seperateMaterial != 'Unspecified') {
                foreach ($xml->archdesc->separatedmaterial->children() as $p) {
                    if ($p->getname() == 'p') {
                        $seperateMaterial  = $seperateMaterial  . $p . "<br />\n" ;
                    }
                }
            }

            $componentList = (isset($xml->archdesc->dsc->c)? true : false);
            $digitalObject = (isset($xml->archdesc->did->daogrp)? true : false);
            $otherfindaids = (isset($xml->archdesc->otherfindaid->bibref->extptr)? $xml->archdesc->otherfindaid->bibref->extptr : false);
            if ($otherfindaids != false) {
                $otherfindaidsAttr = $otherfindaids -> attributes('http://www.w3.org/1999/xlink');
                $filename = $otherfindaidsAttr['href'];
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $iconLink = '/empiresearch/icons/';
                if ($ext == 'docx') {
                    $iconLink = $iconLink . 'word.png';
                } elseif ($ext == 'doc') {
                    $iconLink = $iconLink . 'word.png';
                } elseif ($ext == 'pdf') {
                    $iconLink = $iconLink . 'adobe.png';
                } elseif ($ext == 'xlsx') {
                    $iconLink = $iconLink . 'excel.png';
                }
                $downloadLink = "https://www.empireadc.org/ead/uploads/". $collId ."/".$otherfindaidsAttr['href'];
            }

            $dateRange = array();
            if (isset($xml->archdesc->did->unitdate)) {
                foreach ($xml->archdesc->did->unitdate as $x) {
                    $dateValue = ucfirst($x['type']). ' Date: '.$x ;
                    array_push($dateRange, $dateValue);
                }
            } elseif (isset($xml->archdesc->did->unittitle->unitdate)) {
                foreach ($xml->archdesc->did->unittitle->unitdate as $x) {
                    $dateValue = ucfirst($x['type']). ' Date: '.$x ;
                    array_push($dateRange, $dateValue);
                }
            }
        }
    } //while ends

    ?>

    <style>
        .p-list {
            list-style:disc outside none;
            display:list-item;
        }
    </style>
  <title><?php echo $title." -- ".$repository;?></title>
</head>
<body>

<?php

    function seriesLevel($level, $obj, $collId, $repository)
    { //recursive function that creates placeholder for series and other sub levels
        $flag = 0;
        $component = 0;
        $fileLevel = 0;
        $titleInfo = ' '; ?>
		<div class="<?php echo $level; ?> seriesRow">
					<?php
              foreach ($obj->did->children() as $childObj) {
                  if ($childObj->getname() == 'unittitle') {
                      if (count($childObj) > 0) {
                          echo "<h4 id = '". ucfirst($level) . $obj->did->unitid."'>";

                          if (isset($obj->did->unittitle)) {
                              echo ucfirst($level) . " " . $obj->did->unitid . ": " . $obj->did->unittitle."</h4>";
                          } else {
                              echo ucfirst($level) . " " . $obj->did->unitid . ": " . $childObj->title . $childObj->title->emph . $childObj->emph."</h4>";
                          }
                      } else {
                          echo "<h4 id = '". ucfirst($level) . $obj->did->unitid."'>";
                          echo ucfirst($level) . " " . $obj->did->unitid . ": " . $childObj ."</h4>";
                          if (isset($obj->did->abstract)) {
                              echo "<h4 style='line-height: 24px;'>Abstract: ". $obj->did->abstract."</h4>";
                          }

                          if (isset($obj->phystech->note->p)) {
                              echo "<h4 style='line-height: 24px;'>Physical Characteristics: ". $obj->phystech->note->p."</h4>";
                          }
                      }
                      if (isset($obj->arrangement->p)) {
                          echo "<h4 style='line-height: 24px;'>Arrangement: ". $obj->arrangement->p."</h4>";
                      }
                      if (isset($obj->did->physdesc->extent)) {
                          echo "<h4 style='line-height: 24px;'>Size: ". $obj->did->physdesc->extent."</h4>";
                      }
                  } elseif ($childObj->getname() == 'unitdate') {
                      echo "<p>". ucfirst($childObj['type'])." Date: ".$childObj."</p>";
                  } elseif ($childObj->getname() == 'container') {
                      echo "<p>". ucfirst($childObj['type']).": ". $childObj."</p>";
                  }
              }
        if (isset($obj->scopecontent->p)) {
            echo "<p style='line-height: 24px;'>";
            echo $obj->scopecontent->p;
            echo "</p>";
        }
        if ($obj->did->unittitle != '') {
            $titleInfo = $obj->did->unittitle;
        }
        if ($obj->did->unittitle->emph != '') {
            $titleInfo = $obj->did->unittitle->emph;
        }
        $GLOBALS['tree'] = $GLOBALS['tree'] . '<li class="'. ucfirst($level) . '"><a href="' . '#' . ucfirst($level) . $obj->did->unitid . '"' . ' class="tocLink">' .  ucfirst($level) . " " . $obj->did->unitid . ": " . $titleInfo . '</a></li>';
        $GLOBALS['tree'] = str_replace("'", "&#039;", $GLOBALS['tree']); ?>

          			<!-- Check if this series has children levels -->
          			<?php
                      foreach ($obj->children() as $grandchildObj) {
                          if ($grandchildObj->getname() == 'c') {
                              $cAttr1 = $grandchildObj->attributes();

                              $cLevel1 = $cAttr1["level"];

                              if ($cLevel1 == 'otherlevel' || $cLevel1 == 'subseries' || $cLevel1 == 'series') {
                                  $flag = 1;
                                  seriesLevel($cLevel1, $grandchildObj, $collId, $repository);
                              } elseif ($cLevel1 == 'file') {
                                  $fileLevel = 1;
                              } else {
                                  $fileLevel = 1 ;
                              }
                          }
                      }
        if ($flag == 0) { // if no other level exists, display the files
            if ($fileLevel == 1) {
                ?>
              	<button type="button"  class="btn btn-custm" data-toggle="collapse" data-target="#<?php echo $obj['id']; ?>" style="margin-bottom: 5px; text-decoration: none; color: #fff;">View the files.</button>
                <div id="<?php echo $obj['id']; ?>" class="collapse" style="width: 75%; border-left: 1px solid #ccc; border-right: 1px solid #ccc; margin-left:auto; margin-right: auto;">
								<?php
                                    foreach ($obj->c as $fileObj) {
                                        echo "<div class='fileRow'>";
                                        foreach ($fileObj->children() as $c) {
                                            if ($c->getname() == 'did') {
                                                foreach ($fileObj->did->children() as $file) {
                                                    if ($file->getname() == 'unittitle') {
                                                        if (count($file) > 0) {
                                                            echo "<h4>". $file->title."</h4>";
                                                            $component = $file->title;
                                                            echo "<h4>". $file->emph;
                                                            #Disable not sure if needed
                                                            #echo $file ."</h4>";
                                                        } else {
                                                            echo "<h4>". $file."</h4>";
                                                            $component = $file;
                                                        }
                                                    } elseif ($file->getname() == 'unitdate') {
                                                        echo "<p>". ucfirst($file['type'])." Date: ".$file ."</p>";
                                                    } elseif ($file->getname() == 'container') {
                                                        echo "<p>". ucfirst($file['type']).": ". $file;
                                                        $arr = explode(' ', ucfirst($file['type'])."-". $file);
                                                        $component = $component."-". $arr[0] ."</p>";
                                                    }
                                                }
                                                if (isset($c->physdesc->extent)) {
                                                    echo "<h4>Extent: ". $c->physdesc->extent."</h4>";
                                                }
                                                if (isset($c->note)) {
                                                    echo "<h4>". $c->note->p."</h4>";
                                                }
                                                if (isset($c->unittitle->persname)) {
                                                    echo "<br><h4>Person: ". $c->unittitle->persname."</h4>";
                                                }
                                                if (isset($c->unittitle->unitdate)) {
                                                    echo "<br><h4>Date: ". $c->unittitle->unitdate."</h4>";
                                                }
                                            } elseif ($c->getname() == 'scopecontent') {
                                                echo "<h4>Scope and Content</h4>";
                                                foreach ($fileObj->scopecontent->p as $p) {
                                                    echo "<p style='line-height: 1.6'>".  dom_import_simplexml($p)->textContent  ."</p>";
                                                }
                                            }
                                        }
                                        echo "</div>";
                                    }
                echo "</div>";
            }
        }
        echo "</div>";
    }
?>




      <div class="senylrc_top_container" style="margin-left:16%;">
         <div class="top_left">
                   <div id="logo">
               <a href="/" title="Home"><img src="https://empireadc.org/sites\empireadc.org\files/ead_logo.gif"/></a>
             </div>

           <h1 id="site-title">
             <a href="/" title="Home"></a>

           </h1>
         </div>

         <div class="top_right">
      <div id="site-description">Finding Aids at Your Fingertips</div>
           <nav id="main-menu"  role="navigation">
             <a class="nav-toggle" href="#">Menu</a>
             <div class="menu-navigation-container">
               <ul class="menu"><li class="first leaf"><a href="/empiresearch/browse" title="">Browse</a></li>
     <li class="leaf"><a href="/empiresearch/advsearch" title="">Search</a></li>
     <li class="leaf"><a href="/participate">Participate</a></li>
     <li class="last leaf"><a href="/about">About</a></li>
     </ul>        </div>

             <div class="clear"></div>
           </nav>
         </div>
      </div>
         <div class="clear"></div>





<div class="container-fluid text-center">
  <div class="row content">
    <div class="col-sm-2 sidenav">

    </div>
    <div class="col-sm-8 text-left">
    <div class="reptitle"><h1><span property="dcterms:title"><?php echo $title; ?></span></h1></div>
     <div id="tocResponsive"></div>
     <div id="eadInfo" style="margin-bottom: 30px;">

       <!--if($eadId == TRUE){ ?>
          <label>EAD Id:</label><p><span property="dcterms:identifier"><!--?php echo $eadId; ?></span></p>
       <--?php } */?> -->

       <label>Repository:</label><a class="searchTerm" style="font-style: italic" href="#"><p style="width: 230px;"><?php echo $repository; ?></a><br><?php echo $subarea; ?></p>
<?php
   if ($address == true) {
       foreach ($addressline as $a) {
           if (strpos($a, 'URI') === 0) {
               $a=substr($a, 4);
               if (!preg_match("~^(?:f|ht)tps?://~i", $a)) {
                   $a = "http://" .$a;
                   $a=substr($a, 7);
               }
               #              $a = str_replace( 'http:', 'http://', $a );
               #               $a = str_replace( 'http://', 'http://', $a );
               echo "<h5>URI: <a target='_blank' href='".$a."'>".$a."</a></h5>";
           } elseif (strpos($a, 'URL') === 0) {
               $a=substr($a, 4);
               $a = str_replace('http:', 'http://', $a);
               echo "<h5>URL: <a target='_blank' href='".$a."'>".$a."</a></h5>";
           } elseif (strpos($a, 'http') === 0) {
               echo "<h5>URL: <a target='_blank' href='".$a."'>".$a."</a></h5>";
           } else {
               echo "<h5>".$a."</h5>";
           }
       }
   }?>
      <br><label>Creator: </label>
      <!--?php foreach ($creatorList as $c){ ?>
            <p><span property="dcterms:creator"><a href="#" id="<?php echo $c[0][1]; ?>" class="controlledHeader"><?php echo $c[0][0]; ?></a></span></p>
      <!--?php } -->

      <?php for ($y = 0 ; $y < count($creatorList) ; $y++) {
       ?>
            <p><span property="dcterms:creator"><a href="#" id="<?php echo $creatorList[$y][1]; ?>" class="controlledHeader"><?php echo $creatorList[$y][0]; ?></a></span></p>
      <?php
   }

      if ($extent != 'Unspecified') {
          foreach ($extent as $y) {
              ?>
          <label>Size: </label><p><span property="dcterms:extent"><?php echo $y; ?></span></p>
        <?php
          }
      } ?>

        <label>Language: </label>

      <?php foreach ($languageList as $l) {
          ?>
         <p><?php echo $l; ?></p>
      <?php
      } ?>

      <?php if ($abstract != 'Unspecified') {
          ?>
        <label>Abstract:</label><p><span property="dcterms:abstract"><?php echo auto_link($abstract, 'both', true); ?></span></p>
      <?php
      } ?>

  </div>
  <a class="btn btn-primary openall" href="#">Expand All</a>&nbsp&nbsp&nbsp
  <a class="btn btn-danger closeall" href="#">Collapse All</a><br><br>


<h4 data-toggle="collapse" data-target="#descId" class='infoAccordion accordion'>Collection Details<span class="glyphicon glyphicon-menu-right" style="float:right;"></span></h4>
<div id="descId" class="collapse">
        <?php if ($processInfo != 'Unspecified') {
          ?>
          <label>Processing Information: </label><p><?php echo auto_link($processInfo, 'both', true); ?></p>
        <?php
      }

        if ($acqInfo != 'Unspecified') {
            ?>
          <label>Acquisition Information: </label><p><?php echo auto_link($acqInfo, 'both', true); ?></p>
        <?php
        }

        if ($location != 'Unspecified') {
            ?>
          <label>Location: </label><p><?php echo $location; ?></p>
        <?php
        }

        if ($histNote != 'Unspecified') {
            ?>
          <label>Historical Note: </label><p><?php echo auto_link($histNote, 'both', true); ?></p>
        <?php
        }

        if ($scopeContent != 'Unspecified') {
            ?>
          <label>Scope and Content: </label><p><?php echo auto_link($scopeContent, 'both', true); ?></p>
        <?php
        }

        if ($arrangement != 'Unspecified') {
            ?>
  <label>Arrangement: </label><p><?php echo auto_link($arrangement, 'both', true); ?></p>
        <?php
        }

        if ($seperateMaterial != 'Unspecified') {
            ?>
          <label>Separated Material: </label><p><?php echo auto_link($seperateMaterial, 'both', true); ?></p>
        <?php
        }

        if ($relatedMaterial == true) {
            ?>
          <label>Related Materials: </label><br/>
          <?php for ($i=0 ; $i < sizeof($relatedMaterialLink) ; $i ++) {
                if ($linksAvailable == true) {
                    ?>
            <a href='<?php $relatedMaterialLink[$i][1]; ?>' ><?php echo $relatedMaterialLink[$i][0]; ?></a></br>
          <?php
                } else {
                    ?>
                    <ul>
            <li type="circle"><?php echo $relatedMaterialLink[$i][0]; ?></li>
          </ul>
          <?php
                }
            }
            echo "</p>";
        }

        if ($bibliography != 'Unspecified') {
            ?>
            <label>Bibliography: </label><ul><?php echo auto_link($bibliography, 'both', true); ?></ul>
          <?php
        }

        if ($accruals != 'Unspecified') {
            ?>
          <label>Accruals and Additions: </label><p><?php echo auto_link($accruals, 'both', true); ?></p><br>
        <?php
        } ?>
</div>

<h4 data-toggle="collapse" data-target="#adminInfo" class='infoAccordion accordion'>Collection Access &amp; Use<span class="glyphicon glyphicon-menu-right" style="float:right;"></span></h4>
<div id="adminInfo" class="collapse">
        <?php
        if ($access != 'Unspecified') {
            ?>
          <label>Access: </label><p><?php echo auto_link($access, 'both', true); ?></p>
        <?php
        }
        if ($copyright != 'Unspecified') {
            ?>
          <label>Copyright: </label><p><?php echo auto_link($copyright, 'both', true); ?></p>
        <?php
        }
        if ($prefercite != 'Unspecified') {
            ?>
          <label>Preferred Citation: </label><p><?php echo auto_link($prefercite, 'both', true); ?></p>
        <?php
        } ?>
</div>

<h4 data-toggle="collapse" data-target="#controlHeadings" class='infoAccordion accordion'>Collection Subjects &amp; Formats<span class="glyphicon glyphicon-menu-right" style="float:right;"></span></h4>
<div id="controlHeadings" class="collapse">
<?php
$controlledAccess = (isset($xml->archdesc->controlaccess)? true : false);
if ($controlledAccess == true) {
    $persnameArray =array();
    $subjectArray=array();
    $corpnameArray=array();
    $genreformArray=array();
    $geognameArray=array();
    foreach ($xml->archdesc->controlaccess as $controlaccess) {
        $empirecsf = $controlaccess;

        if (isset($xml->archdesc->controlaccess->controlaccess)) {
            $empirecsf=$controlaccess->controlaccess;
            foreach ($xml->archdesc->controlaccess->controlaccess as $controlaccessnest) {
                foreach ($controlaccessnest->children() as $list) {
                    #store the values in an arrary to be called later when we dispaly those sections
                    $value=$list->getname();
                    if ($value=='persname') {
                        $persnameArray[] = $list;
                    } elseif ($value=='subject') {
                        $subjectArray[] = $list;
                    } elseif ($value=='corpname') {
                        $corpnameArray[] = $list;
                    } elseif ($value=='genreform') {
                        $genreformArray[] = $list;
                    } elseif ($value=='geogname') {
                        $geognameArray[] = $list;
                    }
                }
            }
        } else {
            foreach ($xml->archdesc->controlaccess->children() as $list) {
                #store the values in an arrary to be called later when we dispaly those sections
                $value=$list->getname();
                if ($value=='persname') {
                    $persnameArray[] = $list;
                } elseif ($value=='subject') {
                    $subjectArray[] = $list;
                } elseif ($value=='corpname') {
                    $corpnameArray[] = $list;
                } elseif ($value=='genreform') {
                    $genreformArray[] = $list;
                } elseif ($value=='geogname') {
                    $geognameArray[] = $list;
                }
            }
        }
    }
    #This is for development
    //var_dump($controlledAccessArray);
    #Dispaly the values for controllAccess
    if (empty(!$geognameArray)) {
        echo "<h5>Place:</h5>";
        foreach ($geognameArray as $value) {
            echo "<ul style='font-size:15px;'><li><a href='#' id='geogname_facet' class='controlledHeader' ><span property='dcterms:coverage'>". $value."</span></a></li></ul>";
        }
    }
    if (empty(!$subjectArray)) {
        echo "<h5>Subject:</h5>";
        foreach ($subjectArray as $value) {
            echo "<ul><li><a href='#' id='subject_facet' class='controlledHeader' <span property='dcterms:subject'>". $value."</span></a></li></ul>";
        }
    }
    if (empty(!$persnameArray)) {
        echo "<h5>Person:</h5>";
        foreach ($persnameArray as $value) {
            echo "<ul><li><a href='#' id='persname_facet' class='controlledHeader' <span property='dcterms:subject'>".$value."</span></a></li></ul>";
        }
    }
    if (empty(!$genreformArray)) {
        echo "<h5>Genre/Format:</h5>";
        foreach ($genreformArray as $value) {
            echo "<ul><li><a href='#' id='genreform_facet' class='controlledHeader' <span property='dcterms:subject'>".$value."</span></a></li></ul>";
        }
    }
    if (empty(!$corpnameArray)) {
        echo "<h5>Corporation:</h5>";
        foreach ($corpnameArray as $value) {
            echo "<ul><li><a href='#' id='corpname_facet' class='controlledHeader' <span property='dcterms:subject'>".$value."</span></a></li></ul>";
        }
    }
} else {
    echo"  <h4 style='font-style: italic'>Not available</h4>";
}
?>


      </div>
<?php if ($is_chron_available) {
    ?>
  <!--button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#chronology" style="font-size: 14px;">Chronology</button-->
  <h4 data-target="#chronology" data-toggle="modal" class='infoAccordion accordion'>Chronology</h4>
<?php
} ?>

<div id="chronology" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" style="text-align:center;">Chronology</h4>
      </div>
      <div class="modal-body">
      <?php if (isset($xml->archdesc->bioghist)) {
        $chronolist = 0;
        foreach ($xml->archdesc->bioghist->children() as $chron) {
            if ($chron ->getname() == 'chronlist') {
                if ($chronolist == 0) {
                    ?>
             <button class="accordion active" id='<?php echo $chron ->head; ?>'><?php echo $chron ->head; ?></button>
               <div class="panel" style="display: block" id="<?php echo $chron ->head ; ?>">

             <?php
                } else {
                    ?>
               <button class="accordion" id='<?php echo $chron ->head; ?>'><?php echo $chron ->head; ?></button>
                 <div class="panel" id="<?php echo $chron ->head ; ?>">

              <?php
                } ?>

             <ul class="tl" id="<?php echo $chron ->head ; ?>">
           <?php $i= 0;
                foreach ($xml->archdesc->bioghist->chronlist -> children() as $chronChild) {
                    if ($chronChild -> getname() =='chronitem') {
                        if ($i % 2 == 0) {
                            ?>

                   <li class='tl-inverted' id="<?php echo $chron ->head ; ?>">
                  <div class="tl-badge info"><?php echo $chronChild -> date ; ?>
                  </div><div class="tl-panel">
                  <div class="tl-body">
                               <?php if ($chronChild -> eventgrp) {
                                foreach ($chronChild-> eventgrp -> children()  as $chronEventChild) {
                                    ?>

                                  <p class="p-list"><?php echo $chronEventChild ; ?> </p>
                          <?php
                                } ?>
                     </div></div>

                   <?php
                            } else {
                                ?>

                    <p><?php echo $chronChild -> event ; ?></p>
                   <?php
                            } ?>
              </li>


           <?php
                        } else {
                            ?>

                   <li class='tl' id="<?php echo $chron ->head ; ?>"><div class="tl-badge info">
                 <?php echo $chronChild -> date  ; ?></div><div class="tl-panel">
               <div class="tl-body">
               <?php if ($chronChild -> eventgrp) {
                                foreach ($chronChild-> eventgrp -> children()  as $chronEvenChild) {
                                    ?>

                       <p class="p-list"><?php echo $chronEvenChild ; ?></p>
                    <?php
                                } ?>
                   </div></div>

               <?php
                            } else {
                                ?>

                   <p><?php echo $chronChild -> event ; ?></p>

                   <?php
                            } ?>
                   </li>


           <?php
                        }
                        $i++;
                    }
                } ?>
             </ul>
           </div>

            <?php  $chronolist++ ;
            }
        }
    }?>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>

  </div>
    </div>
  </div>

<div id="componentList">
<?php if ($componentList == true) {
        /* For cases where high level series list exists but a more detailed container list is available for download*/
        if ($otherfindaids != false) {
            ?>
    <h4 style="margin-left:17px;">Download Container List:</h4>
    <a href='<?php echo $downloadLink; ?>' itemprop="url" style="margin-left: 17px;"><img src='<?php echo $iconLink; ?>' class="doc-icon"></a></br></br>
  <?php
        }
        $component = 0;
        foreach ($xml->archdesc->dsc->c as $c) {
            $cAttr = $c->attributes();
            $cLevel = $cAttr["level"];
            if ($cLevel == 'file' || $cLevel == 'item' || $cLevel == 'otherlevel') {
                echo "<div class='fileRow'>";
                foreach ($c->did->children() as $child) {
                    if ($child->getname() == 'unittitle') {
                        if (isset($child->emph)) {
                            echo " <div class='fileTitle'><h4>". $child->emph ."</h4></div>";
                        } else {
                            echo " <div class='fileTitle'><h4>". $child ."</h4></div>";
                        }
                        if (count($child) > 0) {
                            if (isset($child->title->emph)) {
                                echo "<div class='fileTitle'><h4>". ucfirst($cLevel).": ";
                                $component = $child->title->emph;
                                echo $component;
                                $component = str_replace(" ", "", $component);
                                echo "</h4> </div>";
                            } else {
                                echo " <div class='fileTitle'><h4>". ucfirst($cLevel).": ";
                                $component = $child->title;
                                echo $component;
                                $component = str_replace(" ", "", $component);
                                echo "</h4></div>";
                            }
                            if (isset($child->unitdate)) {
                                echo "	<div class='fileDate'><p>". ucfirst($child->unitdate['type']).' Date: '.$child->unitdate ."</p></div>";
                            }
                        } else {
                            echo "<div class='fileTitle'><h4>". ucfirst($cLevel).": ";
                            $component =  $child;
                            echo $component;
                            $component = str_replace(" ", "", $component);
                            echo "</h4></div>";
                        }
                    } elseif ($child->getname() == 'unitdate') {
                        echo "	<div class='fileDate'><p>". ucfirst($child['type']).' Date: '.$child ."</p></div>";
                    } elseif ($child->getname() == 'container') {
                        echo "	<div class='fileContainer'><p>". ucfirst($child['type']).": ". $child;
                        $arr = explode(' ', ucfirst($child['type'])."-". $child);
                        $component = $component."-". $arr[0];
                        echo "</p></div>";
                    }
                }
                if (isset($c->scopecontent)) {
                    foreach ($c->scopecontent->children() as $ab) {
                        if ($ab->getname() == 'p') {
                            $scopeContent = $scopeContent  . $ab . "<br /><br />\n" ;
                        } elseif ($ab->getname() == 'list') {
                            foreach ($c->scopecontent->list->children() as $c) {
                                if ($c -> getname() == 'head') {
                                    $scopeContent = $scopeContent . "<h4>" . $c . "</h4>";
                                } else {
                                    $scopeContent = $scopeContent . dom_import_simplexml($c)->textContent  . "<br />";
                                }
                            }
                        }
                    }
                    echo "<div class='fileContainer'><p>". $scopeContent."</div>";
                }
                echo "</div>";
            } elseif ($cLevel == 'series' || $cLevel == 'collection' || $cLevel == 'recordgrp') {
                seriesLevel($cLevel, $c, $collId, $repository);
            }
        } /* for each */
    } elseif ($otherfindaids != false) {
        echo "<h4>Download Container List:</h4>";
        echo "<a href='". $downloadLink."' itemprop='url'><img src='". $iconLink."' class='doc-icon'></a>";
    } else {
        echo "	<h4 style='font-style: italic; margin-left: 17px;'>Container List Not Available</h4>";
    }
        echo "</div><!-- componentList -->";
    echo "<!-- Dynamic table of contents based on series and subseries -->";
       if ($GLOBALS['tree'] != ' ') {
           ?>
        <button id="tocbutton" type="button" class="btn btn-default" style="display: hidden;">Series in this Collection:</button>
	 <div id='toc' style='position:absolute; width: 370px; height: 290px; overflow-y: auto;'>
            <label>Series in this Collection:</label>
            <?php echo '<ul id="tree">' . $GLOBALS['tree'] . '</ul>'; ?>
          </div>
      <?php
       } ?>

    <h4><label>Output formats:</label></h4>
		    <a href='<?php echo $link_ext; ?>' target='_blank' style='text-decoration: none; color: #ffffff;'><button type="button" class="btn btn-custm" >XML</button></a>
        <!--disable for now -->
    <!--    <a href='<?php echo $rdf; ?>' target='_blank' style='text-decoration: none; color: #ffffff;'><button type="button" class="btn btn-custm" >RDF/XML</button> </a> -->
    </div>
     </br></br>

    <!--div id="cart" style="visibility:hidden;">
          <div align="right">
          </div>
              <table id="researchCart" class="researchCart">

      <thead>
      <tr>

          <th> <button class="btn" id="reserve"><a href="<?php echo base_url("?c=empiresearch&m=help");?>">Help</a></button><h4>Your Research Cart</h4></th>
          <th><button class="btn" id="reserve"><a href="<?php echo base_url("?c=empiresearch&m=reserve");?>">Reserve</a></button></th>
      </tr>
      <tr>
          <th>Item</th>
        <th>Remove</th>
      </tr>
      </thead>
      <tbody>
      <tr>
      </tr>
      </tbody>

    </table>x

    </div-->

	</div>
</div>

</br>
<!--footer class="container-fluid text-center">
  <p>Footer Text</p>
</footer-->
</body>
<script>
function expand() {
    $('.stuff').slideDown(400);
    $('.collapse').slideDown(400);
}

function collapse() {
    $('.stuff').slideUp(400);
    $('.collapse').slideUp(400);
}
	$('a.controlledHeader').click(function(){
      var selectedHeader = $(this).text();
      var selectedFacet = $(this).attr('id');
      var selectedHeader = selectedHeader.trim();
      var selectedHeader = selectedHeader.replace(/ /g,"%20");
      var selectedHeader = encodeURIComponent(selectedHeader);
      //if the controlledHeader includes ( ), search without a facet option.
      if(selectedHeader.indexOf('(') > 0){
        selectedHeader = selectedHeader.replace('(',"").replace(')',"");
        resultUrl = "<?php echo base_url("?key=")?>"+ selectedHeader+"&facet=NULL";
      }else{
        resultUrl = "<?php echo base_url("?key=")?>"+ selectedHeader+"&facet="+selectedFacet;
      }
        window.open(resultUrl,"_self");
    });

    $('a.searchTerm').click(function() {
      var repositoryName =  $(this).text();
        var repositoryName = repositoryName.trim();
        var repositoryName = repositoryName.replace(/ /g,"%20");
        var repositoryName = encodeURIComponent(repositoryName);
        resultUrl = "<?php echo base_url("?key=")?>"+ repositoryName +"&facet=corpname_facet";
        window.open(resultUrl);
    });
    var acc = document.getElementsByClassName("accordion");
    var i;

    for (i = 0; i < acc.length; i++)
    {
      acc[i].onclick = function()
      {
        this.classList.toggle("active");
        var panel = this.nextElementSibling;
        if (panel.style.display === "block") {
          panel.style.display = "none";

        } else {
          panel.style.display = "block";
        }
      }
    }

 $('h4.infoAccordion').click(function(){
  $(this).find('span').toggleClass('glyphicon-menu-right').toggleClass('glyphicon-menu-down');
 });
 $('.closeall').click(function () {
           $('.collapse').collapse('hide');
 });


   $('.openall').click(function () {
         $('.collapse').collapse('show');
   });
 $('button#tocbutton').toggle(function(){
    $('#tocResponsive').html('<label>Series in this Collection: </label><?php echo '<ul id="tree">' . $GLOBALS['tree'] . '</ul>'; ?>');
 });

</script>
</html>

