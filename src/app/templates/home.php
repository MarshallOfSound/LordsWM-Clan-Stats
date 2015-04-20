<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title></title>

    <link href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.3.0/css/material-fullpalette.min.css" rel="stylesheet" type="text/css" />
    <link href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.3.0/css/ripples.min.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <div class="navbar navbar-info">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-responsive-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/">Clan Stats</a>
        </div>
        <div class="navbar-collapse collapse navbar-responsive-collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="/">Clans</a></li>
            </ul>
        </div>
    </div>
    <div class="container">
        <div class="jumbotron">
            <h1>What is this?</h1>
            <p>This is a tool that allows clan leaders to monitor their members progress through the game.  This helps them run competition or kick out inactive members</p>
            <p><a class="btn btn-primary btn-lg">Get started</a></p>
        </div>
        <h2>Clans Being Scanned</h2>
        <table class="table">
            <tr>
                <th>Clan ID</th>
                <th>Clan Name</th>
                <th>View</th>
            </tr>
            <?php
            $clans = (new \LWM\Stats())->getClans();
            foreach ($clans as $clan) {
            ?>
            <tr>
                <td><?php echo $clan["lwm_id"]; ?></td>
                <td><span style="font-family: Arial, sans-serif"><?php echo $clan["name"]; ?></span></td>
                <td width="60px"><a href="/clan/<?php echo $clan["id"]; ?>" class="btn btn-primary">View</a></td>
            </tr>
            <?php
            }
            ?>
        </table>
    </div>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js" type="text/javascript" language="javascript"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.3.0/js/material.min.js" type="text/javascript" language="javascript"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.3.0/js/ripples.min.js" type="text/javascript" language="javascript"></script>
    <script>
        $.material.init()
    </script>
</body>
</html>