# Quick Setup Guide - Gemini AI Integration

## 🚀 Quick Start (5 Minutes)

### 1. Get API Key
Visit https://makersuite.google.com/app/apikey and create an API key

### 2. Add to .env File
```env
GEMINI_API_KEY=your_api_key_here
GEMINI_MODEL=gemini-pro
```

### 3. Run Migration
```bash
php artisan migrate
```

### 4. Enable in Admin
1. Go to **Admin Panel > Settings > Extensions**
2. Find **Gemini AI**
3. Enter your API key
4. Enable the extension
5. Save

## ✅ What's Been Integrated

### Files Created/Modified:

**Backend:**
- ✅ `app/Services/Gemini/GeminiService.php` - Core AI service
- ✅ `app/Http/Controllers/Api/GeminiController.php` - API endpoints
- ✅ `database/migrations/2025_12_23_000000_add_gemini_ai_extension.php` - Extension setup
- ✅ `config/services.php` - Gemini configuration
- ✅ `routes/api.php` - API routes

**Frontend:**
- ✅ `public/vendor/libs/gemini/gemini-ai.js` - JavaScript integration
- ✅ `resources/views/themes/basic/workspace/items/create.blade.php` - AI buttons
- ✅ `resources/views/themes/basic/workspace/items/edit.blade.php` - AI buttons

**Configuration:**
- ✅ `.env.example` - Environment variables template

## 🎯 Features Available

### In Item Creation/Edit Pages:

1. **Generate with AI** button
   - Automatically creates SEO-friendly product descriptions
   - Uses product name, category, tags, and features

2. **Improve Content** button
   - Enhances existing descriptions
   - Optimizes for SEO and readability

3. **Suggest Tags with AI** button
   - Generates 8-12 relevant tags
   - Based on product name and description

## 🔧 How to Use

### For Item Authors:

**Creating New Item:**
1. Enter product name
2. Select category (optional)
3. Click "Generate with AI" button
4. Review and edit the generated description
5. Click "Suggest Tags with AI" for tag recommendations

**Editing Existing Item:**
1. Click "Improve Content" to enhance current description
2. Click "Generate with AI" to create fresh description
3. Use "Suggest Tags" to get new tag ideas

## 📡 API Endpoints

All endpoints require authentication:

```
POST /api/gemini/generate-description
POST /api/gemini/suggest-tags
POST /api/gemini/improve-content
GET  /api/gemini/status
```

## 🌐 Global Service Access

Use anywhere in your Laravel application:

```php
use App\Services\Gemini\GeminiService;

class YourController extends Controller
{
    public function yourMethod(GeminiService $gemini)
    {
        if ($gemini->isEnabled()) {
            $description = $gemini->generateDescription('Product Name', 'Category');
            $tags = $gemini->suggestTags('Product Name');
            $improved = $gemini->improveContent('Your content');
        }
    }
}
```

## 🔒 Security

- ✅ All API routes require authentication
- ✅ CSRF protection enabled
- ✅ API key stored securely in database/env
- ✅ HTML content sanitized before saving
- ✅ Extension can be disabled from admin panel

## 🐛 Troubleshooting

**Buttons not appearing?**
- Check if extension is enabled in admin panel
- Verify user is logged in
- Check browser console for JavaScript errors

**API errors?**
- Verify API key is correct
- Check `storage/logs/laravel.log`
- Run `php artisan config:clear`
- Ensure internet connection for API calls

**Extension not in admin panel?**
- Run `php artisan migrate`
- Check database for `extensions` table
- Clear cache: `php artisan cache:clear`

## 📚 Full Documentation

See `GEMINI_AI_INTEGRATION.md` for complete documentation including:
- Advanced customization
- JavaScript API reference
- Prompt customization
- Additional integration examples
- Future enhancement ideas

## 💡 Tips

1. **Better Results**: Provide more context (category, tags, features) for better AI-generated content
2. **SEO**: Generated descriptions are already SEO-optimized with proper HTML structure
3. **Editing**: Always review and customize AI-generated content to match your brand voice
4. **Cost**: Monitor your Google Cloud usage - Gemini API has usage limits and costs

## 🎉 You're All Set!

The Gemini AI integration is now ready to use. Your item creators can start generating professional, SEO-friendly descriptions with a single click!

---

**Need Help?** Check the detailed documentation in `GEMINI_AI_INTEGRATION.md`
