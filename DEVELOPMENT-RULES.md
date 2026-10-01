# DEVELOPMENT-RULES.md — MondolShopBD

> এই ফাইলটি প্রতিটি ডেভেলপার এবং AI এজেন্টকে অনুসরণ করতে হবে।

---

## ১. Controller Rules

```php
// ✅ CORRECT: Thin controller — only orchestrate
class ProductController extends Controller
{
    public function store(StoreProductRequest $request, CreateProductAction $action): RedirectResponse
    {
        $action->execute($request->toDTO());
        return redirect()->route('products.index')->with('success', 'Product created.');
    }
}

// ❌ WRONG: Fat controller with business logic
class ProductController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [...]); // NO
        $slug = Str::slug($request->name); // NO
        $product = Product::create($request->all()); // NO
        // image upload logic... // NO
    }
}
```

**Rules:**
- MAX 15 lines per method
- Only: receive FormRequest → call Service/Action → return response
- NO validation (use FormRequest)
- NO queries (use Repository via Service)
- NO business logic (use Service/Action)
- NO `compact()` (use ViewModel)

---

## ২. Service Rules

```php
// ✅ CORRECT: Service orchestrates business logic
class ProductService
{
    public function __construct(
        private ProductRepository $repository,
        private ImageService $imageService,
    ) {}

    public function createProduct(ProductData $data): Product
    {
        $product = $this->repository->create($data->toArray());
        $this->imageService->uploadMultiple($product, $data->images);
        return $product;
    }
}
```

**Rules:**
- Orchestrates business logic across repositories/services
- NO direct Eloquent queries — use Repository
- NO Facades — inject dependencies via constructor
- Returns domain objects (Models, DTOs), never raw arrays

---

## ৩. Repository Rules

```php
// ✅ CORRECT: Repository handles all data access
class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function getActiveWithImages(): Collection
    {
        return $this->model->where('status', 1)
            ->with('image')
            ->select('id', 'name', 'slug', 'new_price', 'old_price')
            ->get();
    }
}
```

**Rules:**
- ALL Eloquent queries go here
- NO business logic — only data access
- Always use `select()` to limit columns
- Always eager load relations (no N+1)
- Use query scopes for reusable filters

---

## ৪. DTO Rules

```php
// ✅ CORRECT: Typed DTO with spatie/laravel-data
class ProductData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly int $categoryId,
        public readonly float $newPrice,
        public readonly float $purchasePrice,
        public readonly int $stock,
        public readonly string $description,
        public readonly ?string $slug = null,
        public readonly bool $status = true,
        public readonly array $images = [],
        public readonly array $sizes = [],
        public readonly array $colors = [],
    ) {}
}
```

**Rules:**
- All properties typed and readonly
- Created from FormRequest via `::from($request)`
- Immutable — no setters
- Used for data transfer between layers

---

## ৫. FormRequest Rules

```php
// ✅ CORRECT: Dedicated FormRequest
class StoreProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'new_price' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'required|string',
            'image.*' => 'image|mimes:jpg,png,webp|max:2048',
        ];
    }

    public function toDTO(): ProductData
    {
        return ProductData::from($this->validated());
    }
}
```

**Rules:**
- EVERY controller method that accepts input has a FormRequest
- Include `toDTO()` method for DTO creation
- NO validation in controllers

---

## ৬. ViewModel Rules

```php
// ✅ CORRECT: ViewModel prepares view data
class ProductIndexViewModel extends ViewModel
{
    public function __construct(
        private ProductService $service,
    ) {}

    public function products(): LengthAwarePaginator
    {
        return $this->service->getPaginated();
    }

    public function categories(): Collection
    {
        return $this->service->getCategories();
    }
}
```

**Rules:**
- Replaces `compact()` in controllers
- Type-safe view data
- Can cache data internally
- NO queries in Blade files

---

## ৭. Model Rules

```php
// ✅ CORRECT: Clean model
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'category_id', 'new_price',
        'old_price', 'purchase_price', 'stock', 'status',
    ];

    protected $casts = [
        'new_price' => 'decimal:2',
        'status' => 'boolean',
    ];

    // Relations only
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Scopes only
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }
}
```

**Rules:**
- `$fillable` — NEVER `$guarded = []`
- `$casts` for all typed columns
- Relations, Scopes, Accessors ONLY
- NO business logic (no `fraud_check()` in Order model)
- NO HTTP calls

---

## ৮. Enum Rules

```php
// ✅ CORRECT: PHP 8.1+ Enum
enum OrderStatus: string
{
    case Pending = '1';
    case Confirmed = '2';
    case Processing = '3';
    case Shipped = '4';
    case Delivered = '5';
    case Cancelled = '6';
}
```

**Rules:**
- ALL status/type constants as Enums
- NO magic strings or numbers in code
- Use `OrderStatus::Pending->value` for DB storage

---

## ৯. Route Rules

```php
// ✅ CORRECT: Module routes with middleware group
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    Route::resource('products', ProductController::class);
});

// ❌ WRONG: Individual route definitions for each CRUD action
Route::get('products/manage', ...);
Route::get('products/create', ...);
Route::post('products/save', ...);
```

**Rules:**
- Use `Route::resource()` where possible
- Group by middleware + prefix
- Module-specific routes in module's `routes/web.php`

---

## ১০. General Rules

| Rule | Description |
|---|---|
| **No Facades in Services** | Inject dependencies |
| **No queries in Blade** | Use ViewModel |
| **No raw arrays** | Use DTOs or Collections |
| **No magic numbers** | Use Enums or Constants |
| **No `$request->all()`** | Use `$request->validated()` or DTO |
| **No `compact()`** | Use ViewModel |
| **No inline validation** | Use FormRequest |
| **No business logic in Model** | Use Service/Action |
| **No business logic in Controller** | Use Service/Action |
| **Always type-hint** | Return types, parameter types |
| **Always use `select()`** | Never `SELECT *` |
| **Always eager load** | No N+1 queries |
| **Mandatory Migrations** | Zero direct DB edits; always use Laravel migrations or tenant migrations |

---

## ১১. Database Migration Rules

- **Zero Direct DB/Schema Modifications**: কোনো অবস্থাতেই ডাটাবেজে ম্যানুয়ালি, GUI টুল বা কাঁচা SQL দিয়ে কোনো টেবিল, কলাম বা স্কিমা পরিবর্তন করা সম্পূর্ণ নিষিদ্ধ।
- **Always Use Laravel Migrations**: যেকোনো নতুন টেবিল তৈরি, কলাম যোগ, পরিবর্তন বা ডিলিট করার ক্ষেত্রে অবশ্যই Laravel Migration স্ক্রিপ্ট ব্যবহার করতে হবে (`database/migrations/`)।
- **Multi-Tenant / Tenants Migrations**: মাল্টি-টেন্যান্ট আর্কিটেকচারে টেন্যান্ট স্কিমা পরিবর্তনের জন্য সংশ্লিষ্ট `database/migrations/tenant/` বা `database/migrations/tenants/` ডিরেক্টরিতে মাইগ্রেশন প্রস্তুত করতে হবে এবং টেন্যান্ট মাইগ্রেশন কমান্ড দিয়ে রান করতে হবে।
- **Reversible Migrations**: প্রতিটি মাইগ্রেশনে স্পষ্ট `up()` এবং `down()` মেথড থাকতে হবে যাতে প্রয়োজনে নিরাপদে রোলব্যাক করা যায়।

