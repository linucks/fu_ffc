<?php
// jmht need as running from command-line with no timezone setup
date_default_timezone_set('Europe/London');
require __DIR__ . '/vendor/autoload.php';

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('Google Sheets API PHP Quickstart');
    $client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('offline');

    // Load previously authorized credentials from a file.
    $credentialsPath = 'token.json';
    if (file_exists($credentialsPath)) {
        $accessToken = json_decode(file_get_contents($credentialsPath), true);
    } else {
        // Request authorization from the user.
        $authUrl = $client->createAuthUrl();
        printf("Open the following link in your browser:\n%s\n", $authUrl);
        print 'Enter verification code: ';
        $authCode = trim(fgets(STDIN));

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        // Check to see if there was an error.
        if (array_key_exists('error', $accessToken)) {
            throw new Exception(join(', ', $accessToken));
        }

        // Store the credentials to disk.
        if (!file_exists(dirname($credentialsPath))) {
            mkdir(dirname($credentialsPath), 0700, true);
        }
        file_put_contents($credentialsPath, json_encode($accessToken));
        printf("Credentials saved to %s\n", $credentialsPath);
    }
    $client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
    }
    return $client;
}


// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Sheets($client);

$spreadsheetId = '1Mw8KvNflKOQmm_bCor7goRdAlQBhGTvVE3Sb4ECtUuE';
// $range = 'OnboardingTasks!A2:E';
// $response = $service->spreadsheets_values->get($spreadsheetId, $range);
// $values = $response->getValues();

// Need to use batch get as spreadsheets_values->get ignores empty cells and so returns variable
// length arrays, which means it's impossible to work out which values are missing/empty
$params = array('ranges' => array('OnboardingTasks!A2:A', // names
                                  'OnboardingTasks!D2:D', // TEDx
                                  'OnboardingTasks!E2:E' // Year 9 workshop
                                  ));
$response = $service->spreadsheets_values->batchGet($spreadsheetId, $params);
$columns = $response->valueRanges;

print "GOT " . get_user_status_table('c_houseman', $columns) . "\n";

function get_user_status_table($user_login, $columns){
  if (count($columns) < 3){
    return "<p class=\"error\">Could not access table data.</p>\n";
  }
  $names = $columns[0];
  $tedx = $columns[1];
  $workshop9 = $columns[2];

  $uidx = get_user_index($user_login, $names);
  if (is_null($uidx)) {
    return "<p class=\"error\">Could not find user data for $user_login.</p>\n";
  }

  $thtml = "<table>\n";
  $thtml .= table_row_tedx($tedx[$uidx]);
  $thtml .= table_row_workshop9($workshop9[$uidx]);
  $thtml .= "</table>\n";
  return $thtml;
}


function get_user_index($user_login, $names){
  for($i = 0, $l = count($names); $i < $l; $i++) {
    if ($names[$i][0] == $user_login) {
      return $i;
    }
  }
  return null;
}


function table_row_tedx($cell) {
  $row = "<tr><td>TEDx talk</td>";
  if (empty($cell) || $cell[0] === '') {
    $row .= "<td class=\"task_incomplete\">Incomplete</td>";
    $row .= "<td><a href=\"http://www.google.co.uk\">Book your talk</a></td>";
  } else {
    $row .= "<td>Completed</td>";
    $row .= "<td>$cell[0]</td>";
  }
  $row .= "</tr>\n";
  return $row;
}


function table_row_workshop9($cell) {
  $row = "<tr><td>Year 9 talk</td>";
  if (empty($cell) || $cell[0] === '') {
    $row .= "<td class=\"task_incomplete\">Incomplete</td>";
    $row .= "<td><a href=\"http://www.google.co.uk\">Book your talk</a></td>";
  } else {
    $row .= "<td>Completed</td>";
    $row .= "<td>$cell[0]</td>";
  }
  $row .= "</tr>\n";
  return $row;
}
