# Gemini AI Integration Guide

This guide explains how to set up and use the Gemini AI integration for AI-powered product descriptions in your Marketbob marketplace.

## Features

✨ **AI-Generated Descriptions**: Generate complete, SEO-friendly product descriptions with a single click
🏷️ **Smart Tag Suggestions**: Get relevant tag suggestions based on product name, category, and description
🎨 **Content Improvement**: Enhance existing descriptions for better SEO and readability
🔧 **Easy Integration**: Works seamlessly with CKEditor in both admin and workspace areas
🌐 **Global Access**: Centralized service that can be used anywhere in the application

## Installation & Setup

### Step 1: Get Your Gemini API Key

1. Visit [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Click "Create API Key"
3. Select or create a Google Cloud project
4. Copy your API key

### Step 2: Configure Environment Variables

Add the following to your `.env` file:

```env
GEMINI_API_KEY=your_api_key_here
GEMINI_MODEL=gemini-pro
GEMINI_API_URL=https://generativelanguage.googleapis.com/v1beta
```

### Step 3: Run Migration

Run the database migration to add the Gemini AI extension:

```bash
php artisan migrate
```

This will create the Gemini AI extension record in your database.

### Step 4: Enable Extension in Admin Panel

1. Log in to your admin panel
2. Navigate to **Settings > Extensions**
3. Find "Gemini AI" extension
4. Enter your API key in the settings
5. Enable the extension
6. Save changes

## Usage

### For Item Creators (Workspace)

When creating or editing an item, you'll see AI-powered buttons:

#### Generate Description
1. Enter the product **Name**
2. Select **Category** and **Tags** (optional but recommended)
3. Click **"Generate with AI"** button below the description field
4. The AI will generate a complete, SEO-friendly description

#### Improve Content
1. Write or paste your existing description
2. Click **"Improve Content"** button
3. The AI will enhance your content for better SEO and readability

#### Suggest Tags
1. Enter the product **Name**
2. Optionally add a **Description**
3. Click **"Suggest Tags with AI"** button below the tags field
4. AI will suggest 8-12 relevant tags

### For Developers - Using Gemini Service Globally

The `GeminiService` can be used anywhere in your application:

```php
use App\Services\Gemini\GeminiService;

// In your controller
public function myMethod(GeminiService $geminiService)
{
    // Check if enabled
    if (!$geminiService->isEnabled()) {
        return response()->json(['error' => 'Gemini AI not enabled']);
    }

    // Generate description
    $description = $geminiService->generateDescription(
        'Product Name',
        'Category Name',
        [
            'tags' => ['tag1', 'tag2'],
            'features' => ['feature1', 'feature2']
        ]
    );

    // Suggest tags
    $tags = $geminiService->suggestTags(
        'Product Name',
        'Product description...',
        'Category Name'
    );

    // Improve content
    $improved = $geminiService->improveContent(
        'Existing content...',
        'Product Name'
    );
}
```

### JavaScript API

Use the global `geminiAI` object in your frontend code:

```javascript
// Check status
const isEnabled = await geminiAI.checkStatus();

// Generate description
const description = await geminiAI.generateDescription(
    'Product Name',
    'Category',
    ['tag1', 'tag2'],
    ['feature1', 'feature2']
);

// Suggest tags
const tags = await geminiAI.suggestTags(
    'Product Name',
    'Description',
    'Category'
);

// Improve content
const improved = await geminiAI.improveContent(
    'Current content',
    'Product Name'
);
```

### Adding AI Features to Other Pages

To add Gemini AI buttons to any CKEditor instance:

```javascript
// Add AI generation buttons
addGeminiButtonToCKEditor(
    document.querySelector('textarea.ckeditor'),  // Editor element
    'input[name="title"]',                         // Product name field
    'select[name="category"]',                     // Category field (optional)
    'input[name="tags"]',                          // Tags field (optional)
    null                                           // Features field (optional)
);

// Add tag suggestions
addGeminiTagSuggestions(
    'input[name="tags"]',           // Tags input field
    'input[name="title"]',          // Product name field
    () => editor.getData(),         // Function to get description
    'select[name="category"]'       // Category field (optional)
);
```

## API Endpoints

The following API endpoints are available:

### Generate Description
```
POST /api/gemini/generate-description
```

**Request Body:**
```json
{
    "product_name": "My Product",
    "category": "WordPress Themes",
    "tags": ["responsive", "modern"],
    "features": ["SEO Optimized", "Mobile Friendly"]
}
```

### Suggest Tags
```
POST /api/gemini/suggest-tags
```

**Request Body:**
```json
{
    "product_name": "My Product",
    "description": "Product description...",
    "category": "WordPress Themes"
}
```

### Improve Content
```
POST /api/gemini/improve-content
```

**Request Body:**
```json
{
    "content": "Existing content...",
    "product_name": "My Product"
}
```

### Check Status
```
GET /api/gemini/status
```

## File Structure

```
app/
├── Services/
│   └── Gemini/
│       └── GeminiService.php          # Main service class
├── Http/
│   └── Controllers/
│       └── Api/
│           └── GeminiController.php   # API endpoints
config/
└── services.php                       # Gemini config added
database/
└── migrations/
    └── 2025_12_23_000000_add_gemini_ai_extension.php
public/
└── vendor/
    └── libs/
        └── gemini/
            └── gemini-ai.js          # Frontend JavaScript
resources/
└── views/
    └── themes/
        └── basic/
            └── workspace/
                └── items/
                    ├── create.blade.php  # Updated with AI buttons
                    └── edit.blade.php    # Updated with AI buttons
routes/
└── api.php                           # API routes added
```

## Customization

### Modify AI Prompts

Edit the prompt-building methods in `app/Services/Gemini/GeminiService.php`:

- `buildDescriptionPrompt()` - Customize description generation
- `buildTagSuggestionsPrompt()` - Customize tag suggestions
- `buildContentImprovementPrompt()` - Customize content improvement

### Change Model Settings

You can adjust the AI model behavior in the `callGeminiApi()` method:

```php
'generationConfig' => [
    'temperature' => 0.7,      // Creativity (0.0-1.0)
    'topK' => 40,              // Token selection
    'topP' => 0.95,            // Nucleus sampling
    'maxOutputTokens' => 2048, // Max response length
]
```

### Add to Other Areas

To integrate Gemini AI into other parts of your application:

1. Include the JavaScript file:
```blade
<script src="{{ asset('vendor/libs/gemini/gemini-ai.js') }}"></script>
```

2. Initialize the buttons:
```javascript
addGeminiButtonToCKEditor(editorElement, nameField, ...);
```

## Troubleshooting

### Extension Not Working

1. **Check if extension is enabled**: Go to Admin > Settings > Extensions
2. **Verify API key**: Make sure your Gemini API key is correctly entered
3. **Check .env file**: Ensure `GEMINI_API_KEY` is set
4. **Clear cache**: Run `php artisan config:clear`

### API Errors

- **403 Error**: Extension is not enabled or API key is missing
- **500 Error**: Check Laravel logs in `storage/logs/laravel.log`
- **Network Error**: Verify your server can connect to Google's API

### No Buttons Appearing

1. **Check JavaScript console** for errors
2. **Verify script is loaded**: Look for `gemini-ai.js` in browser dev tools
3. **Ensure user is authenticated**: API routes require authentication

## Security Notes

- API key is stored securely in database/environment variables
- All endpoints require authentication
- HTML content is sanitized before saving
- Rate limiting recommended for production use

## Support

For issues or questions:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Verify API key validity at Google AI Studio
3. Test the API endpoint directly with tools like Postman

## Future Enhancements

Potential features you can add:

- **Batch Generation**: Generate descriptions for multiple items
- **Language Support**: Multi-language description generation
- **Templates**: Pre-defined description templates
- **History**: Track and revert AI-generated content
- **A/B Testing**: Test different AI-generated variations
- **Analytics**: Track which AI features are most used

---

**Powered by Google Gemini AI** 🤖
