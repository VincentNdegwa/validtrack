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


// Install Composer dependencies
task('deploy:composer', function () {
    run('cd {{release_path}} && composer install --no-dev --prefer-dist --optimize-autoloader');
});

// Run Laravel migrations
task('deploy:migrate', function () {
    run('cd {{release_path}} && php artisan migrate --force');
});

// Set permissions
task('deploy:permissions', function () {
    run('cd {{release_path}} && chown -R www-data:www-data storage bootstrap/cache');
});

// Clear config cache after caching (fixes unexpected config issues)
after('artisan:config:cache', 'artisan:config:clear');

// Build frontend assets using pnpm
task('build:assets', function () {
    writeln('<info>Checking Node, PNPM versions...</info>');
    run('node -v');
    run('pnpm -v');

    writeln('<info>Installing dependencies with pnpm...</info>');
    run('cd {{release_path}} && pnpm install --frozen-lockfile >> build.log 2>&1');

    writeln('<info>Building assets with pnpm...</info>');
    run('cd {{release_path}} && NODE_OPTIONS="--max-old-space-size=2048" pnpm build >> build.log 2>&1');
});

// Hook build task before switching symlink
before('deploy:symlink', 'build:assets');

// Post-deploy actions
after('deploy:symlink', 'deploy:permissions');
after('deploy:symlink', 'deploy:migrate');
