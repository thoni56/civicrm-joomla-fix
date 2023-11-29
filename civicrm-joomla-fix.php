<?php

return new class {
    public function __invoke(
        \Joomla\Application\AbstractApplication         $app,
        \Symfony\Component\Console\Input\InputInterface $input
    ): void
    {
        // Static values, replace these with your actual values.
        $old_path = '/var/www/events';
        $old_web_host = 'events.responsive.se';
        $old_db_host = 'localhost';
        $new_web_host = 'events-migrated-to-j4.localhost.tv';
        $new_db_host = 'mysql';
        $db_name = 'events-migrated-to-j4';
        $db_user = 'events-migrated-to-j4';
        $db_password = 'events-migrated-to-j4';

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
            $path_replacement = getcwd()."/htdocs";
            $contents = str_replace($old_path, $path_replacement, $contents);

            file_put_contents($file, $contents);
        }
    }
};

?>
