<?php

namespace Deployer;

require 'recipe/laravel.php';

// ----------------------------------------------
// Configuration
// ----------------------------------------------

set('repository', 'https://github.com/VincentNdegwa/validtrack.git');
set('keep_releases', 2);

add('shared_files', ['.env']);
add('shared_dirs', ['storage']);
add('writable_dirs', ['storage', 'bootstrap/cache', 'public/build']);

// ----------------------------------------------
// Hosts
// ----------------------------------------------

host('validtrack')
    ->setHostname('164.92.89.75')
    ->setRemoteUser('root')
    ->setDeployPath('/var/www/validtrack');

// ----------------------------------------------
// Tasks
// ----------------------------------------------

// Clear previous frontend build
task('deploy:clear_old_build', function () {
    run('rm -rf {{release_path}}/public/build');
});

// Build frontend assets using pnpm
task('build:assets', function () {
    writeln('<info>Checking Node & PNPM versions...</info>');
    run('node -v');
    run('pnpm -v');

    writeln('<info>Installing JS dependencies...</info>');
    run('cd {{release_path}} && pnpm install >> build.log 2>&1');

    writeln('<info>Building assets...</info>');
    run('cd {{release_path}} && NODE_OPTIONS="--max-old-space-size=2048" pnpm build >> build.log 2>&1');
});

// Composer install for production
task('deploy:composer', function () {
    run('cd {{release_path}} && composer install --no-dev --prefer-dist --optimize-autoloader');
});

// Laravel migrate (forcefully)
task('deploy:migrate', function () {
    run('cd {{release_path}} && php artisan migrate --force');
});

// Laravel cache clearing
task('laravel:clear_caches', function () {
    run('cd {{release_path}} && php artisan config:clear');
    run('cd {{release_path}} && php artisan route:clear');
    run('cd {{release_path}} && php artisan view:clear');
    run('cd {{release_path}} && php artisan optimize:clear');
});

// Set permissions
task('deploy:permissions', function () {
    run('cd {{release_path}} && chown -R www-data:www-data storage bootstrap/cache');
});

// Restart Supervisor workers
task('supervisor:restart', function () {
    run('sudo systemctl start supervisor');
    run('sudo supervisorctl reread');
    run('sudo supervisorctl update');
    run('sudo supervisorctl restart validtrack-worker:*');
});

// Reload Apache
task('apache:reload', function () {
    run('sudo systemctl reload apache2');
});

// ----------------------------------------------
// Hooks
// ----------------------------------------------

before('deploy:symlink', 'deploy:clear_old_build');
before('deploy:symlink', 'build:assets');

after('deploy:symlink', 'deploy:composer');
after('deploy:symlink', 'deploy:permissions');
after('deploy:symlink', 'deploy:migrate');
after('deploy:symlink', 'laravel:clear_caches');
after('deploy:symlink', 'supervisor:restart');
after('deploy:symlink', 'apache:reload');

after('deploy:failed', 'deploy:unlock');
