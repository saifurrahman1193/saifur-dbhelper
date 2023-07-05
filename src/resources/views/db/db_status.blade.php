<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DB Status</title>

    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">

    <!-- Include Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Bootstrap Table JS & CSS via CDN -->
    <link href="https://unpkg.com/bootstrap-table@1.22.0/dist/bootstrap-table.min.css" rel="stylesheet">
    <script src="https://unpkg.com/bootstrap-table@1.22.0/dist/bootstrap-table.min.js"></script>

    <style>
        .small-font {
            font-size: 12px; /* Adjust the font size for specific elements with the "small-font" class */
        }
    </style>

</head>

<body>

    <div class="container">
        <div class="row">
            <h4 class="col-6 text-danger">Table Information</h4>
            <p class="text-end py-0 my-0  small-font"><strong>Count:</strong> {{ count($databaseInformation['table']['tables']) }}</p>
            <p class="text-end py-0 my-0  small-font"><strong>Size:</strong> {{ $databaseInformation['table']['summary']['total_size_m'] }}</p>
        </div>

        <div class="row">
            <div class="col-md-12">

                <table id="table-table" class="table table-bordered table-striped sortable table-sm small-font table-hover">
                    <thead>
                        <tr>
                            <th data-field="sl" data-sortable="true">S/L</th>
                            <th data-field="name" data-sortable="true">Table Name</th>
                            <th data-field="engine" data-sortable="true">Engine</th>
                            <th data-field="rows" data-sortable="true">Rows</th>
                            <th data-field="dataSize" data-sortable="true">Data Size</th>
                            <th data-field="indexSize" data-sortable="true">Index Size</th>
                            <th data-field="totalSize" data-sortable="true">Total Size</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($databaseInformation['table']['tables'] as $key => $row)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $row['name'] }}</td>
                                <td>{{ $row['engine'] }}</td>
                                <td>{{ $row['rows'] }}</td>
                                <td>{{ $row['dataSize_m'] }}</td>
                                <td>{{ $row['indexSize_m'] }}</td>
                                <td>{{ $row['totalSize_m'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <h4 class="col-6 text-danger">View Information</h4>
            <p class="text-end py-0 my-0  small-font"><strong>Count:</strong> {{ count($databaseInformation['view']['views']) }}</p>
        </div>

        <div class="row">
            <div class="col-md-12">

                <table id="table-view" class="table table-bordered table-striped sortable table-sm small-font table-hover">
                    <thead>
                        <tr>
                            <th data-field="sl" data-sortable="true">S/L</th>
                            <th data-field="name" data-sortable="true">View Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($databaseInformation['view']['views'] as $key => $view)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $view }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <h4 class="col-6 text-danger">Procedure Information</h4>
            <p class="text-end py-0 my-0  small-font"><strong>Count:</strong> {{ count($databaseInformation['procedure']['procedures']) }}</p>
        </div>

        <div class="row">
            <div class="col-md-12">

                <table id="table-procedure" class="table table-bordered table-striped sortable table-sm small-font table-hover">
                    <thead>
                        <tr>
                            <th data-field="sl" data-sortable="true">S/L</th>
                            <th data-field="name" data-sortable="true">Procedure Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($databaseInformation['procedure']['procedures'] as $key => $procedure)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $procedure }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <h4 class="col-6 text-danger">Function Information</h4>
            <p class="text-end py-0 my-0  small-font"><strong>Count:</strong> {{ count($databaseInformation['function']['functions']) }}</p>
        </div>

        <div class="row">
            <div class="col-md-12">

                <table id="table-function" class="table table-bordered table-striped sortable table-sm small-font table-hover">
                    <thead>
                        <tr>
                            <th data-field="sl" data-sortable="true">S/L</th>
                            <th data-field="name" data-sortable="true">Function Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($databaseInformation['function']['functions'] as $key => $function)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $function }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!-- jQuery via CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>


    <script>
        $(document).ready(function() {
            $('#table-table').bootstrapTable({ search: true   });
            $('#table-view').bootstrapTable({ search: true   });
            $('#table-procedure').bootstrapTable({ search: true   });
            $('#table-function').bootstrapTable({ search: true   });
        });
    </script>
</body>

</html>
