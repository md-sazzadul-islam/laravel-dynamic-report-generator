<?php
// src/Http/Controllers/ReportController.php
namespace DevForest\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DevForest\Services\ReportService;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        $tables = $this->reportService->getTables();
        $tables_in_database = $this->reportService->getDatabaseName();
        return view('laravel-dynamic-report-generator::index', compact('tables', 'tables_in_database'));
    }

    public function save(Request $request)
    {
        $this->reportService->validateAndSaveReport($request);
        return redirect('/report-generator/reports');
    }

    public function listReports()
    {
        $reports = $this->reportService->listReports();
        return view('laravel-dynamic-report-generator::reports', compact('reports'));
    }

    public function execute($slug)
    {
        $reportData = $this->reportService->executeReport($slug);
        // dd($reportData);
        return view('laravel-dynamic-report-generator::result', $reportData);
    }

    public function getColumns($table)
    {
        $columnsData = $this->reportService->getColumns($table);
        return response()->json($columnsData);
    }
}
