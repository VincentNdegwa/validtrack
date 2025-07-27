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
// Disable built-in Laravel tasks that run too early
// ----------------------------------------------

// This completely unregisters the default Laravel recipe’s cache tasks
after('artisan:config:cache', 'noop');
after('artisan:route:cache', 'noop');
after('artisan:view:cache', 'noop');
after('artisan:event:cache', 'noop');
after('artisan:migrate', 'noop');

// ----------------------------------------------
// Tasks
// ----------------------------------------------

task('noop', function () {
    writeln('<comment>Skipping default Laravel artisan:* task</comment>');
});

// Clear old Vite assets
task('deploy:clear_old_build', function () {
    run('rm -rf {{release_path}}/public/build');
});

// Build frontend assets
task('build:assets', function () {
    writeln('<info>Building frontend with PNPM</info>');
    run('cd {{release_path}} && pnpm install >> build.log 2>&1');
    run('cd {{release_path}} && NODE_OPTIONS="--max-old-space-size=2048" pnpm build >> build.log 2>&1');
});

// Composer install
task('deploy:composer', function () {
    run('cd {{release_path}} && composer install --no-dev --prefer-dist --optimize-autoloader');
});

// Laravel migrate
task('deploy:migrate', function () {
    run('cd {{release_path}} && php artisan migrate --force');
});

// Laravel clear caches (run after asset build)
task('laravel:clear_caches', function () {
    run('cd {{release_path}} && php artisan config:clear');
    run('cd {{release_path}} && php artisan route:clear');
    run('cd {{release_path}} && php artisan view:clear');
    run('cd {{release_path}} && php artisan optimize:clear');
});

// Laravel rebuild caches (now safe)
task('laravel:rebuild_caches', function () {
    run('cd {{release_path}} && php artisan config:cache');
    run('cd {{release_path}} && php artisan route:cache');
    run('cd {{release_path}} && php artisan view:cache');
    run('cd {{release_path}} && php artisan event:cache');
});

// Set permissions
task('deploy:permissions', function () {
    run('cd {{release_path}} && chown -R www-data:www-data storage bootstrap/cache');
});

// Restart Supervisor
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
after('deploy:symlink', 'laravel:rebuild_caches');
after('deploy:symlink', 'supervisor:restart');
after('deploy:symlink', 'apache:reload');

after('deploy:failed', 'deploy:unlock');
