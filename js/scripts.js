// API key from the Developer Console
var API_KEY = '';
var SPREADSHEET_ID = '1bM7iqUZYuptvxBBEpeVfaFNZBuO_ViukpAH98mlHSrU';

var range;
var dataList;
var categories;

/**
 *  On load, called to load the API client library.
 */
function handleClientLoad() {
    gapi.load('client', initClient);
}

/**
 *  Initializes the API client library and sets up sign-in state
 *  listeners.
 */
function initClient() {
    gapi.client.init({
        discoveryDocs: ["https://sheets.googleapis.com/$discovery/rest?version=v4"],
        apiKey: API_KEY,
        //scope: "https://www.googleapis.com/auth/spreadsheets.readonly"
        // Bug in google code?}).then(setup).catch(console.error(Error("Could not setup page!")));
    }).then(getRange, function() {
        alert(Error("Could not load data - please refresh the page or try again later."))
    });
}

function getRange() {
    gapi.client.sheets.spreadsheets.values.get({
        spreadsheetId: SPREADSHEET_ID,
        range: 'Sheet2!A:D',
    }).then(processRange);
}

function processRange(response) {
    range = response.result;
    getDataList();
    drawTable();
    printUser();
}

function getDataList() {
    if (range.values.length <= 0) {
        console.error("No data returned");
        return;
    }
    header = range.values[0];
    dataList = [header];
    for (i = 1; i < range.values.length; i++) {
        var row = range.values[i];
        var row_data = { user_login: row[0],
                         reg_form: row[1],
                         risk_asses: row[2],
                         workshop_date: row[3]
                       };

        if (row_data.user_login == MyScriptData.user_login) {
          dataList.push(row_data);
        }
    }
}

function drawTable() {
    removeOldResults();
    var table = document.createElement('table');
    table.setAttribute('id', 'output');
    var tbody = document.createElement('tbody');

    var tr, td, tnode;
    var header = dataList[0];
    tr = document.createElement('tr');
    for (var j = 0; j < header.length; j++) {
        td = document.createElement('th');
        tnode = document.createTextNode(header[j]);
        td.appendChild(tnode);
        tr.appendChild(td);
    }
    tbody.appendChild(tr);

    for (var i = 1; i < dataList.length; i++) {
        tr = document.createElement('tr');

        td = document.createElement('td');
        tnode = document.createTextNode(dataList[i].user_login);
        td.appendChild(tnode);
        tr.appendChild(td);
        td = document.createElement('td');
        tnode = document.createTextNode(dataList[i].reg_form);
        td.appendChild(tnode);
        tr.appendChild(td);
        td = document.createElement('td');
        tnode = document.createTextNode(dataList[i].risk_assess);
        td.appendChild(tnode);
        tr.appendChild(td);
        td = document.createElement('td');
        tnode = document.createTextNode(dataList[i].workshop_date);
        td.appendChild(tnode);
        tr.appendChild(td);

        tbody.appendChild(tr);
    }
    table.appendChild(tbody);
    document.getElementById('tdata').appendChild(table);
}

/**
 * Removes the output generated from the previous result.
 */
function removeOldResults() {
    var div = document.getElementById('tdata');
    if (div.firstChild) {
        div.removeChild(div.firstChild);
    }
}

function printUser() {
  var t = document.createTextNode("Hello " + MyScriptData.user_login + "!")
  document.getElementById('user').appendChild(t);
}
