/* chart_json set by wp_localize_script */

chart_json = JSON.parse(chart_json);
drawChart(chart_json.temp_data, chart_json.left_station_id, 'Temperature (C)');
drawChart(chart_json.light_data, chart_json.right_station_id, 'Lumens');
drawChart(chart_json.temp_data_control, chart_json.left_control_id, 'Temperature (C)');
drawChart(chart_json.light_data_control, chart_json.right_control_id, 'Lumens');

function drawChart(data, chartId, yAxisTitle, numDays=7) {

  chartId = '#' + chartId;
  var chartist_options = {
      plugins: [
          Chartist.plugins.ctAxisTitle({
            axisX: {
              axisTitle: 'Day',
              axisClass: 'ct-axis-title',
              offset: {
                x: 0,
                y: 32
              },
              textAnchor: 'middle'
            },
            axisY: {
              axisTitle: yAxisTitle,
              axisClass: 'ct-axis-title',
              offset: {
                x: 0,
                y: 11
              },
              textAnchor: 'middle',
              flipTitle: true
            }
          })
        ],
    axisX: {
      type: Chartist.FixedScaleAxis,
      divisor: numDays,
      labelInterpolationFnc: function(value, x, y) {
        return moment(value).format('MMM D');
      }
    }
  };

  var time_data = dataToDateSeries(data);
  // document.getElementById('log').innerHTML =  'dataX = ' + JSON.stringify(time_data, undefined, 2);
  // console.log("GOT " + JSON.stringify(time_data, undefined, 2));
  var temp_chart = new Chartist.Line(chartId, time_data, chartist_options);
}

function dataToDateSeries(data) {
  /* Not sure why the data needs to be reformatted - taken from Chartist online examples */
  var new_series = [{
    name: 'ticks',
    data: []
  }];
  for (i = 0; i < data['labels'].length; i++) {
    // Need to multiply dates by 1000 to deal with difference between PHP and JS UTC timestamps
    let ddate = new Date(data['labels'][i] * 1000);
    new_series[0].data.push({
      x: ddate,
      y: data['series'][0][i]
    });
  };
  data.series = new_series;
  return data;
}
