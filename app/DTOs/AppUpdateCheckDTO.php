<?php

namespace App\DTOs;

use Illuminate\Http\Request;

readonly class AppUpdateCheckDTO
{
    public function __construct(
        public bool $updaterEnabled,
        public ?string $provider,
        public ?array $providerConfig,
    ) {
    }

    public static function fromConfig(): self
    {
        $provider = config('nativephp.updater.default');

        return new self(
            updaterEnabled: (bool) config('nativephp.updater.enabled'),
            provider: $provider,
            providerConfig: $provider ? config("nativephp.updater.providers.{$provider}") : null,
        );
    }

    public function isValid(): bool
    {
        return $this->updaterEnabled && $this->provider && $this->providerConfig;
    }

    public function getValidationError(): ?string
    {
        if (!$this->updaterEnabled) {
            return 'El actualizador está deshabilitado.';
        }

        if (!$this->provider || !$this->providerConfig) {
            return 'No hay un proveedor de actualizaciones válido configurado (revisa NATIVEPHP_UPDATER_PROVIDER).';
        }

        return null;
    }
}
