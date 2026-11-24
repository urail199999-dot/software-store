<?php

namespace Tests\Feature;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Filament\Resources\Products\Pages\ManageProducts;
use App\Models\Category;
use App\Models\LicenseKey;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    private function loginAdmin(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $this->actingAs($admin);
    }

    private function image(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9Z3b8AAAAASUVORK5CYII='));
    }

    public function test_catalog_navigates_hierarchy_and_counts_descendant_products(): void
    {
        $root = Category::factory()->create(['name' => 'Игры']);
        $child = Category::factory()->create(['parent_id' => $root->id, 'name' => 'Подраздел']);
        $product = Product::factory()->create(['category_id' => $child->id, 'name' => 'Тестовая программа', 'image' => 'products/cover.png', 'gallery' => ['products/gallery/screen.png']]);
        $this->get('/catalog')->assertOk()->assertSee('Игры')->assertSee(route('category.show', $root), false);
        $this->get(route('category.show', $root))->assertOk()->assertSee('Подраздел')->assertSee('Тестовая программа');
        $this->get(route('category.show', $child))->assertOk()->assertSee('Игры')->assertSee('Тестовая программа');
        $this->get(route('product.show', $product))->assertOk()->assertSee('/storage/products/cover.png', false)->assertSee('/storage/products/gallery/screen.png', false)->assertDontSee('software.zip');
    }

    public function test_hidden_parent_hides_descendants_and_prevents_checkout(): void
    {
        $root = Category::factory()->create(['is_active' => false]);
        $child = Category::factory()->create(['parent_id' => $root->id]);
        $product = Product::factory()->create(['category_id' => $child->id]);
        LicenseKey::create(['product_id' => $product->id, 'key' => 'SAMPLE-KEY']);
        $this->get('/catalog')->assertDontSee($child->name)->assertDontSee($product->name);
        $this->get(route('category.show', $child))->assertNotFound();
        $this->get(route('product.show', $product))->assertNotFound();
        $this->post(route('checkout', $product), ['email' => 'buyer@example.com'])->assertNotFound();
    }

    public function test_hierarchy_rejects_cycles(): void
    {
        $root = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $root->id]);
        $this->expectException(ValidationException::class);
        $root->update(['parent_id' => $child->id]);
    }

    public function test_nonempty_category_cannot_be_deleted(): void
    {
        $root = Category::factory()->create();
        Category::factory()->create(['parent_id' => $root->id]);
        $this->assertFalse(CategoryResource::canDelete($root));
        $empty = Category::factory()->create();
        $this->assertTrue(CategoryResource::canDelete($empty));
        Product::factory()->create(['category_id' => $empty->id]);
        $this->assertFalse(CategoryResource::canDelete($empty));
    }

    public function test_admin_creates_category_and_subcategory_with_cover(): void
    {
        Storage::fake('public');
        $this->loginAdmin();
        Livewire::test(ManageCategories::class)->callAction('create', data: ['name' => 'Раздел', 'slug' => 'section', 'is_active' => true, 'sort_order' => 0, 'image' => [$this->image('cover.png')]])->assertHasNoFormErrors();
        $root = Category::where('slug', 'section')->firstOrFail();
        $this->assertNotNull($root->image);
        Storage::disk('public')->assertExists($root->image);
        Livewire::test(ManageCategories::class)->callAction('create', data: ['name' => 'Подраздел', 'slug' => 'subsection', 'parent_id' => $root->id, 'is_active' => true, 'sort_order' => 1])->assertHasNoFormErrors();
        $this->assertDatabaseHas('categories', ['slug' => 'subsection', 'parent_id' => $root->id]);
    }

    public function test_admin_creates_product_with_image_gallery_and_category(): void
    {
        Storage::fake('public');
        $this->loginAdmin();
        $category = Category::factory()->create();
        Livewire::test(ManageProducts::class)->callAction('create', data: ['name' => 'Новая программа', 'slug' => 'new-program', 'category_id' => $category->id, 'description' => 'Описание', 'price' => '250,00', 'download_url' => 'https://example.com/download.zip', 'is_active' => true, 'sort_order' => 0, 'image' => [$this->image('cover.png')], 'gallery' => [$this->image('screen.png')]])->assertHasNoFormErrors();
        $product = Product::where('slug', 'new-program')->firstOrFail();
        $this->assertEquals($category->id, $product->category_id);
        $this->assertEquals(25000, $product->price);
        Storage::disk('public')->assertExists($product->image);
        $this->assertCount(1, $product->gallery);
        Storage::disk('public')->assertExists($product->gallery[0]);
    }

    public function test_nonimages_are_rejected_and_categories_are_admin_only(): void
    {
        Storage::fake('public');
        $this->loginAdmin();
        Livewire::test(ManageCategories::class)->callAction('create', data: ['name' => 'Раздел', 'slug' => 'bad-file', 'sort_order' => 0, 'image' => [UploadedFile::fake()->createWithContent('file.php', '<?php echo 1;')]])->assertHasFormErrors();
        $this->assertDatabaseMissing('categories', ['slug' => 'bad-file']);
        $this->actingAs(User::factory()->create())->get('/admin/categories')->assertForbidden();
    }

    public function test_admin_enters_rubles_with_optional_kopecks_and_existing_prices_are_preserved(): void
    {
        $this->loginAdmin();
        $product = Product::factory()->create(['price' => 19990]);
        $edit = TestAction::make('edit')->table($product);
        Livewire::test(ManageProducts::class)->mountAction($edit)
            ->assertSchemaStateSet(['price' => '199,90'])->callMountedAction()->assertHasNoFormErrors();
        $this->assertEquals(19990, $product->fresh()->price);
        foreach (['199' => 19900, '199,90' => 19990, '199.9' => 19990, '0,01' => 1, '1000000,00' => 100000000] as $input => $expected) {
            Livewire::test(ManageProducts::class)->callAction($edit, data: ['price' => (string) $input])->assertHasNoFormErrors();
            $this->assertEquals($expected, $product->fresh()->price);
        }
    }

    public function test_invalid_ruble_prices_do_not_change_the_product(): void
    {
        $this->loginAdmin();
        $product = Product::factory()->create(['price' => 19990]);
        $edit = TestAction::make('edit')->table($product);
        foreach (['0', '-10', '199,999', 'abc', '1e3', '1000000,01'] as $input) {
            Livewire::test(ManageProducts::class)->callAction($edit, data: ['price' => $input])->assertHasFormErrors(['price']);
            $this->assertEquals(19990, $product->fresh()->price);
        }
    }
}
