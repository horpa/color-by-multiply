<?php

declare(strict_types=1);

namespace Infrastructure;

final class WorksheetRepository
{
    private string $storageDir;

    public function __construct(?string $storageDir = null)
    {
        $this->storageDir = $storageDir ?? APP_ROOT . '/storage/worksheets';
    }

    /** @param array{grid: array, exercises: array, palette: array, previewImageData: string, questionMode?: string} $worksheet */
    public function save(array $worksheet): string
    {
        $this->ensureStorageDir();

        $id = bin2hex(random_bytes(8));
        $exercises = $worksheet['exercises'] ?? [];
        $payload = [
            'id' => $id,
            'createdAt' => gmdate('c'),
            'questionMode' => $worksheet['questionMode'] ?? 'mixed',
            'exerciseCount' => count($exercises),
            'grid' => $worksheet['grid'],
            'palette' => $worksheet['palette'],
            'exercises' => $exercises,
        ];

        $jsonPath = $this->jsonPath($id);
        $tempPath = $jsonPath . '.tmp';
        $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        if (file_put_contents($tempPath, $encoded, LOCK_EX) === false) {
            throw new RuntimeException('Could not save worksheet.');
        }

        if (!rename($tempPath, $jsonPath)) {
            @unlink($tempPath);
            throw new RuntimeException('Could not save worksheet.');
        }

        $previewPath = $this->previewPath($id);
        $previewData = $worksheet['previewImageData'] ?? '';

        if ($previewData !== '') {
            file_put_contents($previewPath, $previewData, LOCK_EX);
        }

        return $id;
    }

    /** @return array<string, mixed>|null */
    public function find(string $id): ?array
    {
        if (!$this->isValidId($id)) {
            return null;
        }

        $jsonPath = $this->jsonPath($id);

        if (!is_file($jsonPath)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($jsonPath), true);

        if (!is_array($decoded)) {
            return null;
        }

        $previewPath = $this->previewPath($id);
        $decoded['previewImageData'] = is_file($previewPath)
            ? (string) file_get_contents($previewPath)
            : '';

        return $this->toResult($decoded);
    }

    /** @return list<array{id: string, createdAt: string, exerciseCount: int}> */
    public function listSummaries(): array
    {
        $this->ensureStorageDir();
        $summaries = [];

        foreach (glob($this->storageDir . '/*.json') ?: [] as $jsonPath) {
            $decoded = json_decode((string) file_get_contents($jsonPath), true);

            if (!is_array($decoded) || empty($decoded['id'])) {
                continue;
            }

            $summaries[] = [
                'id' => (string) $decoded['id'],
                'createdAt' => (string) ($decoded['createdAt'] ?? ''),
                'exerciseCount' => (int) ($decoded['exerciseCount'] ?? count($decoded['exercises'] ?? [])),
            ];
        }

        usort($summaries, static function (array $left, array $right): int {
            return strcmp($right['createdAt'], $left['createdAt']);
        });

        return $summaries;
    }

    public function delete(string $id): bool
    {
        if (!$this->isValidId($id)) {
            return false;
        }

        $deleted = true;
        $jsonPath = $this->jsonPath($id);
        $previewPath = $this->previewPath($id);

        if (is_file($jsonPath) && !unlink($jsonPath)) {
            $deleted = false;
        }

        if (is_file($previewPath) && !unlink($previewPath)) {
            $deleted = false;
        }

        return $deleted && !is_file($jsonPath);
    }

    public function previewExists(string $id): bool
    {
        return $this->isValidId($id) && is_file($this->previewPath($id));
    }

    /** @return string|false */
    public function readPreview(string $id)
    {
        if (!$this->isValidId($id)) {
            return false;
        }

        $previewPath = $this->previewPath($id);

        return is_file($previewPath) ? file_get_contents($previewPath) : false;
    }

    private function ensureStorageDir(): void
    {
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    private function isValidId(string $id): bool
    {
        return (bool) preg_match('/^[a-f0-9]{16}$/', $id);
    }

    private function jsonPath(string $id): string
    {
        return $this->storageDir . '/' . $id . '.json';
    }

    private function previewPath(string $id): string
    {
        return $this->storageDir . '/' . $id . '.png';
    }

    /** @param array<string, mixed> $stored */
    private function toResult(array $stored): array
    {
        return [
            'grid' => $stored['grid'] ?? [],
            'exercises' => $stored['exercises'] ?? [],
            'palette' => $stored['palette'] ?? [],
            'previewImageData' => $stored['previewImageData'] ?? '',
            'questionMode' => $stored['questionMode'] ?? 'mixed',
            'createdAt' => $stored['createdAt'] ?? '',
            'id' => $stored['id'] ?? '',
        ];
    }
}
