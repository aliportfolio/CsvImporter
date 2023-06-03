<?php

namespace Aliportfolio\CsvImporter;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;

abstract class CsvImporter
{
    /**
     * The database table to import data into.
     *
     * @var string
     */
    protected $table;

    /**
     * The mapping between CSV fields and database columns.
     *
     * @var array
     */
    protected $mapping = [];

    /**
     * The validation rules for the imported data.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Import data from a CSV file.
     *
     * @param string $filePath The path to the CSV file.
     * @return void
     */
    public function import($filePath)
    {
        // Open the CSV file
        $csv = Reader::createFromPath($filePath);

        // Get the header row and column count
        $header = $csv->fetchOne();
        $columnCount = count($header);

        // Get the mapping between CSV fields and database columns
        $mapping = $this->getMapping($header);

        // Start a database transaction
        $connection = Model::resolveConnection();
        $connection->beginTransaction();

        try {
            // Loop through the CSV rows and insert them into the database
            foreach ($csv as $row) {
                // Skip the header row
                if ($row === $header) {
                    continue;
                }

                // Build an array of data to insert
                $data = [];
                for ($i = 0; $i < $columnCount; $i++) {
                    $field = $mapping[$header[$i]];
                    $value = $row[$i];
                    $data[$field] = $value;
                }

                // Validate the data
                $validator = Validator::make($data, $this->rules);
                if ($validator->fails()) {
                    throw new \Exception('Validation failed.');
                }

                // Insert the data into the database
                $model = $this->getModel();
                $model->fill($data);
                $model->save();
            }

            // Commit the transaction
            $connection->commit();
        } catch (\Exception $e) {
            // Roll back the transaction on error
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * Get the mapping between CSV fields and database columns.
     *
     * @param array $header The header row of the CSV file.
     * @return array
     */
    protected function getMapping($header)
    {
        $mapping = [];
        foreach ($this->mapping as $field => $column) {
            if (is_numeric($field)) {
                $field = $column;
            }
            if (in_array($field, $header)) {
                $mapping[$field] = $column;
            }
        }
        return $mapping;
    }

    /**
     * Get the model used to insert data into the database.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function getModel()
    {
        return new ('App\Models\\' . $this->table);
    }
}
