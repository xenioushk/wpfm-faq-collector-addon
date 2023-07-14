<?php

if (!class_exists('WpAutoUpdater')) {

    class WpAutoUpdater
    {
        public $current_version;

        public $update_path;

        public $plugin_slug;

        public $slug;

        function __construct($current_version, $update_path, $plugin_slug)
        {
            // Set the class public variables
            $this->current_version = $current_version;
            $this->update_path = $update_path;
            $this->plugin_slug = $plugin_slug;

            list($t1, $t2) = explode('/', $plugin_slug);
            $this->slug = str_replace('.php', '', $t2);

            // define the alternative API for updating checking
            add_filter('pre_set_site_transient_update_plugins', array(&$this, 'check_update'));

            // Define the alternative response for information checking
            add_filter('plugins_api', array(&$this, 'check_info'), 10, 3);
        }


        public function check_update($transient)
        {

            if (empty($transient->checked)) {
                return $transient;
            }

            // Get the remote version
            $remote_version = $this->getRemote_version();

            // If a newer version is available, add the update
            if (version_compare($this->current_version, $remote_version, '<')) {
                $obj = new stdClass();
                $obj->slug = $this->slug;
                $obj->new_version = $remote_version;
                $obj->url = $this->update_path;
                $obj->package = $this->update_path;
                $transient->response[$this->plugin_slug] = $obj;
            }
            return $transient;
        }

        public function check_info($false, $action, $arg)
        {
            if ($arg->slug === $this->slug) {
                $information = $this->getRemote_information();
                //$information->slug = $this->slug; //This is the new addition
                return $information;
            }
            return $false;
        }

        public function getRemote_version()
        {
            $request = wp_remote_post($this->update_path, array('body' => array('action' => 'version')));
            if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
                return $request['body'];
            }
            return false;
        }

        public function getRemote_information()
        {
            $request = wp_remote_post($this->update_path, array('body' => array('action' => 'info')));
            if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
                return unserialize($request['body']);
            }
            return false;
        }

        public function getRemote_license()
        {
            $request = wp_remote_post($this->update_path, array('body' => array('action' => 'license')));
            if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
                return $request['body'];
            }
            return false;
        }
    }
}
