<?php

namespace App\Services;

use App\Models\Configuracion;
use App\Models\SystemConfig;
use App\Models\Tenant;

class NotificationConfigResolver
{
    public function resolveWhatsAppConfig(?Tenant $tenant): array
    {
        $global = $this->normalizeWhatsAppConfig(SystemConfig::getWhatsAppConfig());
        $global['source'] = 'global';

        if (! $tenant || ! $tenant->allow_custom_whatsapp) {
            return $global;
        }

        $custom = $this->normalizeWhatsAppConfig(Configuracion::getCustomWhatsAppConfig());

        if (! ($custom['usar_canal'] ?? false)) {
            return array_merge($global, ['usar_canal' => false, 'activo' => false, 'source' => 'tenant']);
        }

        if (($custom['activo'] ?? false) && ! empty($custom['url']) && ! empty($custom['token'])) {
            $custom['source'] = 'tenant';

            return $custom;
        }

        return $global;
    }

    public function resolveEmailConfig(?Tenant $tenant): array
    {
        $global = $this->normalizeEmailConfig(SystemConfig::getEmailConfig());
        $global['source'] = 'global';

        if (! $tenant || ! $tenant->allow_custom_email) {
            return $global;
        }

        $custom = $this->normalizeEmailConfig(Configuracion::getCustomEmailConfig());

        if (! ($custom['usar_canal'] ?? false)) {
            return array_merge($global, ['usar_canal' => false, 'activo' => false, 'source' => 'tenant']);
        }

        foreach (['host', 'port', 'username', 'password', 'from_address', 'from_name'] as $key) {
            if (empty($custom[$key])) {
                return array_merge($global, ['usar_canal' => true, 'activo' => false, 'source' => 'tenant']);
            }
        }

        if (! ($custom['activo'] ?? false)) {
            return array_merge($global, ['usar_canal' => true, 'activo' => false, 'source' => 'tenant']);
        }

        return array_merge($global, $custom, ['usar_canal' => true, 'activo' => true, 'source' => 'tenant']);
    }

    private function normalizeEmailConfig(array $config): array
    {
        return [
            'usar_canal' => $config['usar_canal'] ?? true,
            'activo' => $config['activo'] ?? true,
            'host' => $config['host'] ?? ($config['smtp_host'] ?? null),
            'port' => $config['port'] ?? ($config['smtp_port'] ?? null),
            'username' => $config['username'] ?? ($config['smtp_user'] ?? null),
            'password' => $config['password'] ?? ($config['smtp_pass'] ?? null),
            'encryption' => $config['encryption'] ?? ($config['smtp_encryption'] ?? null),
            'from_address' => $config['from_address'] ?? null,
            'from_name' => $config['from_name'] ?? null,
        ];
    }

    private function normalizeWhatsAppConfig(array $config): array
    {
        return [
            'usar_canal' => $config['usar_canal'] ?? ($config['activo'] ?? true),
            'activo' => $config['activo'] ?? false,
            'url' => $config['url'] ?? null,
            'token' => $config['token'] ?? null,
            'codigo_pais' => $config['codigo_pais'] ?? '+598',
        ];
    }
}
