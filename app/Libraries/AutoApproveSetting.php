<?php

namespace App\Libraries;

class AutoApproveSetting
{
    private string $filePath;

    public function __construct()
    {
        $this->filePath = WRITEPATH . 'settings/auto_approve.json';
    }

    public function isEnabled(): bool
    {
        $data = $this->read();

        return (bool) ($data['enabled'] ?? true);
    }

    public function getState(): array
    {
        $data = $this->read();

        return [
            'enabled' => (bool) ($data['enabled'] ?? true),
            'updated_at' => $data['updated_at'] ?? null,
        ];
    }

    public function setEnabled(bool $enabled): array
    {
        $directory = dirname($this->filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $data = [
            'enabled' => $enabled,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        file_put_contents($this->filePath, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);

        return $data;
    }

    private function read(): array
    {
        if (!is_file($this->filePath)) {
            return [
                'enabled' => true,
                'updated_at' => null,
            ];
        }

        $content = file_get_contents($this->filePath);
        $decoded = json_decode($content ?: '', true);

        if (!is_array($decoded)) {
            return [
                'enabled' => true,
                'updated_at' => null,
            ];
        }

        return $decoded;
    }
}