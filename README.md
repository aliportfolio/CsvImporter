# CSV Importer
The package allows you to upload a CSV file and import the data into database.

## Features
- CSV Importer
- TXT Importer
- Fields Validator

## Installation
Install with command
```bash
  composer require aliportfolio/csv-importer
```

## Usage/Examples
First, create custom importer (ex: CategoryImport.php)
```php
<?php

namespace App\Imports;

use Aliportfolio\CsvImporter\CsvImporter;

class CategoryImport extends CsvImporter
{
    // Model Name
    protected $table = 'Category';

    protected $mapping = [
        'name' => 'name'
    ];

    // Validation Data
    protected $rules = [
        'name' => 'required|string'
    ];
}

```
In controller call the CategoryImport Class
```php
$import = new CategoryImport();
$import->import($request->file('csv_file'));
```

