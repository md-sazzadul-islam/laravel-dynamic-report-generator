@extends('laravel-dynamic-report-generator::layouts.app')

@section('content')
    <div class="container">
        <h1>Report Results</h1>
        @if (($isPaginated && $results->isEmpty()) || (!$isPaginated && empty($results)))
            <div class="alert alert-info">
                No results found.
            </div>
        @else
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
                        @foreach ($results as $row)
                            <tr>
                                @foreach ($columns as $column)
                                    <td>{{ $row->$column }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($isPaginated)
                    <div class="d-flex justify-content-center pb-3">
                        {{ $results->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
