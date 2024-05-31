@extends('laravel-dynamic-report-generator::layouts.app')

@section('content')
    <div class="container">
        <h1>Saved Reports</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reports as $report)
                    <tr>
                        <td>{{ $report->name }}</td>
                        <td>{{ $report->slug }}</td>
                        <td>
                            <a href="{{ url('/report-generator/execute-report', $report->slug) }}"
                                class="btn btn-primary">Execute</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
