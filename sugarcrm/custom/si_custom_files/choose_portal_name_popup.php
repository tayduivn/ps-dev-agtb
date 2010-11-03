<?php
chdir('../..');
define('sugarEntry', true);
require_once('include/entryPoint.php');

/* Stolen from CleanUpLead.php */
function validEmailDomainMX($email_address)
{
    if (strlen($email_address) < 7) {
        return false;
    }
    $at_pos = strpos($email_address, '@');
    if (!$at_pos) {
        return false;
    }
    list($user, $domain) = split('@', $email_address, 2);
    if (strpos($domain, '.') === false) {
        return false;
    }
    if (('example.com' == $domain) || (checkdnsrr($domain, 'MX'))) {
        return true;
    } else {
        return false;
    }
}


//$check_email = '';
/*
 * It was requested that we search for partial emails, too, so skip the nice
 * test of the email address. :/
 */
/*
if (!empty($_REQUEST['check_email']) && validEmailDomainMX(trim($_REQUEST['check_email']))) {
    $check_email = trim($_REQUEST['check_email']);
}
*/
$check_email = trim($_REQUEST['check_email']);
$check_email_domain = trim($_REQUEST['check_email_domain']);
$check_name = trim($_REQUEST['check_name']);
$data = array();

if (!empty($check_email) || !empty($check_email_domain) || !empty($check_name)) {
    $mambo_db = mysql_connect('online-comdb2', 'sugarcrm_com', '08yag81g9Ag91');
    if ($mambo_db) {

        $query = array();
        if (!empty($check_email)) {
            $query_email = mysql_real_escape_string($check_email, $mambo_db);
            $_query[] = "`drupal_users`.`mail` LIKE '" . $query_email . "%'";
        }

        if (!empty($check_email_domain)) {
            $query_domain = mysql_real_escape_string($check_email_domain, $mambo_db);
            $_query[] = "`drupal_users`.`mail` LIKE '%" . $query_domain . "%'";
        }

        if (!empty($check_name)) {
            $query_name = mysql_real_escape_string($check_name, $mambo_db);
            $_query[] = "`drupal_profile_values`.`value` LIKE '%" . $query_name . "%'";
        }

        $_query[] = "`drupal_users`.`name` IS NOT NULL
                        AND `drupal_users`.`name` <> ''";

        $_query = join(' AND ', $_query);

        $query = "
SELECT
    `drupal_users`.`name` AS `username`,
    `drupal_profile_values`.`value` AS `name`,
    `drupal_users`.`mail` AS `email`,
    `drupal_users`.`status`
FROM
    `drupal_users`
LEFT JOIN
    `drupal_profile_values`
    ON `drupal_users`.`uid` = `drupal_profile_values`.`uid`
    AND `drupal_profile_values`.`fid` = 1
WHERE
    {$_query}
GROUP BY
    `drupal_users`.`uid`
ORDER BY
    `drupal_users`.`status` DESC
";
        mysql_select_db('sugarcrm_com', $mambo_db);
        $r = mysql_query($query, $mambo_db);
        while ($row = mysql_fetch_assoc($r)) {
            $data[] = $row;
        }
    }
}

$contact_id = "";
if (isset($_REQUEST['contact_id']) && !empty($_REQUEST['contact_id'])) {
    $contact_id = trim($_REQUEST['contact_id']);
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <title>Sugar Internal - Search for SugarCRM.com user</title>
    <link rel="SHORTCUT ICON" href="include/images/sugar_icon.ico"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="themes/Sugar/navigation.css"/>
    <link rel="stylesheet" type="text/css" href="themes/Sugar/style.css"/>
    <link rel="stylesheet" type="text/css" href="themes/Sugar/colors.red.css"/>
    <link rel="stylesheet" type="text/css" href="themes/Sugar/fonts.normal.css"/>
    <script type="text/javascript">
        function chooseValue(value, id)
        {
            var portal_name;
            if (value
                    && window.opener
                    && !window.opener.closed) {
                if (window.opener.document.getElementById('portal_name') != null) {
                    portal_name = window.opener.document.getElementById('portal_name');
                }
                else {
                    if (id) {
                        portal_name = window.opener.document.getElementById('portal_name_' + id);
                    }
                }
                portal_name.value = value;
                window.close();
            }
        }
    </script>
    <style type="text/css">
        table {
            border: 1px solid black;
            border-collapse: collapse;
        }

        tr {
            margin: 0;
            padding: 0;
        }

        th, td {
            margin: 0;
            padding: 0.25em;
        }

        .even {
            background-color: #ddd;
        }

        .even td {
            border-top: 1px solid black;
            border-bottom: 1px solid black;
        }
    </style>
</head>
<body>
<form method="post" action="">
    <p>
        <label>Email: <input type="text" name="check_email" value="<?php echo $check_email; ?>"/></label><br/>
        <label>Email Domain: <input type="text" name="check_email_domain"
                                    value="<?php echo $check_email_domain; ?>"/></label><br/>
        <label>Name: <input type="text" name="check_name" value="<?php echo $check_name; ?>"/></label><br/>
        <input type="submit" name="do" value="Search for SugarCRM.com user"/>
    </p>
</form>
<?php

if ($check_email && !$data) {
    echo "<p>No results found for $check_email</p>";
}
$labels = array('name' => 'Name',
    'username' => 'Username',
    'email' => 'Email Address',
    'status' => 'Active / Inactive',
    'lastVisitDate' => 'Last Visit Date');
if ($data) {
    echo '<table>';
    echo '<tr>';
    foreach (array_keys($data[0]) as $key) {
        echo '<th>';
        echo $labels[$key];
        echo '</th>';
    }
    echo '</tr>';
    $count = 0;
    foreach ($data as $row) {
        echo "\n";
        if (0 == $count++ % 2) {
            echo '<tr class="even">';
        } else {
            echo '<tr>';
        }
        echo '<td>';
        echo '<a href="#" onclick="chooseValue(\'';
        echo $row['username'];
        if ($contact_id) {
            echo '\',\'' . $contact_id . '\'); return false;">';
        }
        else {
            echo '\'); return false;">';
        }
        echo $row['username'];
        echo '</a>';
        echo '</td>';
        echo "\n";
        echo '<td>';
        echo $row['name'];
        echo '</td>';
        echo "\n";
        echo '<td>';
        echo $row['email'];
        echo '</td>';
        echo "\n";
        echo '<td>';
        echo ($row['status'] ? 'Active' : 'Inactive');
        echo '</td>';
        echo "\n";
        if (false) {
            echo '<td>';
            if ('0000-00-00 00:00:00' == $row['lastVisitDate']) {
                $date = 'Never';
            } else {
                $date = strtotime($row['lastVisitDate']);
                $date = date('Y-m-d H:i', $date);
            }
            echo $date;
            echo '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
}
?>
</body>
</html>
