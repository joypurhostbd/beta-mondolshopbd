<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Childcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Application\Actions\CreateCategoryAction;
use Modules\Catalog\Application\Actions\DeleteCategoryAction;
use Modules\Catalog\Application\Actions\GetCategoryTreeAction;
use Modules\Catalog\Application\Actions\UpdateCategoryAction;
use Modules\Catalog\Application\DTOs\CategoryDTO;
use Modules\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use Modules\Catalog\Domain\Entities\CategoryEntity;
use Tests\TestCase;

class CategoryModuleTest extends TestCase
{
    use RefreshDatabase;

    private CategoryRepositoryInterface $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = $this->app->make(CategoryRepositoryInterface::class);
    }

    public function test_category_entity_and_dto(): void
    {
        $entity = new CategoryEntity(
            id: 5,
            name: 'Electronics',
            slug: 'electronics',
            parentId: 0,
            status: 1,
            frontView: 1
        );

        $this->assertEquals(5, $entity->getId());
        $this->assertEquals('Electronics', $entity->getName());
        $this->assertTrue($entity->isRootCategory());
        $this->assertTrue($entity->isFrontView());

        $dto = CategoryDTO::fromEntity($entity);
        $this->assertEquals('electronics', $dto->slug);
    }

    public function test_category_actions_crud_lifecycle(): void
    {
        $createAction = new CreateCategoryAction($this->repo);
        $dto = $createAction->execute([
            'name' => 'Men Collection',
            'status' => 1,
            'front_view' => 1,
        ]);

        $this->assertEquals('Men Collection', $dto->name);
        $this->assertDatabaseHas('categories', ['name' => 'Men Collection']);

        $updateAction = new UpdateCategoryAction($this->repo);
        $updatedDto = $updateAction->execute($dto->id, ['name' => 'Men Premium Collection']);
        $this->assertEquals('Men Premium Collection', $updatedDto->name);

        $deleteAction = new DeleteCategoryAction($this->repo);
        $deleted = $deleteAction->execute($dto->id);
        $this->assertTrue($deleted);
        $this->assertNull(Category::find($dto->id));
    }

    public function test_get_category_tree_action(): void
    {
        $cat = Category::create([
            'name' => 'Clothing',
            'slug' => 'clothing',
            'parent_id' => 0,
            'status' => 1,
        ]);

        $sub = Subcategory::create([
            'subcategoryName' => 'T-Shirts',
            'slug' => 't-shirts',
            'category_id' => $cat->id,
            'status' => 1,
        ]);

        Childcategory::create([
            'childcategoryName' => 'V-Neck',
            'slug' => 'v-neck',
            'category_id' => $cat->id,
            'subcategory_id' => $sub->id,
            'status' => 1,
        ]);

        $treeAction = new GetCategoryTreeAction($this->repo);
        $tree = $treeAction->execute();

        $this->assertNotEmpty($tree);
        $this->assertEquals('Clothing', $tree[0]['name']);
        $this->assertNotEmpty($tree[0]['subcategories']);
        $this->assertEquals('T-Shirts', $tree[0]['subcategories'][0]['name']);
        $this->assertEquals('V-Neck', $tree[0]['subcategories'][0]['children'][0]['name']);
    }
}