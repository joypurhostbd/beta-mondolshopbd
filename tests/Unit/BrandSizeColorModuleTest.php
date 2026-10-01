<?php

namespace Tests\Unit;

use App\Models\Brand;
use App\Models\Color;
use App\Models\Size;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Application\Actions\CreateBrandAction;
use Modules\Catalog\Application\Actions\CreateColorAction;
use Modules\Catalog\Application\Actions\CreateSizeAction;
use Modules\Catalog\Application\Actions\GetCatalogAttributesAction;
use Modules\Catalog\Domain\Contracts\AttributeRepositoryInterface;
use Modules\Catalog\Domain\Entities\BrandEntity;
use Modules\Catalog\Domain\Entities\ColorEntity;
use Modules\Catalog\Domain\Entities\SizeEntity;
use Tests\TestCase;

class BrandSizeColorModuleTest extends TestCase
{
    use RefreshDatabase;

    private AttributeRepositoryInterface $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = $this->app->make(AttributeRepositoryInterface::class);
    }

    public function test_attribute_entities_and_dtos(): void
    {
        $brandEntity = new BrandEntity(1, 'Aarong', 'আড়ং', 'aarong', 'aarong.png', 1);
        $this->assertEquals('Aarong', $brandEntity->getName());
        $this->assertEquals('আড়ং', $brandEntity->getNameBn());

        $sizeEntity = new SizeEntity(2, 'XL', 'xl', 1);
        $this->assertEquals('XL', $sizeEntity->getSizeName());

        $colorEntity = new ColorEntity(3, 'Navy Blue', '#000080', 'navy-blue', 1);
        $this->assertEquals('Navy Blue', $colorEntity->getColorName());
        $this->assertEquals('#000080', $colorEntity->getColor());
    }

    public function test_attribute_actions_create(): void
    {
        $brandAction = new CreateBrandAction($this->repo);
        $brandDto = $brandAction->execute(['name' => 'Cats Eye', 'name_bn' => 'ক্যাটস আই']);
        $this->assertEquals('Cats Eye', $brandDto->name);
        $this->assertDatabaseHas('brands', ['name' => 'Cats Eye']);

        $sizeAction = new CreateSizeAction($this->repo);
        $sizeDto = $sizeAction->execute('XXL');
        $this->assertEquals('XXL', $sizeDto->sizeName);
        $this->assertDatabaseHas('sizes', ['sizeName' => 'XXL']);

        $colorAction = new CreateColorAction($this->repo);
        $colorDto = $colorAction->execute('Crimson Red', '#DC143C');
        $this->assertEquals('Crimson Red', $colorDto->colorName);
        $this->assertDatabaseHas('colors', ['colorName' => 'Crimson Red']);
    }

    public function test_get_catalog_attributes_action(): void
    {
        Brand::create(['name' => 'Apex', 'name_bn' => 'এপেক্স', 'slug' => 'apex', 'status' => 1]);
        Size::create(['sizeName' => 'M', 'slug' => 'm', 'status' => 1]);
        Color::create(['colorName' => 'Black', 'color' => '#000000', 'slug' => 'black', 'status' => 1]);

        $action = new GetCatalogAttributesAction($this->repo);
        $attributes = $action->execute();

        $this->assertNotEmpty($attributes['brands']);
        $this->assertNotEmpty($attributes['sizes']);
        $this->assertNotEmpty($attributes['colors']);
        $this->assertEquals('Apex', $attributes['brands'][0]->name);
        $this->assertEquals('M', $attributes['sizes'][0]->sizeName);
        $this->assertEquals('Black', $attributes['colors'][0]->colorName);
    }
}