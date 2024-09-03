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
                <div class="col-md-12 col-sm-12">
                    <div class="form-group">
                        <label for="name">Report Name</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                        <label for="select_columns">Select columns</label>
                        <div>
                            <div class="pull-left" style="width: 87%">
                                <input readonly type="text" id="select_columns" name="select_columns"
                                    class="form-control" required>
                            </div>
                            <div class="pull-left" style="width: 13%">
                                <button type="button" id="select_columns_undo" class="btn btn-info btn-flip">
                                    <i class="fa fa-share-square fa-flip"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-md-6 col-sm-6">
                    <div class="form-group">

                        <label for="main_table">Main Table</label>
                        <input readonly type="text" id="main_table" name="main_table" class="form-control" required>

                    </div>

                </div>
                <div class="col-md-12 col-sm-12">
                    <div class="form-group">
                        <label for="joined_tables">Joined tables</label>
                        <div>
                            <div class="pull-left" style="width: 90%">
                                <input readonly type="text" id="joined_tables" name="joined_tables" class="form-control"
                                    >
                            </div>
                            <div class="pull-left" style="width: 10%">
                                <button type="button" id="joined_tables_undo" class="btn btn-info btn-flip">
                                    <i class="fa fa-share-square fa-flip"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
                
                <div class="col-md-12 col-sm-12">
                    <div class="form-group">
                        <label for="group_by_columns">Group By Columns</label>
                        <div>
                            <div class="pull-left" style="width: 90%">
                                <input readonly type="text" id="group_by_columns" name="group_by_columns" class="form-control"
                                    >
                            </div>
                            <div class="pull-left" style="width: 10%">
                                <button type="button" id="group_by_column_undo" class="btn btn-info btn-flip">
                                    <i class="fa fa-share-square fa-flip"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label for="query">Generated Query</label>
                        <textarea readonly id="query" name="query" class="form-control" rows="5" required></textarea>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Save Report</button>
        </form>

        <div class="row">

            <div class="col-md-4">
                <div class="panel">
                    <h4>Table list (Frist click on table)</h4>
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
                    <h4>Can be joined with selected table's</h4>
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
            let joined_tables = $('#joined_tables').val().replace(' | ', ' ');
            let groupby_columns = $('#group_by_columns').val();            
            let groupby_query = '';
            if(groupby_columns) {
                groupby_query = `GROUP BY ${groupby_columns}`;
            }
            query = `SELECT ${select_columns} FROM ${main_table} ${joined_tables} ${groupby_query}`;
            $('#query').val(query);
        }


        $('#select_columns_undo').on('click', function() {
            let currentColumns = $('#select_columns').val().split(',').map(col => col.trim()).filter(col => col);
            if (currentColumns.length > 0) {
                currentColumns.pop();
                $('#select_columns').val(currentColumns.join(', '));
            }
            query_generator();
        });

        $('#joined_tables_undo').on('click', function() {
            let currentColumns = $('#joined_tables').val().split('|').map(col => col.trim()).filter(col => col);
            if (currentColumns.length > 0) {
                currentColumns.pop();
                $('#joined_tables').val(currentColumns.join(', '));
            }
            query_generator();
        });

        $('#group_by_column_undo').on('click', function() {
            let currentColumns = $('#group_by_columns').val().split(',').map(col => col.trim()).filter(col => col);
            if (currentColumns.length > 0) {
                currentColumns.pop();
                $('#group_by_columns').val(currentColumns.join(', '));
            }
            query_generator();
        });

        function fetchColumns(table) {
            fetch(`/report-generator/columns/${table}`)
                .then(response => response.json())
                .then(data => {
                    let columnsDiv = document.getElementById('columns');
                    columnsDiv.innerHTML = '';
                    var columnDiv = document.createElement('div');
                    columnDiv.classList.add('column', 'draggable', 'border', 'p-2', 'mb-2');
                    columnDiv.textContent = "*";
                    columnDiv.dataset.column = "*";
                    columnsDiv.appendChild(columnDiv);

                    var columnDiv = document.createElement('div');
                    columnDiv.classList.add('column', 'draggable', 'border', 'p-2', 'mb-2');
                    columnDiv.textContent = "`" + table + "`.*";
                    columnDiv.dataset.column = "`" + table + "`.*";
                    columnsDiv.appendChild(columnDiv);
                    data.columns.forEach(column => {
                        let columnDiv = document.createElement('div');
                        columnDiv.classList.add('column', 'draggable', 'border', 'p-2', 'mb-2');
                        // columnDiv.textContent = `${table}.${column.Field}`;
                        columnDiv.textContent = "`" + table + "`.`" + column.Field + "`";
                        columnDiv.dataset.column = "`" + table + "`.`" + column.Field + "`";
                        columnsDiv.appendChild(columnDiv);
                    });

                    let foreignKeysDiv = document.getElementById('foreign-keys');
                    foreignKeysDiv.innerHTML = '';
                    data.foreignKeys.forEach(fk => {
                        let fkDiv = document.createElement('div');
                        fkDiv.classList.add('foreign-key', 'draggable', 'border', 'p-2', 'mb-2');
                        fkDiv.textContent =
                            `JOIN ${fk.TABLE_NAME} ON ${table}.${fk.REFERENCED_COLUMN_NAME} = ${fk.TABLE_NAME}.${fk.COLUMN_NAME}`;
                        fkDiv.dataset.join =
                            `JOIN ${fk.TABLE_NAME} ON ${table}.${fk.REFERENCED_COLUMN_NAME} = ${fk.TABLE_NAME}.${fk.COLUMN_NAME}`;
                        
                        
                        foreignKeysDiv.appendChild(fkDiv);
                    });

                    $('.draggable').draggable({
                        helper: 'clone'
                    });


                    $('#joined_tables').droppable({
                        accept: '.foreign-key',
                        drop: function(event, ui) {
                            let text = ui.helper.text();

                            let currentColumns = $('#joined_tables').val().split(',').map(col => col
                                .trim()).filter(col => col);
                            if (!currentColumns.includes(text)) {
                                currentColumns.push(text);
                                $('#joined_tables').val(currentColumns.join(' | '));
                            }
                            query_generator();
                        }
                    });
                    $('#select_columns').droppable({
                        accept: '.column',
                        drop: function(event, ui) {
                            let text = ui.helper.text();

                            let currentColumns = $('#select_columns').val().split(',').map(col => col
                                .trim()).filter(col => col);
                            if (!currentColumns.includes(text)) {
                                currentColumns.push(text);
                                $('#select_columns').val(currentColumns.join(', '));
                            }
                            query_generator();
                        }
                    });

                    $('#group_by_columns').droppable({
                        accept: '.column',
                        drop: function(event, ui) {
                            let text = ui.helper.text();

                            let currentColumns = $('#group_by_columns').val().split(',').map(col => col
                                .trim()).filter(col => col);
                            if (!currentColumns.includes(text)) {
                                currentColumns.push(text);
                                $('#group_by_columns').val(currentColumns.join(', '));
                            }
                            query_generator();
                        }
                    });

                    $('#main_table').droppable({
                        accept: '.table',
                        drop: function(event, ui) {
                            let text = ui.helper.text().trim();
                            text = "`" + text + "`";
                            $('#main_table').val(text);
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
