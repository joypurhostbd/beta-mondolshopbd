<?php

namespace Modules\Setting\Application\Services;

use App\Models\GoogleTagManager;
use App\Models\TagManagerEventConfig;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Setting\Application\DTOs\TagManagerDTO;
use Modules\Setting\Domain\Enums\TrackingEventEnum;

class TagManagerService
{
    public const CACHE_KEY_ACTIVE = 'active_google_tag_managers';
    public const CACHE_TTL = 86400; // 24 hours

    /**
     * Get all Tag Manager records with their event configurations.
     */
    public function getAll(): Collection
    {
        return GoogleTagManager::with('eventConfigs')->orderBy('id', 'DESC')->get();
    }

    /**
     * Get active Tag Manager records with event configurations and caching.
     */
    public function getActiveCached(): Collection
    {
        return Cache::remember(self::CACHE_KEY_ACTIVE, self::CACHE_TTL, function () {
            return GoogleTagManager::with('eventConfigs')->active()->get();
        });
    }

    /**
     * Find Tag Manager by ID with event configurations.
     */
    public function getById(int $id): GoogleTagManager
    {
        return GoogleTagManager::with('eventConfigs')->findOrFail($id);
    }

    /**
     * Create a new Tag Manager record and seed default event configs.
     */
    public function create(TagManagerDTO $dto): GoogleTagManager
    {
        $tag = GoogleTagManager::create([
            'title' => $dto->title,
            'code' => $dto->code,
            'status' => $dto->status,
            'description' => $dto->description,
            'is_server_side' => $dto->isServerSide ? 1 : 0,
            'server_container_url' => $dto->serverContainerUrl,
            'measurement_id' => $dto->measurementId,
            'api_secret' => $dto->apiSecret,
            'custom_loader_domain' => $dto->customLoaderDomain ? 1 : 0,
        ]);

        $this->syncEventConfigs($tag->id, $dto->eventConfigs);

        $this->clearCache();

        return $tag->load('eventConfigs');
    }

    /**
     * Update an existing Tag Manager record and sync event configs.
     */
    public function update(int $id, TagManagerDTO $dto): GoogleTagManager
    {
        $tag = $this->getById($id);

        $tag->update([
            'title' => $dto->title,
            'code' => $dto->code,
            'status' => $dto->status,
            'description' => $dto->description,
            'is_server_side' => $dto->isServerSide ? 1 : 0,
            'server_container_url' => $dto->serverContainerUrl,
            'measurement_id' => $dto->measurementId,
            'api_secret' => $dto->apiSecret,
            'custom_loader_domain' => $dto->customLoaderDomain ? 1 : 0,
        ]);

        if (!empty($dto->eventConfigs)) {
            $this->syncEventConfigs($tag->id, $dto->eventConfigs);
        }

        $this->clearCache();

        return $tag->load('eventConfigs');
    }

    /**
     * Sync event configurations for a Tag Manager container.
     */
    public function syncEventConfigs(int $tagManagerId, array $eventsData): void
    {
        $standardEvents = TrackingEventEnum::cases();

        foreach ($standardEvents as $eventEnum) {
            $key = $eventEnum->value;
            $eventInput = $eventsData[$key] ?? [];

            // If input exists for this event, use input; otherwise use enum defaults
            $isWeb = isset($eventInput['is_web_enabled']) ? (bool) $eventInput['is_web_enabled'] : true;
            $isServer = isset($eventInput['is_server_enabled']) ? (bool) $eventInput['is_server_enabled'] : true;
            $customName = !empty($eventInput['custom_event_name']) ? trim((string) $eventInput['custom_event_name']) : $eventEnum->ga4EventName();
            $params = isset($eventInput['parameters']) && is_array($eventInput['parameters'])
                ? $eventInput['parameters']
                : $eventEnum->defaultParameters();

            TagManagerEventConfig::updateOrCreate(
                [
                    'tag_manager_id' => $tagManagerId,
                    'event_key' => $key,
                ],
                [
                    'is_web_enabled' => $isWeb,
                    'is_server_enabled' => $isServer,
                    'custom_event_name' => $customName,
                    'parameters' => $params,
                ]
            );
        }
    }

    /**
     * Toggle status of a Tag Manager record.
     */
    public function setStatus(int $id, int $status): GoogleTagManager
    {
        $tag = $this->getById($id);
        $tag->status = $status === 1 ? 1 : 0;
        $tag->save();

        $this->clearCache();

        return $tag;
    }

    /**
     * Delete a Tag Manager record.
     */
    public function delete(int $id): bool
    {
        $tag = $this->getById($id);
        $deleted = (bool) $tag->delete();

        $this->clearCache();

        return $deleted;
    }

    /**
     * Clear active GTM cache.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_ACTIVE);
    }
}
