<?php

namespace Database\Seeders;

use App\Enums\AccessType;
use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Template;
use App\Models\Tool;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $toolCategories = [
            ['name' => 'Calculators', 'slug' => 'calculators', 'description' => 'Pricing, margin, and cost calculations.'],
            ['name' => 'E-commerce', 'slug' => 'e-commerce', 'description' => 'Tools for listing and selling online.'],
            ['name' => 'Text and content', 'slug' => 'text-and-content', 'description' => 'Writing helpers for product pages and customer notes.'],
            ['name' => 'Business productivity', 'slug' => 'business-productivity', 'description' => 'Documents and routines for day-to-day operations.'],
            ['name' => 'Converters', 'slug' => 'converters', 'description' => 'Practical conversions for shipping and catalog data.'],
        ];

        $templateCategories = [
            ['name' => 'Business spreadsheets', 'slug' => 'business-spreadsheets', 'description' => 'Sheets for tracking money and costs.'],
            ['name' => 'Freelance templates', 'slug' => 'freelance-templates', 'description' => 'Starter material for independent work.'],
            ['name' => 'E-commerce resources', 'slug' => 'e-commerce-resources', 'description' => 'Resources for online product sales.'],
            ['name' => 'Business planners', 'slug' => 'business-planners', 'description' => 'Planning pages for a small operation.'],
            ['name' => 'Invoice templates', 'slug' => 'invoice-templates', 'description' => 'Layouts and checklists for billing.'],
        ];

        $categories = [];

        foreach ($toolCategories as $category) {
            $categories[$category['slug']] = Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [...$category, 'type' => CategoryType::Tool],
            );
        }

        foreach ($templateCategories as $category) {
            $categories[$category['slug']] = Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [...$category, 'type' => CategoryType::Template],
            );
        }

        $tools = [
            [
                'slug' => 'profit-margin-calculator',
                'category' => 'calculators',
                'name' => 'Profit Margin Calculator',
                'description' => 'Work out profit, margin, markup, and a break-even price from your costs and fees.',
                'icon' => 'calculator',
                'access_type' => AccessType::Free,
                'is_featured' => true,
                'popularity' => 40,
                'long_description' => 'Use this when you set a price and need to see what remains after the product, shipping, packaging, and the fees you already know about. Marketplace fees and payment fees are each split into a percentage of the selling price and a fixed amount, so a 2.9% + €0.25 card fee is not treated as a vague single number.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'What is the difference between margin and markup?', 'a' => 'Margin divides profit by the selling price. Markup divides profit by the total cost. A healthy markup can still be a modest margin.'],
                        ['q' => 'Does this include tax?', 'a' => 'No. Enter figures the way you want to compare them, and handle VAT or sales tax in your own accounts.'],
                    ],
                ],
            ],
            [
                'slug' => 'product-description-generator',
                'category' => 'text-and-content',
                'name' => 'Product Description Generator',
                'description' => 'Draft a title, short description, and feature list from the facts you supply.',
                'icon' => 'pen-line',
                'access_type' => AccessType::Free,
                'is_featured' => true,
                'popularity' => 28,
                'long_description' => 'The generator sends your inputs to the configured provider when an API key is set. Without a key, QuickSolve stays in demo mode and assembles a clearly labeled draft on the server. It does not invent reviews or certifications.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Why does it say demo mode?', 'a' => 'Demo mode means no AI key is configured. The text is built from your form, and the response says so.'],
                        ['q' => 'Are there usage limits?', 'a' => 'Yes. Guests have a small daily limit. Signed-in plans have a monthly limit, and long descriptions require Pro or Business. Limits are checked on the server.'],
                    ],
                ],
            ],
            [
                'slug' => 'invoice-generator',
                'category' => 'business-productivity',
                'name' => 'Invoice Generator',
                'description' => 'Build a printable invoice, export a PDF, and save a copy when you are signed in.',
                'icon' => 'file-text',
                'access_type' => AccessType::Free,
                'is_featured' => true,
                'popularity' => 35,
                'long_description' => 'Guests can prepare an invoice and download a PDF without an account. Saving the document to the cloud requires a login. QuickSolve calculates the totals on the server for the PDF. The tool does not take payment and it does not confirm that the tax treatment is right for your country.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Will this charge my customer?', 'a' => 'No. It creates a document. You still send it and collect payment yourself.'],
                        ['q' => 'Can I save invoices?', 'a' => 'Yes, after you create an account. Saved files stay on the private disk and are only available to you.'],
                    ],
                ],
            ],
        ];

        foreach ($tools as $tool) {
            $category = $tool['category'];
            unset($tool['category']);

            Tool::query()->updateOrCreate(
                ['slug' => $tool['slug']],
                [...$tool, 'category_id' => $categories[$category]->id, 'is_published' => true],
            );
        }

        $downloads = [
            'small-business-finance-tracker' => 'small-business-finance-tracker.csv',
            'freelance-business-starter-kit' => 'freelance-business-starter-kit.txt',
            'e-commerce-profit-spreadsheet' => 'ecommerce-profit-spreadsheet.csv',
        ];

        foreach ($downloads as $slug => $filename) {
            $source = resource_path('downloads/'.$filename);
            $target = 'templates/'.$filename;
            Storage::disk('local')->put($target, file_get_contents($source));
        }

        $templates = [
            [
                'slug' => 'small-business-finance-tracker',
                'category' => 'business-spreadsheets',
                'name' => 'Small Business Finance Tracker',
                'price' => 1900,
                'description' => 'A CSV tracker for income and expenses, with example rows you can delete and short instructions in the file.',
                'whats_included' => ['CSV tracker with income and expense columns', 'Example rows marked as examples', 'Instructions inside the file'],
                'preview_image' => '/images/templates/finance-tracker.svg',
                'file' => 'small-business-finance-tracker.csv',
            ],
            [
                'slug' => 'freelance-business-starter-kit',
                'category' => 'freelance-templates',
                'name' => 'Freelance Business Starter Kit',
                'price' => 1500,
                'description' => 'A plain-text checklist for setting up an offer, sending invoices, and reviewing the week. Edit it before you share it with anyone.',
                'whats_included' => ['Starter checklist', 'Invoice habits', 'Weekly review prompts'],
                'preview_image' => '/images/templates/freelance-kit.svg',
                'file' => 'freelance-business-starter-kit.txt',
            ],
            [
                'slug' => 'e-commerce-profit-spreadsheet',
                'category' => 'e-commerce-resources',
                'name' => 'E-commerce Profit Spreadsheet',
                'price' => 1900,
                'description' => 'A CSV for listing selling price, product cost, shipping, packaging, and separate percentage and fixed fees.',
                'whats_included' => ['Fee columns split into percent and fixed amounts', 'One example product row', 'A short explanation of margin and markup'],
                'preview_image' => '/images/templates/profit-sheet.svg',
                'file' => 'ecommerce-profit-spreadsheet.csv',
            ],
        ];

        foreach ($templates as $template) {
            $category = $template['category'];
            $file = $template['file'];
            unset($template['category'], $template['file']);

            Template::query()->updateOrCreate(
                ['slug' => $template['slug']],
                [
                    ...$template,
                    'category_id' => $categories[$category]->id,
                    'currency' => 'EUR',
                    'private_file_path' => 'templates/'.$file,
                    'is_featured' => true,
                    'is_published' => true,
                ],
            );
        }
    }
}
