<?php

function get_sensor_data($station, $sensor)
{
    global $wpdb;
    $query_str = "SELECT * FROM farmurban.$sensor WHERE station=$station";
    $query = $wpdb->prepare($query_str, 'foo');
    $rows = $wpdb->get_results($query);

    // Define the table columns, i.e. what the x and y data actually are.
    $gdata = array();
    $gdata['labels'] = array();
    $gdata['series'] = array();
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
    return  json_encode($gdata);
}

function do_show_sensors()
{
  wp_enqueue_style( 'chartist', "http://cdn.jsdelivr.net/chartist.js/latest/chartist.min.css" );
  wp_enqueue_style( 'leaflet', "https://unpkg.com/leaflet@1.3.4/dist/leaflet.css" );
  wp_enqueue_script( 'moment', "https://cdn.jsdelivr.net/npm/moment@2.24.0/moment.min.js");
  wp_enqueue_script( 'chartist', "http://cdn.jsdelivr.net/chartist.js/latest/chartist.min.js");
  wp_enqueue_script( 'chartist-axis', "https://cdn.jsdelivr.net/npm/chartist-plugin-axistitle@0.0.4/dist/chartist-plugin-axistitle.min.js");
  wp_enqueue_script( 'leaflet', "https://unpkg.com/leaflet@1.3.4/dist/leaflet.js");

  // Add graphs after main content
  $station = 2;
  $sensor = 'ambient_light_0';
  $light_data = get_sensor_data($station, $sensor);
  $sensor = 'humidity_temperature';
  $temp_data = get_sensor_data($station, $sensor);
  $content = "<script>var light_data=$light_data;\nvar temp_data=$temp_data;</script>";
  $content .= file_get_contents(get_stylesheet_directory() . '/sensors_insert.html');
  return $content;
}
add_shortcode('show_sensors', 'do_show_sensors');
