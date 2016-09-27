<?php
return array (
  'backend' => 
  array (
    'frontName' => 'bak7eqp9',
  ),
  'install' => 
  array (
    'date' => 'Thu, 17 Dec 2015 14:56:26 +0000',
  ),
  'crypt' => 
  array (
    'key' => '44af88f972a7d7a81fcb7bffaff9db1d',
  ),
  'session' => 
  array (
    'save' => 'files',
    'path' => 'var/tmp',
  ),
  'db' => 
  array (
    'table_prefix' => 'mg_',
    'connection' => 
    array (
      'default' => 
      array (
        'host' => 'localhost',
        'dbname' => 'ruuqbhevdevm2doab',
        'username' => 'devm2doab',
        'password' => 'Xc5vd?28',
        'model' => 'mysql4',
        'engine' => 'innodb',
        'initStatements' => 'SET NAMES utf8;',
        'active' => '1',
      ),
    ),
  ),
  'resource' => 
  array (
    'default_setup' => 
    array (
      'connection' => 'default',
    ),
  ),
  'x-frame-options' => 'SAMEORIGIN',
  'MAGE_MODE' => 'production',
  'cache_types' => 
  array (
    'config' => 1,
    'layout' => 1,
    'block_html' => 1,
    'collections' => 1,
    'reflection' => 1,
    'db_ddl' => 1,
    'eav' => 1,
    'config_integration' => 1,
    'config_integration_api' => 1,
    'full_page' => 1,
    'translate' => 1,
    'config_webservice' => 1,
    'compiled_config' => 1,
    'customer_notification' => 1,
  ),
  'cache' => 
  array (
    'frontend' => 
    array (
      'default' => 
      array (
        'backend' => 'Cm_Cache_Backend_Redis',
        'backend_options' => 
        array (
          'server' => '127.0.0.1',
          'port' => '6379',
          'persistent' => '',
          'database' => '0',
          'force_standalone' => '0',
          'connect_retries' => '1',
          'read_timeout' => '10',
          'automatic_cleaning_factor' => '0',
          'compress_data' => '1',
          'compress_tags' => '1',
          'compress_threshold' => '20480',
          'compression_lib' => 'gzip',
        ),
      ),
    ),
  ),
);
