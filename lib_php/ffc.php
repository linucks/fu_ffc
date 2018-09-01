<?php

require __DIR__ . '/vendor/autoload.php';

function get_user_status_table(){
  $html = "";
  try {
    $columns = get_spreadsheet_columns();
    $user_login = wp_get_current_user()->user_login;
    $html =  make_table_html($user_login, $columns);
  } catch(Exception $e) {
    $html = "<p class=\"ffc_error\">Error getting user status table: " . $e->getMessage() . "</p>\n";
  }
  return $html;
}


function get_spreadsheet_columns(){
  // Get the API client and construct the service object.
  $client = get_client();
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
  return $columns;
}


function get_client()
{
    $client = new Google_Client();
    $client->setApplicationName('Google Sheets API PHP Quickstart');
    $client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
    $client->setAuthConfig(get_stylesheet_directory() . '/lib_php/credentials.json');
    $client->setAccessType('offline');

    // Load previously authorized credentials from a file.
    $credentialsPath = get_stylesheet_directory() . '/lib_php/token.json';
    if (file_exists($credentialsPath)) {
        $accessToken = json_decode(file_get_contents($credentialsPath), true);
    } else {
        throw new Exception('No credentials file found.');
    }
    $client->setAccessToken($accessToken);
    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
        $oldAccessToken = $client->getAccessToken();
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        $accessToken=$client->getAccessToken();
        $accessToken['refresh_token'] = $oldAccessToken['refresh_token'];
        file_put_contents($credentialsPath, json_encode($accessToken));
        // https://stackoverflow.com/questions/39314833/google-api-client-refresh-token-must-be-passed-in-or-set-as-part-of-setaccessto
        // $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        // file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
    }
    return $client;
}


function make_table_html($user_login, $columns){
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
  $thtml .= "<tr><th>Activity</th><th>Details</th><th>Status</th><th>Action</th></tr>\n";
  $thtml .= table_row_tedx($tedx[$uidx]);
  $thtml .= table_row_workshop9($workshop9[$uidx]);
  $thtml .= table_row_trainingday($workshop9[$uidx]);
  $thtml .= table_row_launchevent($workshop9[$uidx]);
  $thtml .= table_row_competition($workshop9[$uidx]);
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


function get_date_str($cell_data){
  try {
    $date = new DateTime($cell_data);
    $date_str = $date->format('j F Y');
  } catch(Exception $e) {
    $date_str = $cell_data; // or $e->getMessage() ?
  }
  return $date_str;
}


function table_row_tedx($cell) {
  $row = "<tr><td>TEDx talk</td>";
  $row .= "<td>October – November 2019</td>";
  if (empty($cell) || $cell[0] === '') {
    $row .= "<td class=\"ffc_task_incomplete\">Incomplete</td>";
    $row .= "<td><a href=\"http://www.google.co.uk\">Book your talk</a></td>";
  } else {
    $row .= "<td>Completed</td>";
    $date = get_date_str($cell[0]);
    $row .= "<td>Your talks are on: $date</td>";
  }
  $row .= "</tr>\n";
  return $row;
}


function table_row_workshop9($cell) {
  $row = "<tr><td>Year 9 talk</td>";
  $row .= "<td>July 2019</td>";
  if (empty($cell) || $cell[0] === '') {
    $row .= "<td class=\"ffc_task_incomplete\">Incomplete</td>";
    $row .= "<td><a href=\"http://www.google.co.uk\">Book your talk</a></td>";
  } else {
    $row .= "<td>Completed</td>";
    $date = get_date_str($cell[0]);
    $row .= "<td>Your workshops are on: $date</td>";
  }
  $row .= "</tr>\n";
  return $row;
}


function table_row_trainingday($cell) {
  $row = "<tr><td>Teacher Training Day</td>";
  $row .= "<td>16th January 2019</td>";
  $row .= "<td>Ok</td>";
  $row .= "<td>N/A</td>";
  $row .= "</tr>\n";
  return $row;
}


function table_row_launchevent($cell) {
  $row = "<tr><td>Launch Event</td>";
  $row .= "<td>6th February 2019</td>";
  $row .= "<td>Ok</td>";
  $row .= "<td>N/A</td>";
  $row .= "</tr>\n";
  return $row;
}


function table_row_12weekstart($cell) {
  $row = "<tr><td>Start of 12 Week Programme</td>";
  $row .= "<td>11th February 2019</td>";
  $row .= "<td>Ok</td>";
  $row .= "<td>N/A</td>";
  $row .= "</tr>\n";
  return $row;
}


function table_row_competition($cell) {
  $row = "<tr><td>Competition Event</td>";
  $row .= "<td>27th June 2019</td>";
  $row .= "<td>Ok</td>";
  $row .= "<td>N/A</td>";
  $row .= "</tr>\n";
  return $row;
}
