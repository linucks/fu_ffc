// API key from the Developer Console
var API_KEY = '';
var SPREADSHEET_ID = '1WS7C8liq1PVucI0cCOLSBlZl-upf7ldZHHFG1PrBA90';

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
        range: 'A:H',
    }).then(processRange);
}

function processRange(response) {
    range = response.result;
    getDataList();
    drawTable();
    printUser();
}

function printUser() {
  var t = document.createTextNode("Hello " + MyScriptData.user_login + "!")
  document.getElementById('user').appendChild(t);
}

function getDataList() {
    if (range.values.length <= 0) {
        console.error("No data returned");
        return;
    }
    dataList = [];
    categories = {};
    var idx;
    var ncategories = 0;
    for (i = 0; i < range.values.length; i++) {
        if (i == 0) {
            continue;
        }
        /*
        Column 0: Name
        Column 1: Category
        Column 2: Website
        Column 3: GPS Coordinates
        Column 4: Description
        */
        var row = range.values[i];
        var category = row[1];
        var category_id = row[1].replace(/ /g, "_");
        [lng, lat] = row[3].split(",");
        lat = parseFloat(lat);
        lng = parseFloat(lng);
        dataList.push({
            id: "id_" + i.toString(),
            name: row[0],
            category: category,
            category_id: category_id,
            url: row[2],
            lat: lat,
            lng: lng,
            description: row[4]
        });
    }
}

/**
 * Draw the results table
 */
function drawTable() {
    removeOldResults();
    var table = document.createElement('table');
    table.setAttribute('id', 'output');
    var tbody = document.createElement('tbody');

    var tr, td, tnode;
    // Create the header
    var headers = ['Name', 'Category', 'Website'];
    tr = document.createElement('tr');
    for (var j = 0; j < headers.length; j++) {
        td = document.createElement('th');
        tnode = document.createTextNode(headers[j]);
        td.appendChild(tnode);
        tr.appendChild(td);
    }
    tbody.appendChild(tr);

    // Now data rows
    for (var i = 0; i < dataList.length; i++) {
        if (i > 0) {
            tbody.appendChild(tr);
        }
        tr = document.createElement('tr');
        for (var j = 0; j < headers.length; j++) {
            td = document.createElement('td');
            if (headers[j] == 'Website') {
                tnode = document.createElement('a');
                tnode.appendChild(document.createTextNode(dataList[i].url));
                tnode.title = dataList[i].url;
                tnode.href = dataList[i].url;
            } else if (headers[j] == 'Name') {
                tnode = document.createElement('a');
                tnode.appendChild(document.createTextNode(dataList[i].name));
                tnode.title = dataList[i].name;
                tnode.href = "#map";
                tnode.setAttribute("id", dataList[i].id)
            } else if (headers[j] == 'Category') {
                tnode = document.createTextNode(dataList[i].category);
            }
            td.appendChild(tnode);
            tr.appendChild(td);
        }
    }
    tbody.appendChild(tr);
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
