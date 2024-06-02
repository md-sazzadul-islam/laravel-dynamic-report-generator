@extends('laravel-dynamic-report-generator::layouts.app')

@section('content')
    <div class="container">
        <h1>Report Results</h1>
        @if (!empty($paginatedResults->items()))
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            @foreach ($columns as $column)
                                <th>{{ Str::title(str_replace('_', ' ', $column)) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($paginatedResults as $row)
                            <tr>
                                @foreach ($columns as $column)
                                    <td>{{ $row->$column }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-center pb-3">
                    {{ $paginatedResults->links() }}
                </div>
            </div>
        @else
            <p>No results found.</p>
        @endif
    </div>
@endsection
