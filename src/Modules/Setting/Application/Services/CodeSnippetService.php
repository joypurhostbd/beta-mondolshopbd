<?php

namespace Modules\Setting\Application\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Setting\Application\DTOs\CodeSnippetDTO;
use Modules\Setting\Domain\Contracts\CodeSnippetRepositoryInterface;
use Modules\Setting\Domain\Entities\CodeSnippetEntity;
use Modules\Setting\Domain\Enums\SnippetDeviceEnum;
use Modules\Setting\Domain\Enums\SnippetLocationEnum;
use Modules\Setting\Domain\Enums\SnippetTypeEnum;

class CodeSnippetService
{
    public const CACHE_KEY_ACTIVE = 'active_code_snippets';
    public const CACHE_TTL = 86400; // 24 hours

    public function __construct(
        private readonly CodeSnippetRepositoryInterface $repository
    ) {}

    /**
     * @return array<CodeSnippetEntity>
     */
    public function getAll(): array
    {
        return $this->repository->getAll();
    }

    /**
     * @return array<CodeSnippetEntity>
     */
    public function getActiveCached(): array
    {
        return Cache::remember(self::CACHE_KEY_ACTIVE, self::CACHE_TTL, function () {
            return $this->repository->getActive();
        });
    }

    public function getById(int $id): ?CodeSnippetEntity
    {
        return $this->repository->findById($id);
    }

    public function create(CodeSnippetDTO $dto): CodeSnippetEntity
    {
        $entity = new CodeSnippetEntity(
            id: null,
            title: $dto->title,
            type: $dto->type,
            location: $dto->location,
            code: $dto->code,
            status: $dto->status,
            priority: $dto->priority,
            deviceTarget: $dto->deviceTarget,
            targetPages: $dto->targetPages,
            customPageUrls: $dto->customPageUrls,
            authCondition: $dto->authCondition,
            description: $dto->description
        );

        $saved = $this->repository->save($entity);
        $this->clearCache();
        return $saved;
    }

    public function update(int $id, CodeSnippetDTO $dto): CodeSnippetEntity
    {
        $entity = new CodeSnippetEntity(
            id: $id,
            title: $dto->title,
            type: $dto->type,
            location: $dto->location,
            code: $dto->code,
            status: $dto->status,
            priority: $dto->priority,
            deviceTarget: $dto->deviceTarget,
            targetPages: $dto->targetPages,
            customPageUrls: $dto->customPageUrls,
            authCondition: $dto->authCondition,
            description: $dto->description
        );

        $saved = $this->repository->save($entity);
        $this->clearCache();
        return $saved;
    }

    public function delete(int $id): bool
    {
        $result = $this->repository->delete($id);
        $this->clearCache();
        return $result;
    }

    public function toggleStatus(int $id): bool
    {
        $result = $this->repository->toggleStatus($id);
        $this->clearCache();
        return $result;
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_ACTIVE);
    }

    /**
     * Evaluate and return active snippets matching current request context for a given location.
     *
     * @return array<string> Formatted code strings ready for injection
     */
    public function getRenderableSnippets(
        SnippetLocationEnum|string $location,
        string $currentPath = '/',
        ?string $userAgent = null,
        bool $isAuth = false
    ): array {
        $locationValue = $location instanceof SnippetLocationEnum ? $location->value : $location;
        $activeSnippets = $this->getActiveCached();

        $isMobile = $this->isMobileAgent($userAgent ?? request()->userAgent());
        $cleanPath = trim($currentPath, '/');
        $output = [];

        foreach ($activeSnippets as $snippet) {
            if ($snippet->location->value !== $locationValue) {
                continue;
            }

            // 1. Device Filter
            if ($snippet->deviceTarget === SnippetDeviceEnum::DESKTOP && $isMobile) {
                continue;
            }
            if ($snippet->deviceTarget === SnippetDeviceEnum::MOBILE && !$isMobile) {
                continue;
            }

            // 2. Auth Condition Filter
            if ($snippet->authCondition === 'logged_in' && !$isAuth) {
                continue;
            }
            if ($snippet->authCondition === 'guest' && $isAuth) {
                continue;
            }

            // 3. Target Pages Filter
            if (!$this->matchesPageCondition($snippet, $cleanPath)) {
                continue;
            }

            // Format code
            $output[] = $this->formatSnippetCode($snippet);
        }

        return $output;
    }

    private function matchesPageCondition(CodeSnippetEntity $snippet, string $cleanPath): bool
    {
        if ($snippet->targetPages === 'all') {
            return true;
        }

        if ($snippet->targetPages === 'homepage') {
            return $cleanPath === '' || $cleanPath === 'home';
        }

        if ($snippet->targetPages === 'checkout') {
            return Str::contains($cleanPath, ['checkout', 'order']);
        }

        if ($snippet->targetPages === 'thank_you') {
            return Str::contains($cleanPath, ['order-success', 'order_success', 'thank-you', 'order-complete']);
        }

        if ($snippet->targetPages === 'custom' && !empty($snippet->customPageUrls)) {
            $patterns = array_filter(array_map('trim', explode(',', $snippet->customPageUrls)));
            foreach ($patterns as $pattern) {
                $trimmedPattern = trim($pattern, '/');
                if (Str::is($trimmedPattern, $cleanPath)) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    private function isMobileAgent(?string $userAgent): bool
    {
        if (empty($userAgent)) {
            return false;
        }

        return (bool) preg_match('/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i', $userAgent);
    }

    private function formatSnippetCode(CodeSnippetEntity $snippet): string
    {
        $code = trim($snippet->code);
        if ($code === '') {
            return '';
        }

        // Auto-wrap CSS if not already wrapped
        if ($snippet->type === SnippetTypeEnum::CSS && !Str::contains($code, '<style')) {
            return "<!-- Snippet: {$snippet->title} -->\n<style>\n{$code}\n</style>";
        }

        // Auto-wrap JavaScript if not already wrapped
        if ($snippet->type === SnippetTypeEnum::JAVASCRIPT && !Str::contains($code, '<script')) {
            return "<!-- Snippet: {$snippet->title} -->\n<script>\n{$code}\n</script>";
        }

        return "<!-- Snippet: {$snippet->title} -->\n{$code}";
    }
}
