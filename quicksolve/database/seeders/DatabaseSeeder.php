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
            ['name' => 'Marketing and SEO', 'slug' => 'marketing-and-seo', 'description' => 'Previews and campaign helpers for pages and ads.'],
            ['name' => 'Developer tools', 'slug' => 'developer-tools', 'description' => 'Small utilities that run in the browser.'],
            ['name' => 'Everyday', 'slug' => 'everyday', 'description' => 'Simple converters and calendars for daily tasks.'],
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
                'description' => 'Open a PDF in the browser, read it, edit the wording or drop text on a page, and download a new file.',
                'icon' => 'file-text',
                'access_type' => AccessType::Premium,
                'is_featured' => false,
                'popularity' => 12,
                'long_description' => 'This tool runs in your browser after a Pro or Business subscription is confirmed. The file is not uploaded to QuickSolve. Page editor keeps the original layout and lets you click to add text boxes. Document editor extracts readable text so you can rewrite it like a simple Word file, then download a clean PDF. It does not fill government forms or keep complex fonts and images in document mode.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Does QuickSolve keep my PDF?', 'a' => 'No. The file stays in the browser session. Closing the tab discards it unless you download a copy.'],
                        ['q' => 'Can I edit every kind of PDF?', 'a' => 'Password-protected files cannot be unlocked here. Scanned pages may show in the viewer but will not give much text to edit. Document mode rebuilds a new PDF from the wording you type.'],
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
            [
                'slug' => 'break-even-calculator',
                'category' => 'calculators',
                'name' => 'Break-Even Calculator',
                'description' => 'Find how many units you need to sell to cover fixed and variable costs.',
                'icon' => 'calculator',
                'access_type' => AccessType::Free,
                'is_featured' => true,
                'popularity' => 22,
                'long_description' => 'Enter monthly or project fixed costs, the cost that changes with each unit, and the selling price. The result is the smallest whole number of units that covers those costs. Marketplace fees are not included; use the profit margin calculator when a platform takes a cut.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Does this include VAT?', 'a' => 'No. Enter amounts the way you want to compare them.'],
                        ['q' => 'What if the price is below variable cost?', 'a' => 'There is no break-even volume. Raise the price or lower the variable cost.'],
                    ],
                ],
            ],
            [
                'slug' => 'timesheet-calculator',
                'category' => 'business-productivity',
                'name' => 'Timesheet Calculator',
                'description' => 'Add daily start and end times, subtract breaks, and total overtime for the week.',
                'icon' => 'clock',
                'access_type' => AccessType::Premium,
                'is_featured' => false,
                'popularity' => 18,
                'long_description' => 'Times stay in your browser. Overtime is the minutes beyond the daily threshold you set. Overnight shifts are not supported. You can copy the totals or download a CSV.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Is this payroll?', 'a' => 'No. It only totals hours. It does not apply wage rules for your country.'],
                    ],
                ],
            ],
            [
                'slug' => 'serp-snippet-preview',
                'category' => 'marketing-and-seo',
                'name' => 'SERP Snippet Preview',
                'description' => 'Preview how a page title, URL, and meta description may look in search results.',
                'icon' => 'search',
                'access_type' => AccessType::Free,
                'is_featured' => true,
                'popularity' => 21,
                'long_description' => 'This is a layout sketch. Search engines can rewrite titles and descriptions. Character counts are a guide, not a guarantee of what a result page will show.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Will this rank my page?', 'a' => 'No. It only shows how your draft copy might wrap in a result listing.'],
                    ],
                ],
            ],
            [
                'slug' => 'utm-link-builder',
                'category' => 'marketing-and-seo',
                'name' => 'UTM Link Builder',
                'description' => 'Add source, medium, campaign, term, and content parameters to a campaign URL.',
                'icon' => 'link',
                'access_type' => AccessType::Free,
                'is_featured' => false,
                'popularity' => 19,
                'long_description' => 'The builder encodes the values and keeps any query string already on the page. It does not create a short link or record clicks.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Are empty fields included?', 'a' => 'No. Only fields you fill are added to the URL.'],
                    ],
                ],
            ],
            [
                'slug' => 'marketplace-fee-calculator',
                'category' => 'e-commerce',
                'name' => 'Marketplace Fee Calculator',
                'description' => 'Estimate marketplace and payment fees, then see profit and margin on one sale.',
                'icon' => 'store',
                'access_type' => AccessType::Free,
                'is_featured' => true,
                'popularity' => 24,
                'long_description' => 'Example fee schedules are starting points. Edit the percentages and fixed amounts to match the live help page of the platform you use. Tax and currency conversion are not included.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Are the presets official rates?', 'a' => 'No. They are examples. Always check the marketplace fee page before you price a listing.'],
                    ],
                ],
            ],
            [
                'slug' => 'roas-calculator',
                'category' => 'e-commerce',
                'name' => 'ROAS Calculator',
                'description' => 'Calculate return on ad spend, profit after product cost, and a break-even ROAS.',
                'icon' => 'megaphone',
                'access_type' => AccessType::Free,
                'is_featured' => false,
                'popularity' => 17,
                'long_description' => 'ROAS is revenue divided by ad spend. Break-even ROAS uses revenue divided by revenue minus product cost. Wages, returns, and tax are not included.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Is a 4x ROAS always profitable?', 'a' => 'No. If product cost is high, you can still lose money at a high ROAS. Read the profit line.'],
                    ],
                ],
            ],
            [
                'slug' => 'jwt-decoder',
                'category' => 'developer-tools',
                'name' => 'JWT Decoder',
                'description' => 'Inspect a JSON Web Token header and payload in the browser without sending it to a server.',
                'icon' => 'key',
                'access_type' => AccessType::Free,
                'is_featured' => false,
                'popularity' => 15,
                'long_description' => 'The token is decoded locally. Signatures are not verified and signing secrets are not requested. Treat production tokens as sensitive even though they never leave this tab.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Does this check if a token is valid?', 'a' => 'No. It only reads the Base64 parts. It cannot prove the token was signed correctly.'],
                    ],
                ],
            ],
            [
                'slug' => 'regex-tester',
                'category' => 'developer-tools',
                'name' => 'Regex Tester',
                'description' => 'Test a regular expression against sample text and highlight the matches.',
                'icon' => 'code',
                'access_type' => AccessType::Free,
                'is_featured' => false,
                'popularity' => 14,
                'long_description' => 'Patterns run in your browser with JavaScript regular expression syntax. Saved libraries and server-side engines are not included.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Which regex dialect is this?', 'a' => 'JavaScript. Lookbehind and Unicode property escapes depend on the visitor’s browser.'],
                    ],
                ],
            ],
            [
                'slug' => 'date-difference-calculator',
                'category' => 'everyday',
                'name' => 'Date Difference Calculator',
                'description' => 'Count days, weeks, months, and years between two calendar dates.',
                'icon' => 'calendar',
                'access_type' => AccessType::Free,
                'is_featured' => false,
                'popularity' => 20,
                'long_description' => 'The day count uses whole calendar days. Months and years follow the calendar, so a 31 January to 28 February span is not always the same length as a 30-day month.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Does the end date count as a full day?', 'a' => 'The result is the number of midnights between the two dates. Swap the dates if you typed them in reverse; the span is the same.'],
                    ],
                ],
            ],
            [
                'slug' => 'business-card-maker',
                'category' => 'business-productivity',
                'name' => 'Business Card Maker',
                'description' => 'Design a simple business card with your name, role, and contact details, then download a PNG.',
                'icon' => 'id-card',
                'access_type' => AccessType::Premium,
                'is_featured' => true,
                'popularity' => 13,
                'long_description' => 'The card is drawn in your browser at a print-friendly 3.5 by 2 inch size. Choose a layout and colours, then download a PNG. This does not print or mail cards. Pro and Business subscriptions keep the maker unlocked after the five free Pro trials.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Is this printed for me?', 'a' => 'No. You download an image you can send to a printer.'],
                        ['q' => 'Do I need Pro?', 'a' => 'The first five Pro-tool uses are free. After that, a Pro or Business plan is required.'],
                    ],
                ],
            ],
            [
                'slug' => 'cv-builder',
                'category' => 'business-productivity',
                'name' => 'CV templates',
                'description' => 'Pick a layout, edit your details, and download a PDF résumé.',
                'icon' => 'file-text',
                'access_type' => AccessType::Premium,
                'is_featured' => true,
                'popularity' => 19,
                'long_description' => 'Nine layouts share the same fields: classic, modern sidebar, compact, executive, timeline, creative band, split rail, banner, and mosaic. The first five Pro-tool uses are free. After that, a Pro or Business plan is required. The PDF is a document you can send; it does not apply for jobs.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Can I change the layout after writing?', 'a' => 'Yes. The templates reuse the same text. Switch layouts before you download.'],
                        ['q' => 'Do I need Pro?', 'a' => 'Five Pro-tool uses are free, including this one. Further downloads need a Pro or Business subscription.'],
                    ],
                ],
            ],
            [
                'slug' => 'email-templates',
                'category' => 'text-and-content',
                'name' => 'Email templates',
                'description' => 'Nineteen ready-made situations, casual or formal tone, plus a DeepSeek chat to draft emails from a topic and settings.',
                'icon' => 'mail',
                'access_type' => AccessType::Premium,
                'is_featured' => true,
                'popularity' => 18,
                'long_description' => 'Use a ready-made layout or open DeepSeek chat with a topic, situation, tone, and facts. With a DeepSeek API key the chat is live. Without a key, drafts are assembled on the server and labeled as demo. QuickSolve does not send the message. The first five Pro-tool uses are free. After that, a Pro or Business plan is required. Chat generations also count toward the monthly writing allowance.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Does this send the email?', 'a' => 'No. You copy or download the text and send it from your own account.'],
                        ['q' => 'What is the formal tone?', 'a' => 'It uses Dear, Yours sincerely, and fuller sentences. Casual stays shorter.'],
                        ['q' => 'When is DeepSeek live?', 'a' => 'Only when a DeepSeek API key is configured on the server and the request succeeds. Otherwise the page stays in demo mode and says so.'],
                        ['q' => 'Do I need Pro?', 'a' => 'Five Pro-tool uses are free, including this one. Further visits need a Pro or Business subscription.'],
                    ],
                ],
            ],
            [
                'slug' => 'quote-builder',
                'category' => 'business-productivity',
                'name' => 'Quote builder',
                'description' => 'Price a job, set a validity date, and download a quote PDF. Pro adds accent colour; Business removes the QuickSolve line.',
                'icon' => 'file',
                'access_type' => AccessType::Premium,
                'is_featured' => true,
                'popularity' => 17,
                'long_description' => 'Quotes use the same line-item math as invoices: quantity times unit price, then one tax rate on the subtotal. The first five Pro-tool uses are free. After that, a Pro or Business plan is required. This is not a contract and it does not collect payment.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Is a quote legally binding?', 'a' => 'No. It is a price document you can send. A signed contract is separate.'],
                        ['q' => 'Can I save quotes?', 'a' => 'Yes, when you are signed in. They appear with invoices in your documents list.'],
                    ],
                ],
            ],
            [
                'slug' => 'cover-letter',
                'category' => 'text-and-content',
                'name' => 'Cover letter',
                'description' => 'Write a short cover letter and download it as a PDF for a named role and company.',
                'icon' => 'file-text',
                'access_type' => AccessType::Premium,
                'is_featured' => false,
                'popularity' => 15,
                'long_description' => 'Fill your details, the hiring manager, and the letter body. QuickSolve does not submit applications. Five Pro-tool uses are free; further visits need Pro or Business.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Does this send the letter?', 'a' => 'No. You download a PDF and send it yourself.'],
                    ],
                ],
            ],
            [
                'slug' => 'cash-runway-calculator',
                'category' => 'calculators',
                'name' => 'Cash runway calculator',
                'description' => 'Estimate how many months cash lasts given monthly costs and inflow.',
                'icon' => 'calculator',
                'access_type' => AccessType::Premium,
                'is_featured' => false,
                'popularity' => 16,
                'long_description' => 'Runway is cash divided by net monthly burn (costs minus inflow). If inflow covers costs, there is no countdown. This ignores tax, late invoices, and one-off spends. Five Pro-tool uses are free.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Is this a forecast?', 'a' => 'No. It is a simple division of the figures you enter.'],
                    ],
                ],
            ],
            [
                'slug' => 'payment-landing-date',
                'category' => 'calculators',
                'name' => 'Payment landing date',
                'description' => 'Turn an invoice due date into when cash is likely to land, using the client’s pay-run, weekends, holidays, and bank clearing days.',
                'icon' => 'calendar',
                'access_type' => AccessType::Premium,
                'is_featured' => true,
                'popularity' => 19,
                'long_description' => 'Net 30 is not the day the money arrives. Many clients pay only on a Friday, on the 28th, or on the last weekday of the month. This tool starts from the invoice date and terms, then moves to that pay-run, skips weekends and holidays you type, and adds clearing days. It does not connect to a bank. Five Pro-tool uses are free.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Is this when the bank will pay me?', 'a' => 'No. It is a calendar from the dates and pay-run you enter. The client can still pay late or fail.'],
                        ['q' => 'Which holidays does it know?', 'a' => 'None by default. Paste the dates this client actually skips, in YYYY-MM-DD form.'],
                    ],
                ],
            ],
            [
                'slug' => 'scope-change-note',
                'category' => 'business-productivity',
                'name' => 'Scope change note',
                'description' => 'Turn a “can you just…” request into a one-page confirmation with extra cost, and hold the extra work until the client replies.',
                'icon' => 'file-plus',
                'access_type' => AccessType::Premium,
                'is_featured' => true,
                'popularity' => 18,
                'long_description' => 'Invoice and quote tools price a job. They do not record the extra that arrives after you start. Write the original agreement, the new request, hours, and cost, then download a PDF the client can confirm. QuickSolve does not email it and it is not a signed contract. Five Pro-tool uses are free.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Is this legally binding?', 'a' => 'No. It is a written recap. A signed contract is separate. It exists so extras are not treated as free.'],
                        ['q' => 'Does QuickSolve send it?', 'a' => 'No. You download the PDF and send it yourself.'],
                    ],
                ],
            ],
            [
                'slug' => 'random-number-generator',
                'category' => 'everyday',
                'name' => 'Random Number Generator',
                'description' => 'Generate random integers in a range for draws, games, and everyday tasks.',
                'icon' => 'dice',
                'access_type' => AccessType::Free,
                'is_featured' => false,
                'popularity' => 16,
                'long_description' => 'Numbers are generated in your browser. You can ask for more than one value and optionally keep them unique. This is not a certified lottery or cryptographic service.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Are the numbers truly random?', 'a' => 'They use the browser’s cryptographic random source when it is available. They are not a lottery draw.'],
                    ],
                ],
            ],
            [
                'slug' => 'http-status-code-lookup',
                'category' => 'developer-tools',
                'name' => 'HTTP Status Code Lookup',
                'description' => 'Search HTTP status codes and read what they mean, common causes, and what to check next.',
                'icon' => 'globe',
                'access_type' => AccessType::Free,
                'is_featured' => true,
                'popularity' => 18,
                'long_description' => 'Meanings follow the usual HTTP semantics. Debugging notes are practical starting points, not a full diagnosis of your stack.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Does this scan my site?', 'a' => 'No. It only explains the code you look up.'],
                    ],
                ],
            ],
            [
                'slug' => 'unit-converter',
                'category' => 'converters',
                'name' => 'Unit Converter',
                'description' => 'Convert length, weight, temperature, volume, and speed with common units.',
                'icon' => 'repeat',
                'access_type' => AccessType::Free,
                'is_featured' => false,
                'popularity' => 23,
                'long_description' => 'Length, weight, volume, and speed use fixed conversion factors. Temperature uses the usual Celsius, Fahrenheit, and Kelvin formulas. Cooking measures beyond cup and millilitre are not included.',
                'metadata' => [
                    'faqs' => [
                        ['q' => 'Is a US gallon used?', 'a' => 'Yes. The gallon is the US liquid gallon.'],
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
            'freelancer-finance-spreadsheet' => 'freelancer-finance-spreadsheet.csv',
            'business-launch-checklist' => 'business-launch-checklist.txt',
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
            [
                'slug' => 'freelancer-finance-spreadsheet',
                'category' => 'freelance-templates',
                'name' => 'Freelancer Finance Spreadsheet',
                'price' => 1900,
                'description' => 'A CSV for tracking invoices, expenses, and a monthly profit total, with example rows you replace.',
                'whats_included' => ['Income and expense columns', 'Invoice status field', 'Example rows marked as examples'],
                'preview_image' => '/images/templates/finance-tracker.svg',
                'file' => 'freelancer-finance-spreadsheet.csv',
            ],
            [
                'slug' => 'business-launch-checklist',
                'category' => 'business-planners',
                'name' => 'Business Launch Checklist',
                'price' => 1200,
                'description' => 'A plain-text checklist for launching a freelance offer or a small online store. Edit it before you share it.',
                'whats_included' => ['Offer and pricing prompts', 'Legal and payment setup notes', 'Launch-week tasks'],
                'preview_image' => '/images/templates/freelance-kit.svg',
                'file' => 'business-launch-checklist.txt',
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
