<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title></title>

    <link href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.3.0/css/material-fullpalette.min.css" rel="stylesheet" type="text/css" />
    <link href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.3.0/css/ripples.min.css" rel="stylesheet" type="text/css" />
    <link href="//cdn.datatables.net/1.10.6/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
    <style>
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: none !important;
            border: 1px solid transparent !important;
        }
        .progress {
            height: 20px;
        }
    </style>
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
    <div style="width: 100%; overflow: auto; padding: 12px 40px;">
        <div class="progress" style="display: none">
            <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                0%
            </div>
        </div>
        <h2 style="float:left"><?php echo $clan->getDBName() ?> - <small><?php echo $viewing ?></small></h2>
        <a href="#" class="btn btn-danger" style="float:right" data-toggle="modal" data-target="#selectScan">Choose a Scan</a>
        <a href="#" class="btn btn-success" style="float:right" data-toggle="modal" data-target="#selectDiff">Difference between two scans</a>
        <div style="width: 100%; max-height: calc(100vh - 210px); overflow: auto">
            <table class="table table-striped table-hover" id="clanTable">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>User</th>
                        <th>XP</th>
                        <th>Level</th>
                        <th>Gold</th>
                        <th>Wood</th>
                        <th>Ore</th>
                        <th>Mercury</th>
                        <th>Sulfur</th>
                        <th>Crystals</th>
                        <th>Gems</th>
                        <th>Wealth</th>
                        <th>Roulette Winnings</th>
                        <th>Battles Won</th>
                        <th>Battles Lost</th>
                        <th>Total Battles</th>
                        <th>Tavern Lost</th>
                        <th>Tavern Won</th>
                        <th>Knight</th>
                        <th>Necromancer</th>
                        <th>Wizard</th>
                        <th>Elf</th>
                        <th>Barbarian</th>
                        <th>Dark Elf</th>
                        <th>Demon</th>
                        <th>Dwarf</th>
                        <th>Tribal</th>
                        <th>Total FSP</th>
                        <th>Hunters' Guild</th>
                        <th>Laborers' Guild</th>
                        <th>Gamblers' Guild</th>
                        <th>Theives' Guild</th>
                        <th>Rangers' Guild</th>
                        <th>Mercenaries' Guild</th>
                        <th>Commanders' Guild</th>
                        <th>Smiths' Guild</th>
                        <th>Enchanters' Guild</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    foreach ($scans as $scan) {
                    ?>
                    <tr>
                        <?php
                        $i++;
                        echo "<td>$i</td>";
                        foreach ($scan as $value) {
                        ?>
                        <?php
                            if (is_numeric($value)) {
                                echo "<td style='text-align: right'>".number_format(floatval($value))."</td>";
                            } else {
                                echo "<td>".$value."</td>";
                            }
                            ?>
                        <?php
                        }
                        ?>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal fade" id="selectScan">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">Choose which Scan you want to view</h4>
                </div>
                <div class="modal-body">
                    <select class="form-control">
                        <?php
                        $scans = $clan->getAllScans();
                        foreach ($scans as $scan) {
                            $id = $scan["id"];
                            $date = $scan["date"];
                            echo "<option value='$id'>$date</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="choose">Choose</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="selectDiff">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">Choose which Scans you want to compare</h4>
                </div>
                <div class="modal-body">
                    <h5>First Date</h5>
                    <select class="form-control">
                        <?php
                        $scans = $clan->getAllScans();
                        foreach ($scans as $scan) {
                            $id = $scan["id"];
                            $date = $scan["date"];
                            echo "<option value='$id'>$date</option>";
                        }
                        ?>
                    </select>
                    <h5>Second Date</h5>
                    <select class="form-control">
                        <?php
                        $scans = $clan->getAllScans();
                        foreach ($scans as $scan) {
                            $id = $scan["id"];
                            $date = $scan["date"];
                            echo "<option value='$id'>$date</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="compare">Compare</button>
                </div>
            </div>
        </div>
    </div>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js" type="text/javascript" language="javascript"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.4/js/bootstrap.min.js" type="text/javascript" language="javascript"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.3.0/js/material.min.js" type="text/javascript" language="javascript"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.3.0/js/ripples.min.js" type="text/javascript" language="javascript"></script>
    <script src="//cdn.datatables.net/1.10.6/js/jquery.dataTables.min.js" type="text/javascript" language="javascript"></script>
    <script src="//cdn.datatables.net/plug-ins/1.10.6/integration/bootstrap/3/dataTables.bootstrap.js" type="text/javascript" language="javascript"></script>
    <script>
        var CLAN_ID = <?php echo $clan->getID() ?>;
        $.material.init();
        $('#clanTable').DataTable();
        $('#compare').click(function() {
            var modal = $(this).closest('.modal'),
                select1 = modal.find('select:eq(0)').val(),
                select2 = modal.find('select:eq(1)').val();

            window.location = '/clan/' + CLAN_ID + '/compare/' + select1 + '/' + select2;
        });
        $('#choose').click(function() {
            var modal = $(this).closest('.modal'),
                select1 = modal.find('select:eq(0)').val();

            window.location = '/clan/' + CLAN_ID + '/view/' + select1;
        });

        function checkScan() {
            $.ajax('/rest/crawl/7705/status', {
                method: 'GET',
                success: function(data) {
                    if (data === '0') {
                        $('.progress-bar').text('Complete');
                    } else {
                        data = JSON.parse(data);
                        percent = Math.round((parseInt(data[0]) / parseInt(data[1])) * 100);
                        $('.progress').css('display', 'block');
                        $('.progress-bar').text('Scan in progress: ' + percent + '%').css('width', percent + '%');
                    }
                }
            })
        }

        checkScan();
        setInterval(checkScan, 1000);
    </script>
</body>
</html>