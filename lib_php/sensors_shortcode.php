<?php

// styles need to be enqueue as they go in the header
// wp_enqueue_style( 'sensors', get_stylesheet_directory_uri() . '/sensors.css');
wp_enqueue_style( 'chartist', "http://cdn.jsdelivr.net/chartist.js/latest/chartist.min.css" );
wp_enqueue_style( 'leaflet', "https://unpkg.com/leaflet@1.3.4/dist/leaflet.css" );
// just register the styles
wp_register_script( 'moment', 'https://cdn.jsdelivr.net/npm/moment@2.24.0/moment.min.js', array(), '1.0.0', true  );
wp_register_script( 'chartist', "http://cdn.jsdelivr.net/chartist.js/latest/chartist.min.js", array(), '1.0.0', true );
wp_register_script( 'chartist-axis', "https://cdn.jsdelivr.net/npm/chartist-plugin-axistitle@0.0.4/dist/chartist-plugin-axistitle.min.js", array(), '1.0.0', true );
wp_register_script( 'leaflet', "https://unpkg.com/leaflet@1.3.4/dist/leaflet.js", array(), '1.0.0', true );
wp_register_script( 'draw_graphs', get_stylesheet_directory_uri() . '/js/sensor_graphs.js', array(), '1.0.0', true );

function get_sensor_data($station, $sensor, $num_days=5)
{
    global $wpdb;

    // sensor dates are 4 days behind the actual data
    $end_date = "SUBDATE(NOW(), 4)";
    $start_date = "SUBDATE($end_date, $num_days)";
    $date_query = "time BETWEEN $start_date AND $end_date";

    $query_str = "SELECT * FROM farmurban.$sensor WHERE station=$station AND $date_query";
    $query = $wpdb->prepare($query_str, 'foo');
    $rows = $wpdb->get_results($query);

    // Define the table columns, i.e. what the x and y data actually are.
    $gdata = array(
      'labels' => array(),
      'series' => array() );
    $gdata['series'][] = array();
    $time_as_int = 0;
    $i=1;
    foreach ($rows as $obj) :
    {
            if ($time_as_int == 1) {
                $gdata['labels'][] = $i;
            } else {
                $gdata['labels'][] = strtotime($obj->time);
            }
            $gdata['series'][0][] = (float)$obj->reading;
            $i++;
    }
    endforeach;
    return $gdata;
}

function do_show_sensors($atts = [])
{
  $a = shortcode_atts( array(
    'station' => null,
    'num_days' => null,
  ), $atts );

  if ($a['station'] === null) {
    return "<p class=\"error\">Please provide a station number.</p>";
  };
  // Need to have enqeue styles earlier as end up in header
  // wp_enqueue_style( 'chartist', "http://cdn.jsdelivr.net/chartist.js/latest/chartist.min.css" );
  // wp_enqueue_style( 'leaflet', "https://unpkg.com/leaflet@1.3.4/dist/leaflet.css" );
  wp_enqueue_script( 'moment' );
  wp_enqueue_script( 'chartist' );
  wp_enqueue_script( 'chartist-axis' );
  wp_enqueue_script( 'leaflet' );

  $station = $a['station'];
  $control = 3;
  $sensor = 'ambient_light_0';
  //$light_data = get_sensor_data($station, $sensor);
  //$light_data_control = get_sensor_data($control, $sensor);
  $light_data = get_sensor_data($station, $sensor, $a['num_days']);
  $light_data_control = get_sensor_data($control, $sensor, $a['num_days']);
  $sensor = 'humidity_temperature';
  //$temp_data = get_sensor_data($station, $sensor);
  //$temp_data_control = get_sensor_data($control, $sensor);
  $temp_data = get_sensor_data($station, $sensor, $a['num_days']);
  $temp_data_control = get_sensor_data($control, $sensor, $a['num_days']);

  $left_station_id = 'left_' . $station;
  $right_station_id = 'right_' . $station;
  $left_control_id = 'left_control';
  $right_control_id = 'right_control';
  $chart_data = array(
    'temp_data' => $temp_data,
    'light_data' => $light_data,
    'temp_data_control' => $temp_data_control,
    'light_data_control' => $light_data_control,
    'left_station_id' => $left_station_id,
    'right_station_id' => $right_station_id,
    'left_control_id' => $left_control_id,
    'right_control_id' => $right_control_id );

  $chart_json = json_encode($chart_data, JSON_UNESCAPED_SLASHES);
  wp_localize_script( 'draw_graphs', 'chart_json', $chart_json );
  wp_enqueue_script( 'draw_graphs' );

  $content = <<<EOT
  <section>
    <p>Here is the sensor data for the control.</p>
    <div id="$left_control_id"   class="ct-chart ct-perfect-fourth left-chart"></div>
    <div id="$right_control_id" class="ct-chart ct-perfect-fourth right-chart"></div>
    <hr style="clear: both;"/>
    <p>Here is the sensor data for your tower.</p>
    <div id="$left_station_id"   class="ct-chart ct-perfect-fourth left-chart"></div>
    <div id="$right_station_id" class="ct-chart ct-perfect-fourth right-chart"></div>
  </section>
EOT;
  return $content;
}
add_shortcode('show_sensors', 'do_show_sensors');
