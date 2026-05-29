<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;

class CsvImportService
{
    /**
     * Get headers (first row) from a CSV file.
     *
     * @param string $filePath
     * @return array
     */
    public function getHeaders(string $filePath): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [];
        }

        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ',');
            fclose($handle);
            return $header ? array_map('trim', $header) : [];
        }

        return [];
    }

    /**
     * Parse and validate CSV data using dynamic mapping indexes.
     *
     * @param string $filePath
     * @param array $mapping Associative array mapping fields to their CSV index
     * @return array
     */
    public function importMapped(string $filePath, array $mapping): array
    {
        $recipients = [];
        $emailsSeen = [];

        $stats = [
            'total' => 0,
            'valid' => 0,
            'invalid' => 0,
            'duplicates' => 0,
        ];

        if (!file_exists($filePath) || !is_readable($filePath)) {
            return compact('recipients', 'stats');
        }

        if (($handle = fopen($filePath, 'r')) !== false) {
            // Skip the header row
            fgetcsv($handle, 1000, ',');

            // Read mapping definitions (default -1 if not mapped)
            $emailIndex = isset($mapping['email']) ? (int) $mapping['email'] : -1;
            $companyNameIndex = isset($mapping['company_name']) ? (int) $mapping['company_name'] : -1;
            $websiteIndex = isset($mapping['website']) ? (int) $mapping['website'] : -1;
            $hrNameIndex = isset($mapping['hr_name']) ? (int) $mapping['hr_name'] : -1;
            $positionIndex = isset($mapping['position']) ? (int) $mapping['position'] : -1;

            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                // Skip empty lines
                if (empty($row) || (count($row) === 1 && $row[0] === null)) {
                    continue;
                }

                $stats['total']++;

                $email = ($emailIndex !== -1 && isset($row[$emailIndex])) ? trim($row[$emailIndex]) : null;
                $companyName = ($companyNameIndex !== -1 && isset($row[$companyNameIndex])) ? trim($row[$companyNameIndex]) : null;
                $website = ($websiteIndex !== -1 && isset($row[$websiteIndex])) ? trim($row[$websiteIndex]) : null;
                $hrName = ($hrNameIndex !== -1 && isset($row[$hrNameIndex])) ? trim($row[$hrNameIndex]) : null;
                $position = ($positionIndex !== -1 && isset($row[$positionIndex])) ? trim($row[$positionIndex]) : null;

                // Validate
                $validator = Validator::make(
                    [
                        'email' => $email,
                        'company_name' => $companyName,
                        'website' => $website,
                        'hr_name' => $hrName,
                        'position' => $position,
                    ],
                    [
                        'email' => 'required|email|max:255',
                        'company_name' => 'nullable|string|max:255',
                        'website' => 'nullable|string|max:255',
                        'hr_name' => 'nullable|string|max:255',
                        'position' => 'nullable|string|max:255',
                    ]
                );

                if ($validator->fails()) {
                    $stats['invalid']++;
                    continue;
                }

                // Deduplicate emails in the CSV
                $lowercaseEmail = strtolower($email);
                if (in_array($lowercaseEmail, $emailsSeen)) {
                    $stats['duplicates']++;
                    continue;
                }

                $emailsSeen[] = $lowercaseEmail;
                $stats['valid']++;

                $recipients[] = [
                    'email' => $email,
                    'company_name' => $companyName,
                    'website' => $website,
                    'hr_name' => $hrName,
                    'position' => $position,
                ];
            }
            fclose($handle);
        }

        return compact('recipients', 'stats');
    }
}
