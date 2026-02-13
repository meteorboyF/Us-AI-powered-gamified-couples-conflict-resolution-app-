<?php

namespace App\Services;

class WorldCatalogService
{
    public function worldTypes(): array
    {
        return config('world.world_types', []);
    }

    public function allItems(): array
    {
        return config('world.catalog', []);
    }

    public function getItem(string $itemKey): ?array
    {
        return $this->allItems()[$itemKey] ?? null;
    }

    public function itemsForWorldType(string $worldType): array
    {
        return array_filter(
            $this->allItems(),
            fn (array $item) => in_array($worldType, $item['world_types'] ?? [], true)
        );
    }

    public function starterItemsForWorldType(string $worldType): array
    {
        return array_filter(
            $this->itemsForWorldType($worldType),
            fn (array $item) => (bool) ($item['starter'] ?? false)
        );
    }
}
