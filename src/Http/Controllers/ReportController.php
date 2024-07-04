<?php
// src/Http/Controllers/ReportController.php
namespace DevForest\Http\Controllers;

use App\Http\Controllers\Controller;
use DevForest\Http\Requests\CreateGeneratedReport;
use DevForest\Http\Requests\UpdateGeneratedReport;
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

    public function save(CreateGeneratedReport $request)
    {
        $this->reportService->saveReport($request);
        return redirect('/report-generator/reports');
    }

    public function edit($id)
    {
        $report = $this->reportService->findOrFail($id);
        $report_data_set = json_decode($report->data_set, false);
        // dd($report_data_set);
        $tables = $this->reportService->getTables();
        $tables_in_database = $this->reportService->getDatabaseName();
        return view('laravel-dynamic-report-generator::edit', compact('report_data_set', 'report', 'tables', 'tables_in_database'));
    }
    public function update(UpdateGeneratedReport $request, $id)
    {
        $this->reportService->updateReport($request, $id);
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
    public function destroy($id)
    {
        $reportData = $this->reportService->deleteReport($id);
        return redirect()->to('reports')->with('success', 'Report deleted successfully.');
    }
}
