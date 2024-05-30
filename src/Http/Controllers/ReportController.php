<?php
// src/Http/Controllers/ReportController.php
namespace DevForest\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DevForest\Models\GeneratedReport;

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
        $name = $request->input('name');
        $query = $request->input('query');

        GeneratedReport::create(['name' => $name, 'query' => $query]);

        return redirect('/report-generator/reports');
    }

    public function listReports()
    {
        $reports = GeneratedReport::all();
        return view('laravel-dynamic-report-generator::reports', compact('reports'));
    }

    public function execute($id)
    {
        $report = GeneratedReport::findOrFail($id);
        $results = DB::select(DB::raw($report->query));

        $columns = !empty($results) ? array_keys((array)$results[0]) : [];

        return view('laravel-dynamic-report-generator::result', compact('results', 'columns'));
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
