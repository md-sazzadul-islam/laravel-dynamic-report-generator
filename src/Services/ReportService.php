<?php
// src/Services/ReportService.php
namespace DevForest\Services;

use Illuminate\Support\Facades\DB;
use DevForest\Models\GeneratedReport;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ReportService
{
    public function getTables()
    {
        return DB::select('SHOW TABLES');
    }

    public function getDatabaseName()
    {
        return 'Tables_in_' . DB::connection()->getDatabaseName();
    }

    public function validateAndSaveReport($request)
    {
        $request->validate([
            'query' => ['required', function ($attribute, $value, $fail) {
                $dangerousQueries = ['create', 'alter', 'truncate', 'drop', 'insert', 'update', 'delete'];
                foreach ($dangerousQueries as $dangerousQuery) {
                    if (preg_match("/\b($dangerousQuery)\b/i", strtolower($value))) {
                        return $fail("Unable to execute {$dangerousQuery} query");
                    }
                }
                // Check if the query is executable
                try {
                    DB::beginTransaction();
                    DB::select(DB::raw($value . ' limit 1'));
                    DB::rollBack();
                } catch (\Exception $e) {
                    return $fail("The query is not executable: " . $e->getMessage());
                }
            }],
            'name' => 'required',
        ]);

        $data_set = json_encode($request->except('_token'));
        $name = $request->input('name');
        $slug = Str::slug($request->input('name'));
        $query = $request->input('query');

        GeneratedReport::create(['name' => $name, 'slug' => $slug, 'data_set' => $data_set, 'query' => $query]);
    }

    public function listReports()
    {
        return GeneratedReport::all();
    }

    public function executeReport($slug)
    {
        $report = GeneratedReport::where('slug', $slug)->firstOrFail();

        $isPaginated = config('report-generator.paginatedQuery');
        if ($isPaginated) {
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $perPage = 10;
            $offset = ($currentPage - 1) * $perPage;

            $query = $report->query . " LIMIT $perPage OFFSET $offset";
        } else {
            $query = $report->query;
        }

        $results = DB::select(DB::raw($query));

        $data_set = json_decode($report->data_set, false);
        $custom_query_count = "select count(*) as count from " . $data_set->main_table . " " . $data_set->joined_tables;

        $totalRecords = DB::select(DB::raw($custom_query_count))[0]->count;

        if ($isPaginated) {
            $paginatedResults = new LengthAwarePaginator($results, $totalRecords, $perPage, $currentPage, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
            ]);
        } else {
            $paginatedResults = $results;
        }

        return [
            'isPaginated' => $isPaginated,
            'results' => $paginatedResults,
            'columns' => !empty($results) ? array_keys((array)$results[0]) : [],
        ];
    }

    public function getColumns($table)
    {
        $columns = DB::select("SHOW COLUMNS FROM {$table}");

        $foreignKeys = DB::select("
                SELECT
                    COLUMN_NAME, TABLE_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                FROM
                    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE
                    TABLE_SCHEMA = DATABASE() AND
                    REFERENCED_TABLE_NAME =?
            ", [$table]);

        return [
            'columns' => $columns,
            'foreignKeys' => $foreignKeys,
        ];
    }
}
