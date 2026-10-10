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
            [
                'slug' => 'discount-calculator',
                'category' => 'calculators',
                'name' => 'Discount Calculator',
                'description' => 'Work out a sale price, the amount saved, and the real discount when more than one reduction applies.',
                'icon' => 'percent',
                'access_type' => AccessType::Free,
                'is_featured' => false,
                'popularity' => 22,
                'long_description' => 'Enter an original price and a first discount, either a percentage of that price or a fixed amount taken off once. Extra discounts are percentages of the price that remains, so 20% followed by 10% is not the same as 30% off. The effective discount compares the final sale price with the original price.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Why is 20% then 10% not 30% off?', 'a' => 'The second percentage is calculated on the reduced price. On €100, 20% leaves €80, and 10% of €80 is €8, so the sale price is €72.'],
                        ['q' => 'Does this apply the discount at a store?', 'a' => 'No. It only calculates the figures. It does not change a listing or complete a purchase.'],
                    ],
                ],
            ],
            [
                'slug' => 'freelance-rate-calculator',
                'category' => 'calculators',
                'name' => 'Freelance Rate Calculator',
                'description' => 'Estimate the hourly or daily rate that covers an income target, expenses, billable hours, and unpaid leave.',
                'icon' => 'clock',
                'access_type' => AccessType::Free,
                'is_featured' => false,
                'popularity' => 18,
                'long_description' => 'The income target is the amount you want left after business expenses. Required revenue adds those expenses back in. Available days are your working weeks times days per week, minus unpaid leave. The hourly rate divides required revenue by billable hours. The daily rate divides it by available days. Pro and Business accounts can download that result as a plain-text report. The report is an estimate, not tax or accounting advice.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'What counts as unpaid leave?', 'a' => 'Days you planned to work but will not bill. They come out of the available days before the rate is calculated.'],
                        ['q' => 'Who can download the rate report?', 'a' => 'Pro and Business subscribers. The calculator itself stays free. The Freelance Business Starter Kit is a separate one-time file.'],
                    ],
                ],
            ],
            [
                'slug' => 'qr-code-generator',
                'category' => 'converters',
                'name' => 'QR Code Generator',
                'description' => 'Create a QR code for a URL, Wi-Fi network, contact card, or short piece of text, then download PNG or SVG.',
                'icon' => 'qr-code',
                'access_type' => AccessType::Free,
                'is_featured' => false,
                'popularity' => 16,
                'long_description' => 'The code is drawn in your browser. A Wi-Fi password is not sent to QuickSolve. Free codes are black on white. Pro and Business can choose foreground and background colors. PNG and SVG downloads use the same content. This does not host the destination page or join a Wi-Fi network for you.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Is a Wi-Fi password stored?', 'a' => 'No. The password is used only in your browser to build the code.'],
                        ['q' => 'What is included with branding?', 'a' => 'Custom foreground and background colors. Free codes stay black on white. A logo is not added to the code.'],
                    ],
                ],
            ],
            [
                'slug' => 'pdf-viewer-editor',
                'category' => 'business-productivity',
                'name' => 'PDF Viewer and Editor',
                'description' => 'Open a PDF in the browser, read each page, rotate pages, add a text note, and download the result.',
                'icon' => 'file-text',
                'access_type' => AccessType::Premium,
                'is_featured' => false,
                'popularity' => 12,
                'long_description' => 'This tool runs in your browser after a Pro or Business subscription is confirmed. The file is not uploaded to QuickSolve. You can read pages, rotate them, place a short text note, and download a new PDF. It is not a full layout program and it does not fill government forms or guarantee print fidelity.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Does QuickSolve keep my PDF?', 'a' => 'No. The file stays in the browser session. Closing the tab discards it unless you download a copy.'],
                        ['q' => 'Can I edit every kind of PDF?', 'a' => 'Password-protected files and some scanned-only documents cannot be changed here. You can still try to view pages that the browser can render.'],
                    ],
                ],
            ],
            [
                'slug' => 'image-resizer',
                'category' => 'converters',
                'name' => 'Image Resizer',
                'description' => 'Resize an image and convert it between JPEG, PNG, and WebP without sending the file to the server.',
                'icon' => 'image',
                'access_type' => AccessType::Premium,
                'is_featured' => false,
                'popularity' => 11,
                'long_description' => 'Choose a JPEG, PNG, WebP, or GIF, set a width and height, and download a new file in JPEG, PNG, or WebP. The work happens in your browser. GIF animation is not preserved. Pro and Business subscriptions unlock the tool after Stripe confirms payment.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Is the image uploaded?', 'a' => 'No. Conversion uses the canvas in your browser.'],
                        ['q' => 'Why is a GIF still after conversion?', 'a' => 'Only the first frame is drawn. Animated GIFs are not exported as animations.'],
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
