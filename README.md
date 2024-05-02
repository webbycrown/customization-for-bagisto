# customization-for-bagisto

## 1. Introduction:

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

**Method** : POST.<br />
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
