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
                        <td width="250">
                            <div class="d-flex justify-content-around align-items-center">
                                <a href="{{ url('/report-generator/execute-report', $report->slug) }}"
                                    class="btn btn-primary">Execute</a>

                                <a href="{{ url('/report-generator/reports/'.$report->id.'/edit') }}"
                                    class="btn btn-warning">Edit</a>
                                <form action="{{ url('report-generator/reports/' . $report->id) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this report?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
