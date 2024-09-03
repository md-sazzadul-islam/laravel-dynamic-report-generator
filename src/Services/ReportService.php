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

    public function saveReport($request)
    {

        $data_set = json_encode($request->except('_token'));
        $name = $request->input('name');
        $slug = Str::slug($request->input('name'));
        $query = $request->input('query');

        GeneratedReport::create(['name' => $name, 'slug' => $slug, 'data_set' => $data_set, 'query' => $query]);
    }

    public function updateReport($request, $id)
    {
        $report = $this->findOrFail($id);
        $data_set = json_encode($request->except('_token', '_method'));
        $name = $request->input('name');
        $slug = Str::slug($request->input('name'));
        $query = $request->input('query');

        $report->update(['name' => $name, 'slug' => $slug, 'data_set' => $data_set, 'query' => $query]);
    }

    public function listReports()
    {
        return GeneratedReport::all();
    }
    public function deleteReport($id)
    {
        $report = $this->findOrFail($id);

        $report->delete();
        return $report;
    }
    public function findOrFail($id)
    {
        return GeneratedReport::findOrFail($id);
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

        $results = DB::select(DB::raw($query)->getValue(DB::getQueryGrammar()));

        $data_set = json_decode($report->data_set, false);
        $group_by = (isset($data_set->group_by_columns) ? ($data_set->group_by_columns ? " Group By " . $data_set->group_by_columns : "") : "");
        $custom_query_count = "select count(*) as count from " . $data_set->main_table . " " . str_replace("|", "", $data_set->joined_tables) . " " . $group_by;
        $totalRecords = DB::select(DB::raw($custom_query_count)->getValue(DB::getQueryGrammar()));
        $totalRecords = 0;
        if ($totalRecords) {
            $totalRecords = $totalRecords[0]->count;
        } else

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
