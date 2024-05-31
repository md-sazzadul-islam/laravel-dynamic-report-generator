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

        // Retrieve the current page from the request or set default to 1
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        // Set the number of results per page
        $perPage = 10;

        // Calculate the offset for the query
        $offset = ($currentPage - 1) * $perPage;

        // Execute the raw query with limit and offset for pagination
        $paginatedQuery = $report->query . " LIMIT $perPage OFFSET $offset";
        $results = DB::select(DB::raw($paginatedQuery));

        // Fetch the total count of records without limit for pagination
        $totalRecords = DB::select(DB::raw("SELECT COUNT(*) as count FROM ({$report->query}) as subquery"))[0]->count;

        // Create a LengthAwarePaginator instance
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
            COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
            TABLE_SCHEMA = DATABASE() AND
            TABLE_NAME = ? AND
            REFERENCED_TABLE_NAME IS NOT NULL
    ", [$table]);
        return response()->json(['columns' => $columns, 'foreignKeys' => $foreignKeys]);
    }
}
