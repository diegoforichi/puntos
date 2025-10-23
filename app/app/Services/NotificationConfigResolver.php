<?php

namespace App\Services;

use App\Models\Configuracion;
use App\Models\SystemConfig;
use App\Models\Tenant;

class NotificationConfigResolver
{
    public function resolveWhatsAppConfig(?Tenant $tenant): array
    {
        $global = SystemConfig::getWhatsAppConfig();

        if (!$tenant || !$tenant->allow_custom_whatsapp) {
            return $global;
        }

        $custom = Configuracion::getCustomWhatsAppConfig();

        if (($custom['activo'] ?? false) && !empty($custom['url']) && !empty($custom['token'])) {
            return [
                'activo' => true,
                'url' => $custom['url'],
                'token' => $custom['token'],
            ];
        }

        return $global;
    }

    public function resolveEmailConfig(?Tenant $tenant): array
    {
        $global = SystemConfig::getEmailConfig();

        if (!$tenant || !$tenant->allow_custom_email) {
            return $global;
        }

        $custom = Configuracion::getCustomEmailConfig();

        $required = ['host', 'port', 'username', 'password', 'from_address', 'from_name'];
        $hasAll = true;
        foreach ($required as $key) {
            if (empty($custom[$key])) {
                $hasAll = false;
                break;
            }
        }

        if ($hasAll) {
            return array_merge($global, $custom);
        }

        return $global;
    }
}

