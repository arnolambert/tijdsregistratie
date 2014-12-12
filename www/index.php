<html>
<head>
<style>
body {
    background-color: linen;
}

    .flat-table {
		margin-bottom: 5px;
		border-collapse:collapse;
		font-family: Arial, sans-serif;
		border: none;
                border-radius: 3px;
               -webkit-border-radius: 3px;
               -moz-border-radius: 3px;
	}
	.flat-table th, .flat-table td {
		box-shadow: inset 0 -1px rgba(0,0,0,0.25),
			inset 0 1px rgba(0,0,0,0.25);
	}
	.flat-table th {
		font-weight: bold;
		-webkit-font-smoothing: antialiased;
        padding: 0.7em 1em 0.7em 1.15em;
		color: rgba(0,0,0,0.45);
		text-shadow: 0 0 1px rgba(0,0,0,0.1);
		font-size: 1.5em;
	}
	.flat-table td {
		color: #f7f7f7;
		padding: 0.7em 1em 0.7em 1.15em;
		text-shadow: 0 0 1px rgba(255,255,255,0.1);
		font-size: 1.4em;
	}
	.flat-table tr {
		-webkit-transition: background 0.3s, box-shadow 0.3s;
		-moz-transition: background 0.3s, box-shadow 0.3s;
		transition: background 0.3s, box-shadow 0.3s;
	}
	.flat-table-1 {
		background: #336ca6;
	}
	.flat-table-1 tr:hover {
		background: rgba(0,0,0,0.19);
	}
	.flat-table-2 tr:hover {
		background: rgba(0,0,0,0.1);
    }

    .flat-table-2 {
		background: #f06060;
    }

	.flat-table-3 {
		background: #52be7f;
    }

	.flat-table-3 tr:hover {
		background: rgba(0,0,0,0.1);
	}
a {
    background-color: #8ac007;
    border: 1px solid #8ac007;
    border-radius: 5px;
    color: #ffffff;
    display: inline-block;
    font-size: 12px;
    font-weight: bold;
    margin-bottom: 5px;
    margin-left: 0;
    margin-top: 0;
    padding: 3px 10px 4px;
    text-align: center;
    text-decoration: none;
    white-space: nowrap;
    white-space: nowrap;
}
</style>
</head>
<body>
<a href="<?php echo $_SERVER["REQUEST_URI"];?>">Refresh</a>
<?php
/*
 * check if this is a reload or a new page
 */
if (isset($_REQUEST['refresh'])) {
    $refresh = false;
    $begin = $_REQUEST['refresh'];
} else {
    $refresh = true;
    $begin = time();
}

$dateString = date('d-m-Y');

error_reporting(-1);
include './_pdo.php';

$dbFile = '../db/sqliteTijdsregistratie.db';
if (!is_file($dbFile)) {
    $newDb = true;
} else {
    $newDb = false;
}
PDO_Connect("sqlite:$dbFile");

$queries = array();
if ($newDb) {
    $query = 'CREATE TABLE tijdsregistratie (
        id integer PRIMARY KEY,
        datum varchar(50),
        begin varchar(50),
        einde varchar(50)
    )';
    $stmt = PDO_Execute($query);
}

if ($refresh) {
    $query = 'INSERT INTO tijdsregistratie (datum, begin, einde) VALUES
        ("' . $dateString . '",
        "' . $begin . '",
        "' . $begin . '")';
} else {
    $query = 'UPDATE tijdsregistratie
        SET einde = "' . time() . '"
        WHERE datum = "' . $dateString . '"
            AND begin = "' . $begin . '"';
}

$stmt = PDO_Execute($query);

$data = PDO_FetchAll('SELECT * FROM tijdsregistratie ORDER BY id ASC');

$result = array();
foreach ($data as $timeReg) {
    if (!isset($result[$timeReg['datum']])) {
        $result[$timeReg['datum']]['tijd'] = 0;
        $result[$timeReg['datum']]['week'] = date('W', $timeReg['begin']);
    }
    $result[$timeReg['datum']]['tijd'] += ($timeReg['einde'] - $timeReg['begin']);
}

print '<table
        class="flat-table flat-table-3" >
        <thead>
            <th>week</th>
            <th>datum</th>
            <th>tijd (uur)</th>
            <th>tijd (sec)</th>
	</thead><tbody> ';

$weekTotaal = 0;
$prevWeek = '';
foreach ($result as $datum => $tijdWeek) {
    $tijd = $tijdWeek['tijd'];
    $week = $tijdWeek['week'];
    $hours = floor($tijd / 3600);
    $minutes = floor(($tijd / 60) % 60);
    $seconds = $tijd % 60;

    $tijdUur = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);

    if ($prevWeek !== ''
        && $prevWeek !== $week
    ) {
        $weekHours = floor($weekTotaal / 3600);
        $weekMinutes = floor(($weekTotaal / 60) % 60);
        $weekSeconds = $weekTotaal % 60;

        $weekTotaal = sprintf("%02d:%02d:%02d", $weekHours, $weekMinutes, $weekSeconds);
        print "
        <tr class=\"flat-table flat-table-2\">
            <td colspan=2>totaal week $prevWeek</td>
            <td colspan=2>$weekTotaal</td>
        </tr>";
        $weekTotaal = 0;
    } else {
        $weekTotaal += $tijd;
    }
    print "
        <tr>
            <td>$week</td>
            <td>$datum</td>
            <td>$tijdUur</td>
            <td>$tijd</td>
        </tr>";
    $prevWeek = $week;
}

//last week
$weekHours = floor($weekTotaal / 3600);
$weekMinutes = floor(($weekTotaal / 60) % 60);
$weekSeconds = $weekTotaal % 60;

$weekTotaal = sprintf("%02d:%02d:%02d", $weekHours, $weekMinutes, $weekSeconds);
print "
        <tr class=\"flat-table flat-table-2\">
            <td colspan=2>totaal week $week</td>
            <td colspan=2>$weekTotaal</td>
        </tr>";

print '<tbody></table>';
/*
print('<pre>');
print_r($data);
print('</pre>');
 */

?>
<script type="text/javascript">
setTimeout (function()
    {
        window.location.href = 'index.php?refresh=<?php print $begin; ?>';
    },
    5000
);
</script>
</body>
</html>

