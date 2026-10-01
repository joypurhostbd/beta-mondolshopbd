<?php

namespace Modules\Setting\Domain\Contracts;

use Modules\Setting\Domain\Entities\CodeSnippetEntity;

interface CodeSnippetRepositoryInterface
{
    /**
     * @return array<CodeSnippetEntity>
     */
    public function getAll(): array;

    /**
     * @return array<CodeSnippetEntity>
     */
    public function getActive(): array;

    public function findById(int $id): ?CodeSnippetEntity;

    public function save(CodeSnippetEntity $entity): CodeSnippetEntity;

    public function delete(int $id): bool;

    public function toggleStatus(int $id): bool;
}
