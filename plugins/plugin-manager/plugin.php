<?php

/**
 * Plugin name: Plugin Manager
 * Description: Manage, upload, and uninstall plugins
 * Version: 1.0
 * Author: ThunderPHP
 **/

if(!defined('PLUGINS_PATH'))
    define('PLUGINS_PATH', __DIR__ . '/..');

set_value([
    'plugin_route' => 'plugins',
    'admin_route' => 'admin',
]);

/** set user permissions for this plugin **/
add_filter('permissions',function($permissions){
    $permissions[] = 'view_plugins';
    $permissions[] = 'upload_plugin';
    $permissions[] = 'uninstall_plugin';
    return $permissions;
});

/** add to admin links **/
add_filter('basic-admin_before_admin_links',function($links){
    if(user_can('view_plugins'))
    {
        $vars = get_value();
        $obj = (object)[];
        $obj->title = 'Plugins';
        $obj->link = ROOT . '/'.$vars['admin_route'].'/'.$vars['plugin_route'];
        $obj->icon = 'fa-solid fa-puzzle-piece';
        $obj->parent = 0;
        $links[] = $obj;
    }
    return $links;
});

/** handle form submissions **/
add_action('controller',function(){
    $req = new \Core\Request;
    $vars = get_value();
    
    if(URL(1) == $vars['plugin_route'] && $req->posted())
    {

        // Handle plugin upload
        if(!empty($_FILES['plugin_zip']) && user_can('upload_plugin'))
        {
            $file = $_FILES['plugin_zip'];
            if($file['error'] === 0)
            {
                $temp_dir = sys_get_temp_dir();
                $zip = new ZipArchive;
                
                if($zip->open($file['tmp_name']) === TRUE)
                {
                    // Extract to temp directory first
                    $temp_extract = $temp_dir . '/plugin_temp_' . time();
                    if(!file_exists($temp_extract)) {
                        mkdir($temp_extract, 0777, true);
                    }
                    
                    $zip->extractTo($temp_extract);
                    $zip->close();

                    // Check for plugin files in subdirectories
                    $plugin_files = glob($temp_extract . '/**/plugin.php');
                    $config_files = glob($temp_extract . '/**/config.json');
                    
                    if(!empty($plugin_files) && !empty($config_files))
                    {
                        $plugin_dir = dirname($plugin_files[0]);
                        $config = json_decode(file_get_contents($config_files[0]));
                        
                        if(!empty($config->id))
                        {
                            $target_dir = PLUGINS_PATH . '/' . $config->id;
                            
                            // Move to plugins directory
                            if(!file_exists($target_dir))
                            {
                                // Move files to target directory
                                rename($plugin_dir, $target_dir);
                                
                                // Check and run migrations if they exist
                                $migrations_dir = $target_dir . '/migrations';
                                if(file_exists($migrations_dir))
                                {
                                    $migration_files = glob($migrations_dir . '/*.php');
                                    foreach($migration_files as $migration_file)
                                    {
                                        require_once $migration_file;
                                        $migration_class = basename($migration_file, '.php');
                                        if(class_exists($migration_class))
                                        {
                                            $migration = new $migration_class();
                                            if(method_exists($migration, 'up'))
                                            {
                                                $migration->up();
                                            }
                                        }
                                    }
                                }
                                
                                message_success("Plugin uploaded and installed successfully!");
                            }else{
                                message_fail("A plugin with this ID already exists!");
                            }
                        }else{
                            message_fail("Invalid plugin configuration: missing plugin ID!");
                        }
                    }else{
                        message_fail("Invalid plugin structure: missing plugin.php or config.json!");
                    }

                    // Cleanup temp directory
                    delete_directory($temp_extract);
                }else{
                    message_fail("Could not open ZIP file!");
                }
            }else{
                message_fail("Error uploading file: " . $file['error']);
            }
            redirect($vars['admin_route'].'/'.$vars['plugin_route']);
        }

        // Handle plugin uninstall
        if(!empty($_POST['action']) && $_POST['action'] == 'uninstall' && !empty($_POST['plugin_id']) && user_can('uninstall_plugin'))
        {
            $plugin_id = $_POST['plugin_id'];
            $plugin_dir = PLUGINS_PATH . '/' . $plugin_id;
            
            if(file_exists($plugin_dir))
            {
                delete_directory($plugin_dir);
                message_success("Plugin uninstalled successfully!");
            }else{
                message_fail("Plugin not found!");
            }
            redirect($vars['admin_route'].'/'.$vars['plugin_route']);
        }
    }
});

/** displays the view file **/
add_action('basic-admin_main_content',function(){
    $vars = get_value();
    
    if(URL(1) == $vars['plugin_route'])
    {
        // Get list of installed plugins
        $plugins = [];
        $plugin_dirs = glob(PLUGINS_PATH . '/*', GLOB_ONLYDIR);
        
        foreach($plugin_dirs as $dir)
        {
            $config_file = $dir . '/config.json';
            if(file_exists($config_file))
            {
                $config = json_decode(file_get_contents($config_file));
                if(!empty($config))
                    $plugins[] = $config;
            }
        }
        
        require plugin_path('views/list.php');
    }
});

/** Helper function to delete directory recursively **/
function delete_directory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);
    
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!delete_directory($dir . DIRECTORY_SEPARATOR . $item)) return false;
    }
    
    return rmdir($dir);
}


