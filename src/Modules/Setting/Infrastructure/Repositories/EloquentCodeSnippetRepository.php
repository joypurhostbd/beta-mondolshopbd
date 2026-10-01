<?php

namespace Modules\Setting\Infrastructure\Repositories;

use App\Models\CodeSnippet;
use Modules\Setting\Domain\Contracts\CodeSnippetRepositoryInterface;
use Modules\Setting\Domain\Entities\CodeSnippetEntity;
use Modules\Setting\Domain\Enums\SnippetDeviceEnum;
use Modules\Setting\Domain\Enums\SnippetLocationEnum;
use Modules\Setting\Domain\Enums\SnippetTypeEnum;
use Throwable;

class EloquentCodeSnippetRepository implements CodeSnippetRepositoryInterface
{
    public function getAll(): array
    {
        try {
            return CodeSnippet::ordered()
                ->get()
                ->map(fn($item) => $this->toEntity($item))
                ->toArray();
        } catch (Throwable) {
            return [];
        }
    }

    public function getActive(): array
    {
        try {
            return CodeSnippet::active()
                ->ordered()
                ->get()
                ->map(fn($item) => $this->toEntity($item))
                ->toArray();
        } catch (Throwable) {
            return [];
        }
    }

    public function findById(int $id): ?CodeSnippetEntity
    {
        try {
            $model = CodeSnippet::find($id);
            return $model ? $this->toEntity($model) : null;
        } catch (Throwable) {
            return null;
        }
    }

    public function save(CodeSnippetEntity $entity): CodeSnippetEntity
    {
        $model = $entity->id ? CodeSnippet::find($entity->id) : new CodeSnippet();
        if (!$model) {
            $model = new CodeSnippet();
        }

        $model->title = $entity->title;
        $model->type = $entity->type->value;
        $model->location = $entity->location->value;
        $model->code = $entity->code;
        $model->status = $entity->status;
        $model->priority = $entity->priority;
        $model->device_target = $entity->deviceTarget->value;
        $model->target_pages = $entity->targetPages;
        $model->custom_page_urls = $entity->customPageUrls;
        $model->auth_condition = $entity->authCondition;
        $model->description = $entity->description;
        $model->save();

        return $this->toEntity($model);
    }

    public function delete(int $id): bool
    {
        try {
            $model = CodeSnippet::find($id);
            return $model ? (bool) $model->delete() : false;
        } catch (Throwable) {
            return false;
        }
    }

    public function toggleStatus(int $id): bool
    {
        try {
            $model = CodeSnippet::find($id);
            if (!$model) {
                return false;
            }
            $model->status = !$model->status;
            return $model->save();
        } catch (Throwable) {
            return false;
        }
    }

    private function toEntity(CodeSnippet $model): CodeSnippetEntity
    {
        return new CodeSnippetEntity(
            id: $model->id,
            title: $model->title,
            type: SnippetTypeEnum::tryFrom($model->type) ?? SnippetTypeEnum::HTML,
            location: SnippetLocationEnum::tryFrom($model->location) ?? SnippetLocationEnum::HEAD,
            code: $model->code ?? '',
            status: (bool) $model->status,
            priority: (int) ($model->priority ?? 10),
            deviceTarget: SnippetDeviceEnum::tryFrom($model->device_target) ?? SnippetDeviceEnum::ALL,
            targetPages: $model->target_pages ?? 'all',
            customPageUrls: $model->custom_page_urls,
            authCondition: $model->auth_condition ?? 'all',
            description: $model->description,
            createdAt: $model->created_at?->toIso8601String(),
            updatedAt: $model->updated_at?->toIso8601String()
        );
    }
}
