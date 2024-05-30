@extends('laravel-dynamic-report-generator::layouts.app')

@section('content')
    <div class="container">
        <h1>Report Generator</h1>
        {{-- show $validator->fails() --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="report-form" method="POST" action="{{ url('/report-generator/save-report') }}">
            @csrf
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label for="name">Report Name</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="select_columns">Select columns</label>
                        <input readonly type="text" id="select_columns" name="select_columns" class="form-control"
                            required>
                    </div>

                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="main_table">Main Table</label>
                        <input readonly type="text" id="main_table" name="main_table" class="form-control" required>
                    </div>

                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label for="query">Generated Query</label>
                        <textarea id="query" name="query" class="form-control" rows="5" required></textarea>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Save Report</button>
        </form>

        <div class="row">

            <div class="col-md-4">
                <div class="panel">
                    <h4>Table list</h4>
                    <div id="tables" class="droppable">
                        @foreach ($tables as $table)
                            <div class="table draggable border p-2 mb-2" data-table="{{ $table->$tables_in_database }}">
                                {{ $table->$tables_in_database }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel">
                    <h4>Selected table's columns</h4>
                    <div id="columns" class="droppable"></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel">
                    <h4>Selected table's foreign key</h4>
                    <div id="foreign-keys" class="droppable"></div>
                </div>
            </div>
        </div>


    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let tables = document.querySelectorAll('.table');
            tables.forEach(table => {
                table.addEventListener('click', function() {
                    fetchColumns(this.dataset.table);
                });
            });
        });
        /* database query generator function*/
        function query_generator() {
            let select_columns = $('#select_columns').val();
            let main_table = $('#main_table').val();
            query = `SELECT ${select_columns?select_columns:'*'} FROM ${main_table}`;
            $('#query').val(query);
        }

        function fetchColumns(table) {
            fetch(`/report-generator/columns/${table}`)
                .then(response => response.json())
                .then(data => {
                    let columnsDiv = document.getElementById('columns');
                    columnsDiv.innerHTML = '';
                    data.columns.forEach(column => {
                        let columnDiv = document.createElement('div');
                        columnDiv.classList.add('column', 'draggable', 'border', 'p-2', 'mb-2');
                        columnDiv.textContent = column.Field;
                        columnDiv.dataset.column = column.Field;
                        columnsDiv.appendChild(columnDiv);
                    });

                    let foreignKeysDiv = document.getElementById('foreign-keys');
                    foreignKeysDiv.innerHTML = '';
                    data.foreignKeys.forEach(fk => {
                        let fkDiv = document.createElement('div');
                        fkDiv.classList.add('foreign-key', 'draggable', 'border', 'p-2', 'mb-2');
                        fkDiv.textContent =
                            `Join on ${fk.COLUMN_NAME} -> ${fk.REFERENCED_TABLE_NAME}.${fk.REFERENCED_COLUMN_NAME}`;
                        fkDiv.dataset.join =
                            `JOIN ${fk.REFERENCED_TABLE_NAME} ON ${table}.${fk.COLUMN_NAME} = ${fk.REFERENCED_TABLE_NAME}.${fk.REFERENCED_COLUMN_NAME}`;
                        foreignKeysDiv.appendChild(fkDiv);
                    });

                    $('.draggable').draggable({
                        helper: 'clone'
                    });

                    $('#select_columns').droppable({
                        accept: '.column',
                        drop: function(event, ui) {
                            let text = ui.helper.text();
                            let select_columns = $('#select_columns').val();
                            select_columns += select_columns ? `, ${text}` : text;
                            $('#select_columns').val(select_columns.trim());
                            query_generator();
                        }
                    });
                    $('#main_table').droppable({
                        accept: '.table',
                        drop: function(event, ui) {
                            let text = ui.helper.text();
                            let main_table = $('#main_table').val();
                            main_table += main_table ? `, ${text}` : text;
                            $('#main_table').val(main_table.trim());
                            query_generator();
                        }
                    });
                    // $('#query').droppable({
                    //     accept: '.column',
                    //     drop: function(event, ui) {
                    //         let text = ui.helper.text();
                    //         let query = $('#query').val();
                    //         query += query ? `, ${text}` : text;
                    //         $('#query').val(query);
                    //     }
                    // });
                });
        }
    </script>
@endpush
