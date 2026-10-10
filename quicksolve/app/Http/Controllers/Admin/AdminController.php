<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CategoryType;
use App\Enums\PurchaseStatus;
use App\Http\Controllers\Controller;
use App\Exceptions\BillingNotConfiguredException;
use App\Http\Requests\Admin\SaveBillingSettingsRequest;
use App\Http\Requests\Admin\SaveCategoryRequest;
use App\Http\Requests\Admin\SaveTemplateRequest;
use App\Http\Requests\Admin\SaveToolRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Purchase;
use App\Models\Template;
use App\Models\Tool;
use App\Models\UsageEvent;
use App\Models\User;
use App\Services\Billing\PlanPriceResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Cashier\Subscription;

class AdminController extends Controller
{
    public function dashboard(): Response
    {
        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'users' => User::query()->count(),
                'published_tools' => Tool::query()->published()->count(),
                'published_templates' => Template::query()->published()->count(),
                'paid_purchases' => Purchase::query()->where('status', PurchaseStatus::Paid)->count(),
                'active_subscriptions' => Subscription::query()->where('stripe_status', 'active')->count(),
                'events_30_days' => UsageEvent::query()->where('created_at', '>=', now()->subDays(30))->count(),
                'unread_messages' => ContactMessage::query()->where('is_read', false)->count(),
                'admins' => User::query()->where('is_admin', true)->count(),
            ],
            'billing' => app(PlanPriceResolver::class)->summary(),
        ]);
    }

    public function users(Request $request): Response
    {
        $search = $request->string('q')->toString();
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $search);

        $users = User::query()
            ->when($search !== '', function ($query) use ($escaped) {
                $query->where(function ($inner) use ($escaped) {
                    $inner->where('name', 'like', '%'.$escaped.'%')
                        ->orWhere('email', 'like', '%'.$escaped.'%');
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/Users', [
            'filters' => ['q' => $search],
            'users' => $users,
        ]);
    }

    public function updateUser(UpdateAdminUserRequest $request, User $user): RedirectResponse
    {
        $makeAdmin = $request->boolean('is_admin');

        if (! $makeAdmin && $user->is_admin && User::query()->where('is_admin', true)->count() <= 1) {
            return back()->with('error', 'Keep at least one administrator.');
        }

        $user->forceFill(['is_admin' => $makeAdmin])->save();

        return back()->with('success', $makeAdmin ? 'Administrator access granted.' : 'Administrator access removed.');
    }

    public function billing(PlanPriceResolver $prices): Response
    {
        return Inertia::render('admin/Billing', [
            'billing' => $prices->summary(),
        ]);
    }

    public function updateBilling(SaveBillingSettingsRequest $request, PlanPriceResolver $prices): RedirectResponse
    {
        if ($request->filled('pro_price_id')) {
            $prices->store('pro', $request->string('pro_price_id')->toString());
        }

        if ($request->filled('business_price_id')) {
            $prices->store('business', $request->string('business_price_id')->toString());
        }

        return back()->with('success', 'Stripe price IDs saved.');
    }

    public function syncBilling(PlanPriceResolver $prices): RedirectResponse
    {
        try {
            $prices->ensurePaidPlans();
        } catch (BillingNotConfiguredException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Stripe created or reused the Pro and Business prices.');
    }

    public function contacts(): Response
    {
        return Inertia::render('admin/Contacts', [
            'messages' => ContactMessage::query()->latest()->paginate(20),
        ]);
    }

    public function markContactRead(ContactMessage $message): RedirectResponse
    {
        $message->update(['is_read' => true]);

        return back()->with('success', 'Message marked as read.');
    }

    public function destroyContact(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return back()->with('success', 'Message deleted.');
    }

    public function tools(): Response
    {
        return Inertia::render('admin/Tools', [
            'tools' => Tool::query()->with('category')->orderBy('name')->get(),
            'categories' => Category::query()->where('type', CategoryType::Tool)->orderBy('name')->get(),
        ]);
    }

    public function storeTool(SaveToolRequest $request): RedirectResponse
    {
        Tool::query()->create([
            ...$request->validated(),
            'is_featured' => $request->boolean('is_featured'),
            'is_published' => $request->boolean('is_published'),
        ]);

        return back()->with('success', 'Tool saved.');
    }

    public function updateTool(SaveToolRequest $request, Tool $tool): RedirectResponse
    {
        $tool->update([
            ...$request->validated(),
            'is_featured' => $request->boolean('is_featured'),
            'is_published' => $request->boolean('is_published'),
        ]);

        return back()->with('success', 'Tool updated.');
    }

    public function toggleTool(Tool $tool): RedirectResponse
    {
        $tool->update(['is_published' => ! $tool->is_published]);

        return back()->with('success', $tool->is_published ? 'Tool published.' : 'Tool unpublished.');
    }

    public function destroyTool(Tool $tool): RedirectResponse
    {
        $tool->delete();

        return back()->with('success', 'Tool deleted.');
    }

    public function categories(): Response
    {
        return Inertia::render('admin/Categories', [
            'categories' => Category::query()->orderBy('type')->orderBy('name')->get(),
        ]);
    }

    public function storeCategory(SaveCategoryRequest $request): RedirectResponse
    {
        Category::query()->create($request->validated());

        return back()->with('success', 'Category saved.');
    }

    public function updateCategory(SaveCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return back()->with('success', 'Category updated.');
    }

    public function destroyCategory(Category $category): RedirectResponse
    {
        if ($category->tools()->exists() || $category->templates()->exists()) {
            return back()->with('error', 'Move or delete the tools and templates in this category first.');
        }

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    public function templates(): Response
    {
        return Inertia::render('admin/Templates', [
            'templates' => Template::query()->with('category')->orderBy('name')->get()->map(fn (Template $template) => [
                'id' => $template->id,
                'name' => $template->name,
                'slug' => $template->slug,
                'description' => $template->description,
                'whats_included' => implode("\n", $template->whats_included ?? []),
                'price' => $template->price,
                'currency' => $template->currency,
                'category_id' => $template->category_id,
                'is_featured' => $template->is_featured,
                'is_published' => $template->is_published,
                'has_file' => $template->fileExists(),
            ]),
            'categories' => Category::query()->where('type', CategoryType::Template)->orderBy('name')->get(),
        ]);
    }

    public function storeTemplate(SaveTemplateRequest $request): RedirectResponse
    {
        $data = $this->templatePayload($request);
        $data['private_file_path'] = $request->file('file')->store('templates', 'local');

        if ($request->hasFile('preview')) {
            $data['preview_image'] = '/storage/'.$request->file('preview')->store('template-previews', 'public');
        }

        Template::query()->create($data);

        return back()->with('success', 'Template saved.');
    }

    public function updateTemplate(SaveTemplateRequest $request, Template $template): RedirectResponse
    {
        $data = $this->templatePayload($request);

        if ($request->hasFile('file')) {
            if ($template->private_file_path) {
                Storage::disk('local')->delete($template->private_file_path);
            }

            $data['private_file_path'] = $request->file('file')->store('templates', 'local');
        }

        if ($request->hasFile('preview')) {
            $data['preview_image'] = '/storage/'.$request->file('preview')->store('template-previews', 'public');
        }

        $template->update($data);

        return back()->with('success', 'Template updated.');
    }

    public function toggleTemplate(Template $template): RedirectResponse
    {
        if (! $template->is_published && ! $template->fileExists()) {
            return back()->with('error', 'Upload a file before publishing this template.');
        }

        $template->update(['is_published' => ! $template->is_published]);

        return back()->with('success', $template->is_published ? 'Template published.' : 'Template unpublished.');
    }

    public function destroyTemplate(Template $template): RedirectResponse
    {
        if ($template->purchases()->exists()) {
            return back()->with('error', 'This template has purchases. Unpublish it instead of deleting it.');
        }

        if ($template->private_file_path) {
            Storage::disk('local')->delete($template->private_file_path);
        }

        $template->delete();

        return back()->with('success', 'Template deleted.');
    }

    public function purchases(): Response
    {
        return Inertia::render('admin/Purchases', [
            'purchases' => Purchase::query()->with(['user:id,name,email', 'template:id,name'])->latest()->paginate(20),
        ]);
    }

    public function refundPurchase(Purchase $purchase): RedirectResponse
    {
        if ($purchase->status !== PurchaseStatus::Paid) {
            return back()->with('error', 'Only paid purchases can be marked refunded.');
        }

        $purchase->update(['status' => PurchaseStatus::Refunded]);

        return back()->with('success', 'Purchase marked refunded. This does not send a Stripe refund by itself.');
    }

    public function subscriptions(): Response
    {
        return Inertia::render('admin/Subscriptions', [
            'subscriptions' => Subscription::query()->with('user:id,name,email')->latest()->paginate(20),
        ]);
    }

    public function usage(): Response
    {
        $rows = UsageEvent::query()
            ->select('usage_type', 'tool_slug')
            ->selectRaw('count(*) as total')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('usage_type', 'tool_slug')
            ->orderByDesc('total')
            ->limit(50)
            ->get();

        return Inertia::render('admin/Usage', [
            'rows' => $rows,
            'messages' => ContactMessage::query()->latest()->limit(20)->get(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function templatePayload(SaveTemplateRequest $request): array
    {
        $included = collect(preg_split('/\r\n|\r|\n/', (string) $request->input('whats_included')) ?: [])
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values()
            ->all();

        return [
            'category_id' => $request->integer('category_id'),
            'name' => $request->string('name')->toString(),
            'slug' => Str::slug($request->string('slug')->toString()),
            'description' => $request->string('description')->toString(),
            'whats_included' => $included,
            'price' => $request->integer('price'),
            'currency' => $request->string('currency')->toString(),
            'is_featured' => $request->boolean('is_featured'),
            'is_published' => $request->boolean('is_published'),
        ];
    }
}
