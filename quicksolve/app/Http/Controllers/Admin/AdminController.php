<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CategoryType;
use App\Enums\PurchaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveCategoryRequest;
use App\Http\Requests\Admin\SaveTemplateRequest;
use App\Http\Requests\Admin\SaveToolRequest;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Purchase;
use App\Models\Template;
use App\Models\Tool;
use App\Models\UsageEvent;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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
            ],
        ]);
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

    public function purchases(): Response
    {
        return Inertia::render('admin/Purchases', [
            'purchases' => Purchase::query()->with(['user:id,name,email', 'template:id,name'])->latest()->paginate(20),
        ]);
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
