<?php

return new class {
    public function __invoke(
        \Joomla\Application\AbstractApplication $app,
        \Symfony\Component\Console\Input\InputInterface $input
    ): void {
        // As this file is executed after the install configuration.php is updated
        // but the CiviCRM configuration files are not, so we can get
        // the new values from configuration.php and the old from civicrm settings

        // Figure out the old values from the civicrm settings
        $civicrm_root = '';
        $lines = file('htdocs/administrator/components/com_civicrm/civicrm.settings.php');
        foreach ($lines as $line) {
            if (strpos($line, '$civicrm_root') === 0) {
                $civicrm_root = trim(explode('=', $line)[1]);
                break;
            }
        }

        $old_path = '';
        $parts = explode('/', $civicrm_root);
        for ($i = 0; $parts[$i] != 'administrator'; $i++) {
            $old_path .= $parts[$i] . '/';
        }

        $old_web_host = '';
        foreach ($lines as $line) {
            if (
                preg_match("/define\(\s*'CIVICRM_UF_BASEURL'\s*,/", $line) &&
                preg_match("/'([^']+)'/", $line, $matches)
            ) {
                $old_web_host = $matches[1];
                break;
            }
        }

        $old_db_host = '';
        foreach ($lines as $line) {
            if (
                preg_match("/define\(\s*'CIVICRM_UF_DSN'\s*,/", $line) &&
                preg_match("/'([^']+)'/", $line, $matches)
            ) {
                $value = $matches[1];
                break;
            }
        }

        $new_web_host = 'events-copy.localhost.tv'; // What about this?

        // Figure out the new db values from the configuration.php file
        // Get the current values from the configuration.php file
        include 'htdocs/configuration.php';
        $config = new JConfig();
        $new_db_host = $config->host;
        $db_name = $config->db;
        $db_user = $config->user;
        $db_password = $config->password;

        echo "old_path     = $old_path\n";
        echo "old_web_host = $old_web_host\n";
        echo "old_db_host  = $old_db_host\n";
        echo "new_web_host = $new_web_host\n";
        echo "new_db_host  = $new_db_host\n";
        echo "db_name      = $db_name\n";
        echo "db_user      = $db_user\n";
        echo "db_password  = $db_password\n";

        $files_to_change = [
            'htdocs/components/com_civicrm/civicrm.settings.php',
            'htdocs/administrator/components/com_civicrm/civicrm.settings.php',
            'htdocs/administrator/components/com_civicrm/civicrm/civicrm.config.php'
        ];

        // Replacements
        foreach ($files_to_change as $file) {
            $contents = file_get_contents($file);

            // host replacement
            $contents = preg_replace("|https://$old_web_host|", "https://$new_web_host", $contents);

            // db replacement
            $db_old_connection_string = "mysql://[^:]*:[^@]*@$old_db_host/[^?]*";
            $db_new_connection_string = "mysql://$db_user:$db_password@$new_db_host/$db_name";
            $contents = preg_replace("|$db_old_connection_string|", $db_new_connection_string, $contents);

            // path replacement
            $path_replacement = getcwd() . "/htdocs";
            $contents = str_replace($old_path, $path_replacement, $contents);

            file_put_contents($file, $contents);
        }
    }
};

?>