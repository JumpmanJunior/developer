<!DOCTYPE html>
<html dir="ltr" lang="<?php echo $user_language;?>">
<head>
    <?php if(isset($header)) echo $header; ?>
    <title><?php if(isset($ewCfg['title'])) echo $ewCfg['title']; ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index,follow,noodp,noydir" />
    <meta name="description" content="">
    <meta name="author" content="2012 - <?php echo date('Y'); ?> <?php if(isset($ewCfg['title'])) echo $ewCfg['title']; ?>">
    <link href="//netdna.bootstrapcdn.com/bootstrap/2.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
    <link href="css/default/easy-wi.css" rel="stylesheet">

    <link rel="shortcut icon" href="images/favicon.ico" />

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" ></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/2.3.2/js/bootstrap.min.js"></script>
    <script src="js/default/footable.js" type="text/javascript"></script>
    <script type="text/javascript">$(function() { $('table').footable();});</script>
    <script src="js/default/main.js" type="text/javascript"></script>
    <script type="text/javascript">window.onDomReady(onReady); function onReady() { SwitchShowHideRows('init_ready');}</script>
</head>
<body>
<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner text-center">
        <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <div class="nav-collapse collapse">
            <p class="navbar-text pull-left">
                &nbsp;&nbsp;
            	<?php foreach ($languages as $language){ echo '<a href="userpanel.php?l='.$language.'"><img src="images/flags/'.$language.'.png" alt="Flag: '.$language.'.png."></a> ';} ?>
                &nbsp;&nbsp;
        	</p>

            <div class="nav navbar-text pull-left">
                <?php if($pa['tickets'] and $crashedArray['ticketsOpen']>0) { ?><a href="userpanel.php?w=ti"><span class="badge badge-info"><?php echo $crashedArray['tickets'].'/'.$crashedArray['ticketsOpen'].' '.$sprache_bad->tickets; ?></span></a><?php }?>
                <?php if($gscount>0 and $pa['gserver']) { ?>
                <?php if($crashedArray['gsCrashed']>0) { ?><a href="userpanel.php?w=gs&amp;d=md"><span class="badge badge-important"><?php echo $crashedArray['gsCrashed'].' '.$sprache_bad->gserver_crashed; ?></span></a><?php }?>
                <?php if($crashedArray['gsPWD']>0) { ?><a href="userpanel.php?w=gs&amp;d=md"><span class="badge badge-important"><?php echo $crashedArray['gsPWD'].' '.$sprache_bad->gserver_removed; ?></span></a><?php }?>
                <?php if($crashedArray['gsTag']>0) { ?><a href="userpanel.php?w=gs&amp;d=md"><span class="badge badge-important"><?php echo $crashedArray['gsTag'].' '.$sprache_bad->gserver_tag_removed; ?></span></a><?php }?>
                <?php }?>
                <?php if($voicecount>0 and $pa['voiceserver'] and $crashedArray['ts3']>0) { ?><a href="userpanel.php?w=vo&amp;d=md"><span class="badge badge-important"><?php echo $crashedArray['ts3'].' '.$sprache_bad->voice_crashed; ?></a><?php }?>
            </div>

        	<span class="navbar-text">Easy-WI.com</span>

            <a href="login.php?w=lo" class="navbar-text pull-right navbar-logout">
                <button class="btn btn-mini btn-danger"><i class="fa fa-sign-out"></i> Logout</button>
            </a>

            <ul class="nav pull-right">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user fa-fw"></i> <?php echo $great_user;?> <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="#"><?php echo $gsprache->last.'<br />'.$great_last;?></a></li>
                        <li class="divider"></li>
                        <?php if ($support_phonenumber!="") echo '<li><a href="#"><i class="fa fa-phone fa-fw"></i> '.$gsprache->hotline.": ".$support_phonenumber.'</a></li><li class="divider"></li>';?>
                        <?php if($pa['usersettings'] and !isset($_SESSION['sID'])) { ?>
                        <li><a href="userpanel.php?w=se&amp;d=pw"><i class="fa fa-key fa-fw"></i> <?php echo $gsprache->password." ".$gsprache->change;?></a></li>
                        <li><a href="userpanel.php?w=se"><i class="fa fa-cog fa-fw"></i> <?php echo $gsprache->settings;?></a></li>
                        <li class="divider"></li>
                        <?php } ?>
                        <li><a href="https://easy-wi.com" target="_blank"><i class="fa fa-info-circle fa-fw"></i> About</a></li>
                        <li><a href="https://twitter.com/EasyWI" target="_blank"><i class="fa fa-twitter fa-fw"></i> Easy-WI @ Twitter</a></li>
                        <li><a href="https://easy-wi.com/forum/" target="_blank"><i class="fa fa-comments fa-fw"></i> Forum</a></li>
                        <li><a href="http://wiki.easy-wi.com" target="_blank"><i class="fa fa-question-circle fa-fw"></i> Wiki</a></li>
                        <li><a href="https://github.com/ValveSoftware/steam-for-linux/issues" target="_blank"><i class="fa fa-bug fa-fw"></i> Steam Bugtracker</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
<div class="container-fluid" id="content">
    <div class="row-fluid">
        <div class="span3">
            <div class="well sidebar-nav">
                <div class="accordion" id="accordionMenu">
                    <div class="accordion-group">
                        <div class="accordion-heading">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordionMenu" href="#collapseOne"><i class="fa fa-home fa-fw"></i> Home</a>
                        </div>
                        <div id="collapseOne" class="accordion-body collapse <?php if(in_array($w,array('da','se','lo','ip','ho','su'))) echo 'in';?>">
                            <div class="accordion-inner">
                                <ul class="nav nav-pills nav-stacked">
                                    <li <?php if($w=='da' or $w=='ho') echo 'class="active"';?>><a href="userpanel.php?w=da">Dashboard</a></li>
                                    <?php if(!isset($_SESSION['sID'])){ ?><li <?php if($w=='su') echo 'class="active"';?>><a href="userpanel.php?w=su"><?php echo $gsprache->substitutes;?></a></li><?php }?>
                                    <li <?php if($w=='lo') echo 'class="active"';?>><a href="userpanel.php?w=lo"><?php echo $gsprache->logs;?></a></li>
                                    <?php if($easywiModules['ip']){ ?><li <?php if($w=='ip') echo 'class="active"';?>><a href="userpanel.php?w=ip"><?php echo $gsprache->imprint;?></a></li><?php }?>
                                    <?php foreach ($customModules['us'] as $k => $v) { echo '<li '; echo ($ui->smallletters('w',255,'get')==$k) ? 'class="active"' : ''; echo '><a href="admin.php?w='.$k.'">'.$v.'</a></li>'; }; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php if($easywiModules['ti'] and $pa['usertickets']) { ?>
                    <div class="accordion-group">
                        <div class="accordion-heading">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordionMenu" href="#collapseTwo"><i class="fa fa-h-square fa-fw"></i> <?php echo $gsprache->support;?></a>
                        </div>
                        <div id="collapseTwo" class="accordion-body collapse <?php if($w=='ti') echo 'in';?>">
                            <div class="accordion-inner">
                                <ul class="nav nav-pills nav-stacked">
                                    <li <?php if($w=='ti' and $d!='ad') echo 'class="active"';?>><a href="userpanel.php?w=ti"><?php echo $gsprache->overview;?></a></li>
                                    <li <?php if($w=='ti' and $d=='ad') echo 'class="active"';?>><a href="userpanel.php?w=ti&amp;d=ad"><?php echo $gsprache->support2;?></a></li>
                                    <?php foreach ($customModules['ti'] as $k => $v) { echo '<li '; echo ($ui->smallletters('w',255,'get')==$k) ? 'class="active"' : ''; echo '><a href="admin.php?w='.$k.'">'.$v.'</a></li>'; }; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if($easywiModules['gs'] and $gscount>0 and $pa['restart']) { ?>
                    <div class="accordion-group">
                        <div class="accordion-heading">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordionMenu" href="#collapseThree"><i class="fa fa-gamepad fa-fw"></i> <?php echo $gsprache->gameserver;?></a>
                        </div>
                        <div id="collapseThree" class="accordion-body collapse <?php if(in_array($w,array('gs','fd','ao','ca','bu','ms'))) echo 'in';?>">
                            <div class="accordion-inner">
                                <ul class="nav nav-pills nav-stacked">
                                    <?php if($pa['restart']) { ?>
                                    <li <?php if(in_array($w,array('gs','ao','ca','bu'))) echo 'class="active"';?>><a href="userpanel.php?w=gs"><?php echo $gsprache->overview;?></a></li>
                                    <?php } ?>
                                    <?php if($pa['fastdl']) { ?>
                                    <li <?php if($w=='fd') echo 'class="active"';?>><a href="userpanel.php?w=fd"><?php echo $gsprache->fastdownload;?></a></li>
                                    <?php } ?>
                                    <?php if($pa['restart']) { ?>
                                    <li <?php if($w=='ms') echo 'class="active"';?>><a href="userpanel.php?w=ms"><?php echo $gsprache->migration;?></a></li>
                                    <?php } ?>
                                    <?php foreach ($customModules['gs'] as $k => $v) { echo '<li '; echo ($ui->smallletters('w',255,'get')==$k) ? 'class="active"' : ''; echo '><a href="admin.php?w='.$k.'">'.$v.'</a></li>'; }; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if($easywiModules['vo'] and ($voicecount>0 or $tsdnscount>0) and $pa['voiceserver']) { ?>
                    <div class="accordion-group">
                        <div class="accordion-heading">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordionMenu" href="#collapseFour"><i class="fa fa-microphone fa-fw"></i> <?php echo $gsprache->voiceserver;?></a>
                        </div>
                        <div id="collapseFour" class="accordion-body collapse <?php if(in_array($w,array('vo','vd'))) echo 'in';?>">
                            <div class="accordion-inner">
                                <ul class="nav nav-pills nav-stacked">
                                    <?php if($voicecount>0) { ?><li <?php if($w=='vo') echo 'class="active"';?>><a href="userpanel.php?w=vo"><?php echo $gsprache->overview;?></a></li><?php } ?>
                                    <?php if($tsdnscount>0) { ?><li <?php if($w=='vd') echo 'class="active"';?>><a href="userpanel.php?w=vd">TS3 DNS</a></li><?php } ?>
                                    <?php foreach ($customModules['vo'] as $k => $v) { echo '<li '; echo ($ui->smallletters('w',255,'get')==$k) ? 'class="active"' : ''; echo '><a href="admin.php?w='.$k.'">'.$v.'</a></li>'; }; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if($easywiModules['my'] and $dbcount>0 and ($pa['mysql'] or $pa['mysql'])) { ?>
                    <div class="accordion-group">
                        <div class="accordion-heading">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordionMenu" href="#collapseFive"><i class="fa fa-sitemap fa-fw"> </i> MySQL</a>
                        </div>
                        <div id="collapseFive" class="accordion-body collapse <?php if($w=='my') echo 'in';?>">
                            <div class="accordion-inner">
                                <ul class="nav nav-pills nav-stacked">
                                    <li <?php if($w=='my') echo 'class="active"';?>><a href="userpanel.php?w=my"><?php echo $gsprache->overview;?></a></li>
                                    <?php foreach ($customModules['my'] as $k => $v) { echo '<li '; echo ($ui->smallletters('w',255,'get')==$k) ? 'class="active"' : ''; echo '><a href="admin.php?w='.$k.'">'.$v.'</a></li>'; }; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if($easywiModules['ro'] and ($virtualcount+$rootcount)>0 and $pa['roots']) { ?>
                    <div class="accordion-group">
                        <div class="accordion-heading">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordionMenu" href="#collapseSix"><i class="fa fa-laptop fa-fw"></i> Rootserver</a>
                        </div>
                        <div id="collapseSix" class="accordion-body collapse <?php if(in_array($w,array('de','vm'))) echo 'in';?>">
                            <div class="accordion-inner">
                                <ul class="nav nav-pills nav-stacked">
                                    <?php if($rootcount>0) { ?><li <?php if($w=='de') echo 'class="active"';?>><a href="userpanel.php?w=de"><?php echo $gsprache->dedicated;?></a></li><?php } ?>
                                    <?php if($virtualcount>0) { ?><li <?php if($w=='vm') echo 'class="active"';?>><a href="userpanel.php?w=vm"><?php echo $gsprache->virtual;?></a></li><?php } ?>
                                    <?php foreach ($customModules['ro'] as $k => $v) { echo '<li '; echo ($ui->smallletters('w',255,'get')==$k) ? 'class="active"' : ''; echo '><a href="admin.php?w='.$k.'">'.$v.'</a></li>'; }; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if(count($customModules['mo'])>0) { ?>
                    <div class="accordion-group">
                        <div class="accordion-heading">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordionMenu" href="#collapseFourteen"><i class="fa fa-tasks fa-fw"></i> <?php echo $gsprache->modules;?></a>
                        </div>
                        <div id="collapseFourteen" class="accordion-body collapse <?php if(isset($customModules['mo'][$ui->smallletters('w',255,'get')])) echo 'in';?>">
                            <div class="accordion-inner">
                                <ul class="nav nav-pills nav-stacked">
                                    <?php foreach ($customModules['mo'] as $k => $v) { echo '<li '; echo ($ui->smallletters('w',255,'get')==$k) ? 'class="active"' : ''; echo '><a href="admin.php?w='.$k.'">'.$v.'</a></li>'; }; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div><!--/.well -->
        </div><!--/span-->
        <div class="span9">
            <?php if(isset($header)){ ?>
            <div class="alert alert-block">
                <button type="button" class="close" data-dismiss="alert">&times;</button><?php echo $text; ?>
            </div>
            <?php } ?>