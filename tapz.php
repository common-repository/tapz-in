<?php
/*
    Plugin Name: Tapz.
    Plugin URI: https://tapz.in
    Description: Web Promotion and User engagement tools - Popup/sticky banner,Feedback widget, Contact widget, Social chat and share widget, Lead form and URL Shortner based on multiple targeting rules.
    Version: 1.0
    Author: tapz.in
    Author URI: 
*/

// Version check
global $wp_version;
if(!version_compare($wp_version, '3.0', '>='))
{
    die("tapz.in requires WordPress 3.0 or above. <a target='_blank' href='http://codex.wordpress.org/Upgrading_WordPress'>Please update!</a>");
}
// END - Version check


//this is to avoid getting in trouble because of the
//wordpress bug http://core.trac.wordpress.org/ticket/16953
$tapz_file = __FILE__; 

if ( isset( $mu_plugin ) ) { 
    $tapz_file = $mu_plugin; 
} 
if ( isset( $network_plugin ) ) { 
    $tapz_file = $network_plugin; 
} 
if ( isset( $plugin ) ) { 
    $tapz_file = $plugin; 
} 

$GLOBALS['tapz_file'] = $tapz_file;


// Make sure class does not exist already.
if(!class_exists('tapz')) :

    class tapzWidget extends WP_Widget {
        function tapzWidget() {
            parent::WP_Widget(false, 'tapz Widget', array('description' => 'Description'));
        }

        function widget($args, $instance) {
            echo '<div id="tapz_widget"></div>';
        }

        function update( $new_instance, $old_instance ) {
            // Save widget options
            return parent::update($new_instance, $old_instance);
        }

        function form( $instance ) {
            // Output admin widget options form
            return parent::form($instance);
        }
    }

    function tapz_widget_register_widgets() {
        register_widget('tapzWidget');
    }

    // Declare and define the plugin class.
    class tapz
    {
        // will contain id of plugin
        private $plugin_id;
        // will contain option info
        private $options;

        /** function/method
        * Usage: defining the constructor
        * Arg(1): string(alphanumeric, underscore, hyphen)
        * Return: void
        */
        public function __construct($id)
        {
            // set id
            $this->plugin_id = $id;
            // create array of options
            $this->options = array();
            // set default options
            $this->options['key'] = '';            
            $this->options['client_id'] = '';

            /*
            * Add Hooks
            */
            // register the script files into the footer section
            add_action('wp_footer', array(&$this, 'tapz_scripts'));
            // initialize the plugin (saving default options)
            register_activation_hook(__FILE__, array(&$this, 'install'));
            // triggered when plugin is initialized (used for updating options)
            add_action('admin_init', array(&$this, 'init'));
            // register the menu under settings
            add_action('admin_menu', array(&$this, 'menu'));
            // Register sidebar widget
            add_action('widgets_init', 'tapz_widget_register_widgets');

           
        }

        /** function/method
        * Usage: return plugin options
        * Arg(0): null
        * Return: array
        */
        private function get_options()
        {
            // return saved options
            $options = get_option($this->plugin_id);
            return $options;
        }
        /** function/method
        * Usage: update plugin options
        * Arg(0): null
        * Return: void
        */
        private function update_options($options=array())
        {
            // update options
            update_option($this->plugin_id, $options);
        }

        /** function/method
        * Usage: helper for loading tapz.js
        * Arg(0): null
        * Return: void
        */
        public function tapz_scripts()
        {
            if (!is_admin()) {
                $options = $this->get_options();
                $key = trim($options['key']);
                $client_id = trim($options['client_id']);
                $this->show_tapz_js($key,$client_id);
            }
        }
        
        public function show_tapz_js($key="",$client_id="")
        {        
			echo '<script>
                var _tapz=window._tapz||{};
                (function(){
                    _tapz.key="'.$key.'";
                    _tapz.client_id="'.$client_id.'";
                    _tapz.async=!1;
                    var a=document.createElement("script");
                    a.src="https://tapz.in/js/widget/tapz.1.0.js?v1";
                    var b=document.getElementsByTagName("script")[0];
                    b.parentNode.insertBefore(a,b)
                })();
                </script>';
                        
        }

        /** function/method
        * Usage: helper for hooking activation (creating the option fields)
        * Arg(0): null
        * Return: void
        */
        public function install()
        {
            $this->update_options($this->options);
        }
        
        /** function/method
        * Usage: helper for hooking (registering) options
        * Arg(0): null
        * Return: void
        */
        public function init()
        {
            register_setting($this->plugin_id.'_options', $this->plugin_id);
        }
                
        /** function/method
        * Usage: show options/settings form page
        * Arg(0): null
        * Return: void
        */
        public function options_page()
        {
            if (!current_user_can('manage_options'))
            {
                wp_die( __('You can manage options from the Settings->tapz Options menu.') );
            }

            // get saved options
            $options = $this->get_options();
            $updated = false;

            if ($updated) {
                $this->update_options($options);
            }
            include('tapz_form.php');
        }
        /** function/method
        * Usage: helper for hooking (registering) the plugin menu under settings
        * Arg(0): null
        * Return: void
        */
        public function menu()
        {
            $icon = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMDY3IDc5LjE1Nzc0NywgMjAxNS8wMy8zMC0yMzo0MDo0MiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjQ3MjZDNjIwMThCMjExRUE5MEI1QjcxOEI1MzEzNDkwIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjQ3MjZDNjIxMThCMjExRUE5MEI1QjcxOEI1MzEzNDkwIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6NDcyNkM2MUUxOEIyMTFFQTkwQjVCNzE4QjUzMTM0OTAiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6NDcyNkM2MUYxOEIyMTFFQTkwQjVCNzE4QjUzMTM0OTAiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz6rKRR1AAADNUlEQVR42qxUTUhUURQ+9747b97o6KhhVpRBRj9U6K5VumiRhBRBEf0YEQm2qEUtBGmhZCQGte5HoUyiIFIQ+iECKdpUiyDFFmJRNM5MUqlNM3fee7fvvvfUxmznhe+dc88999zz+5hSipZyiVnmYNUumkxblJguIIN7j1iuomZw9aTYEex3A1eIqWFGNACdPtNwk5awZ/Xp+UT/vEFXMYpZWZKOQWkpiDFaCfFV/1SZROwbmBQjVYvrddi3wHArZD2LeqiXAY2KaJp+ZEyStuFim4WYAxFbsUcKAL+WM3UIXrUiW93g10DW/o9BFQBGqMSSlLENpb3WC5cUwvN4x2WffknRCc1+UzgPIGoDPgK38gyGuFsOchco1vuo6YZxyQyeG8An4wUh6EJYOP05xxiFwT148rWXW6LHQGLOYHE4dxRk57y/7O9sbPNlNKWIvdKNwfzjMZBLoF3AMeDyvEEru5f5xWkBEgizjDHVqZ1XviwOY2MGU8nSSEanwesK6NyBTgewL89gSURuBom7LuvKOpws4ZTAi4teWhT1TUvzq9ZbVTSjzwiPeEZzLo9D7wOONublEIXQuRtHaLqqugLRucBIhUsjWS8Lgrs1eHQ92JGYJUccT52+A5vyDKIHp0CW6YYG0sDPABW4vKPAtMelzS3p8l7st0J+ChgJrut70ws9HAWphTc69LdwRyv0YX8W9Bp8bQhxp6qQGIypJM4fBt5Xgt8A5s1CDwd8g9QMNOketBVvQztVYjr2Q3YgUP1su/w4wk8I5vXmCV04QN/HZAY/h3cny5fjtfdgy4B6JP5ZxhY0I0NkGk4dqlsNzcmcw59iSlLRcA7TxGug+5K8iWJbqm+mJuY8LApLhEHngNvAPST/MArxJGsb9DsnhhzFhnRV9cREQjbBy+3S4PehWwicASbyQi6AElYvcrUOIbYpv/NvwJOeYksOY5+GPctFvmCskTH3NDwX6M0OBNmz6M8hmNv2sLC/cOY1axOUm1DVJHKmWyPGlVoR8uc6hd/b+awtrjuK0X8NBkPXjWIMcq4awTdAqpt2NTCD0xfQGUQv9kqHxxfeZ0v9x+a0xOuPAAMAgeJFzj9a88MAAAAASUVORK5CYII=";
            add_menu_page('Tapz', 'Tapz', 'manage_options',$this->plugin_id.'-plugin', array(&$this, 'options_page'),$icon,'80');
        }
    }

    // Instantiate the plugin
    $tapz = new tapz('tapz');

// END - class exists
endif;
?>
