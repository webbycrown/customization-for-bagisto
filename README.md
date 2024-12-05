# customization-for-bagisto

## 1. Introduction:

“Customization for Bagisto” is an extension that enables you to build dynamic page section options managed from the backend. This plugin allows you to manage the frontend appearance from the backend and also has an API, which allows you to deploy the website from the front end. This means that users can manage page sections from the backend, specify the types of sections needed for each page, and seamlessly deploy these changes to the frontend using the API.

### Key Features:

- Backend settings allow you to create page's sections and you can create section options.
- You can build sections of pages in the backend that you construct on page settings, which allows you to classify which pages and what types of sections you require on that page. With API, it will be dynamically presented on the website's front end.
- Each section includes a range of field types, including Text Box, Select Field, Text Area, File, Product, Category, Category Product, and Repeater.
- To add a blog, you may need to install a blog extension so that blogging functionalities are available on your site.
- There is another feature called "Repeater" that allows you to repeat content options in the backend.
- There is a facility that also allows you to add multiple fields to showcase your content.

## 2. Requirements:

* **PHP**: 8.0 or higher.
* **Bagisto**: v2.0.*
* **Composer**: 1.6.5 or higher.

## 3. Installation:

- Run the below command to install the package.
```
composer require webbycrown/customization-for-bagisto:dev-main
```

```
composer dump-autoload
```

```
php artisan optimize:clear
```

```
php artisan migrate --path=vendor/webbycrown/customization-for-bagisto/src/Database/Migrations
```

```
php artisan storage:link
```

```
php artisan vendor:publish --all
```

## 4. Api:

- Get Customization Details

**Method** : POST.

**URL** : http://localhost/api/v1/customization_details

| Param Name    | Type      | Description														|
| --------------| --------- | ----------------------------------------------------------------- |
| page_slug     | string    | ( Required ) Page slug for specific page content.					|
| section_slug  | string    | ( Optional ) Section slug for specific section content of page.	|
| field_key     | string    | ( Optional ) Field key for specific content of section in page.	|

1. You should just enter **page_slug** to get full page content with all section details.
2. You should enter **page_slug** and **section_slug** to get specific section details of the page.
3. You should enter **page_slug**, **section_slug** and **field_key** to get specific content of the section in page.

- **Validations**

1. **page_slug** is required.
2. **page_slug** is required when you enter **section_slug**.
3. **page_slug** and **section_slug** is required when you enter **field_key**.
