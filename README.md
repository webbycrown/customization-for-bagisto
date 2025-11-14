# WebbyCrown Customization for Bagisto

## 1. Introduction:

"Customization for Bagisto" is a powerful page builder extension that enables you to create and manage dynamic page sections with flexible content management directly from the backend. This plugin allows complete control over your frontend appearance from the admin panel and includes a RESTful API for seamless frontend deployment. Build custom pages, manage sections, and deploy changes dynamically without touching code.

### Key Features:

- 🎨 **Dynamic Page Builder** - Create custom pages with flexible section management
- 📝 **8 Field Types** - Text, Select, Textarea, File Upload, Product, Category, Blog, Repeater
- 🔄 **Repeater Fields** - Add multiple instances of content blocks
- 🎯 **Section-Based Architecture** - Organize content in reusable sections
- 🔌 **RESTful API** - Deploy frontend content dynamically via API
- 📤 **File Upload with Optimization** - Auto-convert images to WebP format
- 🛍️ **E-commerce Integration** - Product and Category selectors
- 📱 **Blog Support** - Integrate with blog extensions
- ⚙️ **Backend Configuration** - Complete admin panel interface
- 🔐 **Field Validation** - Required fields and data validation
- 🗂️ **Multiple Selection** - Support for multi-select fields
- 🌐 **API-Driven Frontend** - Fetch content dynamically for SPAs and headless setups

## 2. Requirements:

* **PHP**: 8.1 or 8.2
* **Bagisto**: v2.0.* or higher
* **Composer**: 1.6.5 or higher
* **Laravel**: 10.x or 11.x
* **Intervention Image**: ^2.7 (PHP 8.1) or ^3.0 (PHP 8.2)
* **Intervention ImageCache**: Optional (not required)

## 3. Installation:

### Step 1: Install the Package

```bash
composer require webbycrown/customization-for-bagisto
```

> **Note:** This package now supports both PHP 8.1 and PHP 8.2

### Step 2: Clear Cache

```bash
composer dump-autoload
php artisan optimize:clear
```

### Step 3: Run Database Migrations

```bash
php artisan migrate --path=vendor/webbycrown/customization-for-bagisto/src/Database/Migrations
```

### Step 4: Create Storage Link

```bash
php artisan storage:link
```

### Step 5: Publish Assets (Optional)

```bash
php artisan vendor:publish --all
```

## 4. Configuration:

### Admin Panel Access

Navigate to: **Admin Panel → Customization**

The admin panel provides four main sections:

1. **Pages** - Manage custom pages
2. **Sections** - Create reusable sections for pages
3. **Settings** - Configure section fields and options
4. **Content** - Add actual content to sections

### Database Tables

The package creates four tables:

| Table | Description |
|-------|-------------|
| `customization_pages` | Stores page definitions |
| `customization_sections` | Stores section definitions |
| `customization_settings` | Stores field configurations |
| `customization_details` | Stores actual content data |

## 5. Usage Guide:

### 5.1 Creating a Page

1. Go to **Admin → Customization → Pages**
2. Click **Add New Page**
3. Enter **Page Title** (e.g., "Homepage")
4. Enter **Page Slug** (e.g., "homepage")
5. Click **Save**

### 5.2 Creating a Section

1. Navigate to your page (e.g., "Homepage")
2. Click **Add New Section**
3. Enter **Section Title** (e.g., "Hero Banner")
4. Enter **Section Slug** (e.g., "hero-banner")
5. Click **Save**

### 5.3 Adding Fields to a Section

Navigate to: **Page → Section → Settings**

#### Available Field Types:

| Field Type | Description | Use Case |
|------------|-------------|----------|
| **Text Box** | Single line text input | Titles, headings, short text |
| **Text Area** | Multi-line text input | Descriptions, paragraphs |
| **Select Field** | Dropdown selection | Options, choices |
| **File** | File upload (images converted to WebP) | Images, documents |
| **Product** | Product selector from catalog | Featured products |
| **Category** | Category selector | Product categories |
| **Category Product** | Products from specific category | Category showcases |
| **Blog** | Blog post selector (requires blog extension) | Featured articles |
| **Repeater** | Repeatable field groups | Testimonials, features, slides |

#### Field Configuration Options:

- **Title** - Display label for the field
- **Field Name** - Unique identifier (key) for the field
- **Field Type** - Select from 8 available types
- **Required** - Make field mandatory
- **Multiple** - Allow multiple selections (for select, product, category, file)
- **Status** - Enable/disable the field
- **Options** - Define dropdown options (for select fields)

### 5.4 Adding Content

1. Navigate to: **Page → Section**
2. Fill in the field values
3. Upload files (automatically optimized to WebP for images)
4. Select products/categories as needed
5. Click **Save**

### 5.5 Using Repeater Fields

Repeater fields allow you to create multiple instances of a content block:

1. Add a **Repeater** field to your section
2. Define sub-fields within the repeater
3. In content section, click **Add Row** to create multiple instances
4. Each row can have different content
5. Drag to reorder rows

**Example Use Case:** Testimonials section with multiple customer reviews

```
Repeater: testimonials
  ├─ Text: customer_name
  ├─ Text Area: review_text
  ├─ File: customer_photo
  └─ Text: rating
```

## 6. API Documentation:

### 6.1 Get Customization Details

Retrieve page content dynamically via API.

**Endpoint:** `POST /api/v1/customization_details`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

### 6.2 API Parameters:

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `page_slug` | string | ✅ Yes | Page slug (e.g., "homepage") |
| `section_slug` | string | ❌ No | Specific section slug |
| `field_key` | string | ❌ No | Specific field key |

### 6.3 API Usage Examples:

#### Example 1: Get Full Page Content

**Request:**
```json
POST /api/v1/customization_details
{
    "page_slug": "homepage"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "page": {
            "title": "Homepage",
            "slug": "homepage"
        },
        "sections": {
            "hero-banner": {
                "title": "Hero Banner",
                "fields": {
                    "heading": "Welcome to Our Store",
                    "subheading": "Best products at best prices",
                    "banner_image": "https://cdn.example.com/banner.webp",
                    "cta_text": "Shop Now"
                }
            },
            "featured-products": {
                "title": "Featured Products",
                "fields": {
                    "products": [1, 5, 8, 12]
                }
            }
        }
    }
}
```

#### Example 2: Get Specific Section

**Request:**
```json
POST /api/v1/customization_details
{
    "page_slug": "homepage",
    "section_slug": "hero-banner"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "section": {
            "title": "Hero Banner",
            "slug": "hero-banner",
            "fields": {
                "heading": "Welcome to Our Store",
                "subheading": "Best products at best prices",
                "banner_image": "https://cdn.example.com/banner.webp"
            }
        }
    }
}
```

#### Example 3: Get Specific Field

**Request:**
```json
POST /api/v1/customization_details
{
    "page_slug": "homepage",
    "section_slug": "hero-banner",
    "field_key": "heading"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "field_value": "Welcome to Our Store"
    }
}
```

### 6.4 API Validations:

| Validation | Rule |
|------------|------|
| `page_slug` | Always required |
| `section_slug` | Required when using `field_key` |
| Invalid slugs | Returns 404 error |

## 7. Field Types in Detail:

### 7.1 Text Box Field
- Single line text input
- Use for: Titles, headings, button text, URLs

**Example Configuration:**
```
Title: Hero Heading
Field Name: hero_heading
Type: Text Box
Required: Yes
```

### 7.2 Text Area Field
- Multi-line text input
- Use for: Descriptions, paragraphs, long content

**Example Configuration:**
```
Title: About Us Description
Field Name: about_description
Type: Text Area
Required: Yes
```

### 7.3 Select Field
- Dropdown selection
- Supports multiple selection
- Define custom options

**Example Configuration:**
```
Title: Layout Style
Field Name: layout_style
Type: Select
Options: Grid, List, Carousel
Multiple: No
```

### 7.4 File Upload Field
- Supports images and documents
- Images automatically converted to WebP
- Supports multiple file uploads
- Integrates with S3 (if configured)

**Example Configuration:**
```
Title: Banner Image
Field Name: banner_image
Type: File
Multiple: No
Required: Yes
```

**Supported Formats:**
- Images: JPG, PNG, GIF, WebP (auto-converted)
- Documents: PDF, DOC, DOCX, etc.

### 7.5 Product Field
- Select products from your catalog
- Supports multiple selection
- Returns product IDs

**Example Configuration:**
```
Title: Featured Products
Field Name: featured_products
Type: Product
Multiple: Yes
Required: No
```

### 7.6 Category Field
- Select categories from your store
- Supports multiple selection
- Returns category IDs

**Example Configuration:**
```
Title: Featured Categories
Field Name: featured_categories
Type: Category
Multiple: Yes
```

### 7.7 Category Product Field
- Select products from a specific category
- Useful for category-specific showcases

**Example Configuration:**
```
Title: Electronics Products
Field Name: electronics_showcase
Type: Category Product
Multiple: Yes
```

### 7.8 Blog Field
- Select blog posts (requires blog extension)
- Supports multiple selection
- Returns blog post IDs

**Requirements:**
- Blog extension must be installed
- Example: `webbycrown/blog-bagisto`

**Example Configuration:**
```
Title: Featured Articles
Field Name: featured_articles
Type: Blog
Multiple: Yes
```

### 7.9 Repeater Field
- Create repeatable content blocks
- Define sub-fields within repeater
- Drag and drop to reorder

**Example Use Cases:**
- Testimonials with customer name, photo, review
- Feature lists with icon, title, description
- Team members with photo, name, position
- FAQ sections with question and answer

**Example Configuration:**
```
Title: Customer Testimonials
Field Name: testimonials
Type: Repeater

Sub-fields:
  - customer_name (Text)
  - customer_photo (File)
  - review_text (Textarea)
  - rating (Select: 1-5 stars)
```

## 8. Image Optimization:

### Automatic WebP Conversion

The package automatically converts uploaded images to WebP format for better performance:

- **Original Format:** JPG, PNG, GIF
- **Output Format:** WebP
- **Benefits:** 25-35% smaller file size
- **Quality:** Maintained during conversion

### How It Works:

```php
// Automatic process when uploading images
if (isImage($file)) {
    $manager = new ImageManager();
    $image = $manager->make($file)->encode('webp');
    Storage::put($path, $image);
}
```

### S3 Integration:

If you have the S3 extension installed, images are automatically uploaded to your S3 bucket.

## 9. Advanced Usage:

### 9.1 Building a Homepage

**Step-by-Step Example:**

1. **Create Page:** "Homepage" (slug: `homepage`)

2. **Create Sections:**
   - Hero Banner (slug: `hero-banner`)
   - Featured Products (slug: `featured-products`)
   - About Section (slug: `about-section`)
   - Testimonials (slug: `testimonials`)

3. **Configure Hero Banner Fields:**
   - `heading` (Text) - "Welcome to Our Store"
   - `subheading` (Textarea) - Store description
   - `background_image` (File) - Hero image
   - `cta_text` (Text) - "Shop Now"
   - `cta_link` (Text) - "/shop"

4. **Configure Featured Products:**
   - `title` (Text) - "Featured Products"
   - `products` (Product, Multiple) - Select 8 products

5. **Configure Testimonials (Repeater):**
   - Repeater field: `testimonials`
   - Sub-fields:
     - `customer_name` (Text)
     - `customer_photo` (File)
     - `review` (Textarea)
     - `rating` (Select: 1-5)

6. **Fetch via API:**
```javascript
fetch('/api/v1/customization_details', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ page_slug: 'homepage' })
})
.then(response => response.json())
.then(data => {
    // Render your homepage with the data
    console.log(data);
});
```

### 9.2 Frontend Integration

#### React/Vue Example:

```javascript
import { useState, useEffect } from 'react';

function Homepage() {
    const [pageData, setPageData] = useState(null);

    useEffect(() => {
        fetch('/api/v1/customization_details', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ page_slug: 'homepage' })
        })
        .then(res => res.json())
        .then(data => setPageData(data.sections));
    }, []);

    if (!pageData) return <div>Loading...</div>;

    return (
        <div>
            <section className="hero">
                <h1>{pageData['hero-banner'].fields.heading}</h1>
                <p>{pageData['hero-banner'].fields.subheading}</p>
                <img src={pageData['hero-banner'].fields.background_image} />
            </section>

            <section className="testimonials">
                {pageData.testimonials.fields.testimonials.map((item, index) => (
                    <div key={index} className="testimonial">
                        <img src={item.customer_photo} />
                        <h3>{item.customer_name}</h3>
                        <p>{item.review}</p>
                        <span>Rating: {item.rating}/5</span>
                    </div>
                ))}
            </section>
        </div>
    );
}
```

## 10. PHP 8.2 Compatibility:

### Updated Dependencies

This package has been updated to support PHP 8.2 with the following changes:

**Before (PHP 8.1 only):**
```json
{
    "php": "^8.1",
    "intervention/image": "^2.4",
    "intervention/imagecache": "^2.5.2"
}
```

**After (PHP 8.1 & 8.2):**
```json
{
    "php": "^8.1|^8.2",
    "intervention/image": "^2.7|^3.0",
    "intervention/imagecache": "^2.6"
}
```

### Intervention Image Versions:

- **PHP 8.1:** Uses Intervention Image v2.7+
- **PHP 8.2:** Uses Intervention Image v3.0+ (recommended)

### No Code Changes Required:

The package code is compatible with both versions. Composer will automatically install the correct version based on your PHP version.

## 11. Troubleshooting:

**Issue: PHP 8.2 errors with intervention/image**
- **Solution:** Update to package v1.0.0 which supports PHP 8.2
- **Command:** `composer require webbycrown/customization-for-bagisto`

**Issue: Images not uploading**
- Check storage permissions: `php artisan storage:link`
- Verify upload_max_filesize in php.ini
- Check intervention/image is installed

**Issue: API returns 404**
- Verify page_slug and section_slug are correct
- Check if content exists in admin panel
- Ensure migrations are run

**Issue: Repeater fields not saving**
- Check JavaScript console for errors
- Verify all sub-fields are properly configured
- Clear cache: `php artisan optimize:clear`

**Issue: Blog field not showing**
- Install blog extension first
- Verify blog table exists in database
- Check blog extension service provider is loaded

**Issue: S3 upload fails**
- Verify S3 extension is installed
- Check AWS credentials are configured
- Test S3 connection separately

**Issue: WebP conversion fails**
- Ensure GD or Imagick PHP extension is installed
- Check intervention/image version compatibility
- Verify image file is valid

## 12. FAQ:

**Q: Can I use this without the API?**  
A: Yes, you can query the database directly using the models, but the API is recommended for frontend integration.

**Q: Does it work with headless/SPA setups?**  
A: Yes! The RESTful API makes it perfect for React, Vue, Angular, or any frontend framework.

**Q: Can I create multiple pages?**  
A: Yes, create unlimited pages, each with unlimited sections.

**Q: Is there a limit on repeater rows?**  
A: No technical limit, but consider performance with very large datasets.

**Q: Can I reuse sections across pages?**  
A: Sections are page-specific, but you can duplicate section configurations.

**Q: Does it support multi-language?**  
A: Currently single language, multi-language support planned for future releases.

**Q: Can I export/import page configurations?**  
A: Not built-in yet, but you can export database tables manually.

**Q: Is it compatible with custom themes?**  
A: Yes, it's theme-agnostic. Integrate via API in any theme.

## 13. Support:

- 🐛 [Report Issues](https://github.com/webbycrown/customization-for-bagisto/issues)
- 📧 Email: info@webbycrown.com
- 🌐 Website: [webbycrown.com](https://webbycrown.com)

## 14. Changelog:

### v1.0.1 - 2025-11-14

#### 🐛 Bug Fixes

- 🔧 **Fixed critical validation error** in `CustomizationController@store` method
- ✅ Changed `field_details` validation from `array` to `nullable|array`
- ✅ Added `repeater_data` validation rule to support repeater fields
- 🔄 Fixed repeater fields not appearing/saving properly
- 🚀 Sections with only repeater fields now work correctly

#### 🔧 Dependency Fix

- 🔧 **Fixed Laravel 11 compatibility** - Removed `intervention/imagecache` from required dependencies
- ✅ Made `intervention/imagecache` optional (moved to suggest section)
- 💪 Works with both Laravel 10 and 11

---

### v1.0.0 - 2025-11-14

#### ✨ Initial Stable Release

- 🎉 Initial release of **Customization for Bagisto**
- 🎨 Dynamic page builder with section-based architecture
- 📝 8 flexible field types (Text, Select, Textarea, File, Product, Category, Blog, Repeater)
- 🔄 Repeater fields for creating multiple content instances
- 🔌 RESTful API for frontend content deployment
- 📤 Automatic WebP image conversion for optimization
- 🛍️ E-commerce integration with product and category selectors
- 📱 Blog post selector (with blog extension)
- ⚙️ Complete admin panel interface for content management
- 🗂️ Multi-select support for products, categories, and files
- 📊 Four database tables for organized data storage
- 🔐 Field validation and required field support

#### 🐛 Bug Fixes

- 🔧 **PHP 8.2 Compatibility** - Updated intervention/image to support both PHP 8.1 and 8.2
- 📦 Updated intervention/image from ^2.4 to ^2.7|^3.0
- 📦 Updated intervention/imagecache from ^2.5.2 to ^2.6
- ✅ Added PHP ^8.1|^8.2 support in composer.json

#### 📚 Documentation

- 📖 Comprehensive README with 14 sections
- 🔧 Complete installation guide
- 💡 Detailed usage guide for all field types
- 🔌 Complete API documentation with examples
- 💻 Frontend integration examples (React/Vue)
- ❓ FAQ section (8 questions)
- 🛠️ Troubleshooting guide
- 📊 Field type reference with use cases
- 🎯 Advanced usage examples (homepage builder)
- 🔄 Repeater field detailed documentation

#### 🔄 Package

- 📦 Stable package version for production use
- ✨ Simple installation: `composer require webbycrown/customization-for-bagisto`
- 🏷️ Properly tagged and versioned for Packagist
- 🔑 SEO-optimized with relevant keywords
- 📦 Published to Packagist: [webbycrown/customization-for-bagisto](https://packagist.org/packages/webbycrown/customization-for-bagisto)
- 🐙 Open source on GitHub: [webbycrown/customization-for-bagisto](https://github.com/webbycrown/customization-for-bagisto)

---

<div align="center">
  <strong>Made with ❤️ by <a href="https://webbycrown.com">WebbyCrown</a></strong>
</div>
