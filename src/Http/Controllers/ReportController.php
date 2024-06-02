<?php
// src/Http/Controllers/ReportController.php
namespace DevForest\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DevForest\Models\GeneratedReport;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function index()
    {
        $tables = DB::select('SHOW TABLES');
        $tables_in_database = 'Tables_in_' . DB::connection()->getDatabaseName();
        return view('laravel-dynamic-report-generator::index', compact('tables', 'tables_in_database'));
    }

    public function save(Request $request)
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
        $slug =  Str::slug($request->input('name'));
        $query = $request->input('query');


        GeneratedReport::create(['name' => $name, 'slug' => $slug, 'data_set' => $data_set, 'query' => $query]);

        return redirect('/report-generator/reports');
    }

    public function listReports()
    {
        $reports = GeneratedReport::all();
        return view('laravel-dynamic-report-generator::reports', compact('reports'));
    }


    public function execute($slug)
    {
        $report = GeneratedReport::where('slug', $slug)->firstOrFail();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $offset = ($currentPage - 1) * $perPage;

        $paginatedQuery = $report->query . " LIMIT $perPage OFFSET $offset";
        $results = DB::select(DB::raw($paginatedQuery));

        $data_set = json_decode($report->data_set, false);
        $custom_query_count = "select count(*) as count from " . $data_set->main_table . " " . $data_set->joined_tables;

        $totalRecords = DB::select(DB::raw($custom_query_count))[0]->count;
        
        $paginatedResults = new LengthAwarePaginator($results, $totalRecords, $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        $columns = !empty($results) ? array_keys((array)$results[0]) : [];

        return view('laravel-dynamic-report-generator::result', compact('paginatedResults', 'columns'));
    }

    public function getColumns($table)
    {
        $columns = DB::select("SHOW COLUMNS FROM {$table}");
        $foreignKeys  = DB::select("
        SELECT
            COLUMN_NAME, TABLE_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
            TABLE_SCHEMA = DATABASE()  AND
            REFERENCED_TABLE_NAME =?
    ", [$table]);
        return response()->json(['columns' => $columns, 'foreignKeys' => $foreignKeys]);
    }
}
