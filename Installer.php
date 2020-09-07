<?php

namespace SoosyzeExtension\Matomo;

class Installer extends \SoosyzeCore\System\Migration
{
    public function getDir()
    {
        return __DIR__;
    }
    
    public function boot()
    {
        $this->loadTranslation('fr', __DIR__ . '/Lang/fr/config.json');
    }
    
    public function install(\Psr\Container\ContainerInterface $ci)
    {
        $ci->config()
            ->set('settings.analytics_url', '')
            ->set('settings.analytics_id', '')
            ->set('settings.analytics_visibility_pages', false)
            ->set('settings.analytics_pages', 'admin/%' . "\n" . 'user/%')
            ->set('settings.analytics_visibility_roles', false)
            ->set('settings.analytics_roles', '1');
    }

    public function uninstall(\Psr\Container\ContainerInterface $ci)
    {
        $ci->config()
            ->del('settings.analytics_url')
            ->del('settings.analytics_id')
            ->del('settings.analytics_visibility_pages')
            ->del('settings.analytics_pages')
            ->del('settings.analytics_visibility_roles')
            ->del('settings.analytics_roles');
    }

    public function hookInstall(\Psr\Container\ContainerInterface $ci)
    {
    }

    public function hookUninstall(\Psr\Container\ContainerInterface $ci)
    {
    }

    public function seeders(\Psr\Container\ContainerInterface $ci)
    {
    }
}
