<?php

namespace Deployer;

require 'recipe/laravel.php';

// Configurations

set('repository', 'https://github.com/VincentNdegwa/validtrack.git');

// Shared files/dirs (these persist between deployments)
add('shared_files', ['.env']);
add('shared_dirs', ['storage']);
add('writable_dirs', ['storage', 'bootstrap/cache']);

host('validtrack') 
    ->set('hostname', '164.92.89.75') 
    ->set('remote_user', 'root')     
    ->set('deploy_path', '/var/www/validtrack');


// Hooks (after deploy tasks)
after('deploy:failed', 'deploy:unlock');

// Tasks (you can add custom tasks here)

// Install Composer dependencies
task('deploy:composer', function () {
    run('cd {{release_path}} && composer install --no-dev --prefer-dist --optimize-autoloader');
});

// Run migrations
task('deploy:migrate', function () {
    run('cd {{release_path}} && php artisan migrate --force');
});

// Set permissions
task('deploy:permissions', function () {
    run('cd {{release_path}} && chown -R www-data:www-data storage bootstrap/cache');
});

after('artisan:config:cache', 'artisan:config:clear');

before('deploy:symlink', 'build:assets');

task('build:assets', function () {
    $npmPath = run('which npm');
    run("cd {{release_path}} && $npmPath ci && $npmPath run build");
});

// Run when deploy is successful
after('deploy:symlink', 'deploy:permissions');
after('deploy:symlink', 'deploy:migrate');