@extends ('core.layouts.app')
@section ('title', trans('labels.backend.products.management') . ' | ' . trans('products.stock_transfer'))

@section('page-header')
    <h1>
        {{ trans('labels.backend.products.management') }}
        <small>{{ trans('labels.backend.products.create') }}</small>
    </h1>
@endsection
@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-body">
                <div class="card">
                    <div class="card-header">
                        <h5> {{trans('products.stock_transfer') }}</h5>
                        <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                        <div class="heading-elements">
                            <ul class="list-inline mb-0">
                                <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                                <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                                <li><a data-action="close"><i class="ft-x"></i></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-content">
                        <div id="notify" class="alert alert-success" style="display:none;">
                            <a href="#" class="close" data-dismiss="alert">&times;</a>

                            <div class="message"></div>
                        </div>
                        <div class="card-body">
                            {{ Form::open(['route' => 'biller.products.stock_transfer_post', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post','files' => false, 'id' => 'create-transfer']) }}
                            <div class="form-group row">


                                <div class="col-sm-4"><label class="col-form-label"
                                                             for="product_cat">{{trans('products.stock_transfer_from') }}</label>
                                    <select id="wfrom" name="from_warehouse" class="form-control">
                                        <option value='0'>Select</option>
                                        <?php
                                        foreach ($warehouses as $row) {
                                            $cid = $row['id'];
                                            $title = $row['title'];
                                            echo "<option value='$cid'>$title</option>";
                                        }
                                        ?>
                                    </select>


                                </div>

                                <div class="col-sm-4"><label class="col-form-label"
                                                             for="product_cat">{{trans('products.stock_transfer_to') }}</label>
                                    <select id="wto" name="to_warehouse" class="form-control">
                                        <option value='0'>Select</option>
                                        <?php
                                        foreach ($warehouses as $row) {
                                            $cid = $row['id'];
                                            $title = $row['title'];
                                            echo "<option value='$cid'>$title</option>";
                                        }
                                        ?>
                                    </select>


                                </div>
                                <div class="col-sm-4"><label class="col-form-label"
                                                             for="product_cat">{{trans('products.existing_product') }}</label>
                                    <select id="wto" name="merger" class="form-control">
                                        <option value='1'
                                                selected>{{trans('products.merge_only_if_code_match')}}</option>
                                        <option value='0'>{{trans('products.not_applicable')}}</option>
                                        <option value='2'>{{trans('products.merge_if_code_is_empty')}}</option>

                                    </select>


                                </div>


                                <div class="col-sm-8"><label class="col-form-label"
                                                             for="pay_cat">{{trans('products.product') }}</label>
                                    <select id="products_l" name="products_l[]" class="form-control required select-box"
                                            multiple="multiple">

                                    </select>


                                </div>
                            </div>

                            <div class="form-group row">


                                <div class="col-8"><label class="col-form-label"
                                                          for="width">  {{trans('products.qty') }}</label>
                                    <input name="qty" class="form-control required" type="text" value="1">
                                    <small>{{trans('products.use_comma')}}</small>

                                </div>

                                <div class="col-sm-4"><label class="col-form-label"
                                    for="type_action">{{trans('products.type_action') }}</label>
                                    <select id="type_action" name="type_action" class="form-control">
                                        <option value='1'
                                                selected>{{trans('products.temporaly_loan')}}</option>
                                        <option value='0'>{{trans('products.permanent_loan')}}</option>
                                        <option value='2'>{{trans('products.return')}}</option>

                                    </select>


                                </div>

                            </div>


                            <div class="form-group row">


                                <div class="col-sm-4">
                                    <input type="submit" class="btn btn-success margin-bottom"
                                           value="{{trans('products.stock_transfer')}}"
                                           data-loading-text="Adding...">

                                </div>
                            </div>
                        </div>

                        {{ Form::close() }}
                    </div>
                    <div class="content-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
        
                                    <div class="card-content">
        
                                        <div class="card-body">
                                            <table id="products-table2"
                                                   class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                                   width="100%">
                                                <thead>
                                                <tr>
                                                    <th>{{ trans('labels.backend.products.table.id') }}</th>
                                                    <th>{{ trans('products.name') }}</th>
                                                    <th>{{ trans('products.productcategory_id') }}</th>
                                                    <th>{{ trans('products.warehouse_id') }} de Origen</th>
                                                    <th>{{ trans('products.warehouse_id') }} de Destino</th>
        
                                                    <th>{{ trans('products.qty') }}</th>
                                                    <th>{{ trans('products.price') }}</th>
                                                    <th>{{ trans('general.createdat') }}</th>
                                                    <th>{{ trans('products.type_action') }}</th>
                                                    {{-- <th>{{ trans('labels.general.actions') }}</th> --}}
                                                </tr>
                                                </thead>
        
        
                                                <tbody>
                                                <tr>
                                                    <td colspan="100%" class="text-center text-success font-large-1"><i
                                                                class="fa fa-spinner spinner"></i></td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
        
        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('extra-scripts')
    {{ Html::script('focus/js/select2.min.js') }}
    {{ Html::script(mix('js/dataTable.js')) }}

    <script type="text/javascript">
        $("#products_l").select2();
        $("#wfrom").on('change', function () {
            var tips = $('#wfrom').val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $("#products_l").select2({

                tags: [],
                ajax: {
                    url: '{{route('biller.products.product_search_post',['label'])}}',
                    dataType: 'json',
                    type: 'POST',
                    quietMillis: 50,
                    data: function (product) {

                        return {
                            keyword: product,
                            wid: tips

                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.name,
                                    id: item.id
                                }
                            })
                        };
                    },
                }
            });
        });

        $(function () {
            setTimeout(function () {
                draw_data();
            }, {{config('master.delay')}});
        });

        function draw_data() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var dataTable = $('#products-table2').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,
                language: {
                    @lang('datatable.strings')
                },
                ajax: {
                    url: '{{ route("biller.variation.get") }}',
                    type: 'post',
                    @if($segment) data: {p_rel_id: '{{$segment['id']}}', p_rel_type: '{{$input['rel_type']}}'},@endif },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'category', name: 'category'},
                    {data: 'from_w', name: 'from_w'},
                    {data: 'warehouse', name: 'warehouse'},
                    {data: 'qty', name: 'qty'},
                    {data: 'price', name: 'price'},
                    {data: 'created_at', name: '{{config('module.products.table2')}}.created_at'},
                    {data: 'type_action', name: 'type_action'},
                    // {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "asc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: {
                    buttons: [

                        {extend: 'csv', footer: true, exportOptions: {columns: [0, 1, 2, 3, 4, 5, 6, 7,8]}},
                        {extend: 'excel', footer: true, exportOptions: {columns: [0, 1, 2, 3, 4, 5, 6, 7,8]}},
                        {extend: 'print', footer: true, exportOptions: {columns: [0, 1, 2, 3, 4, 5, 6, 7,8]}}
                    ]
                },
            });
            $('#products-table_wrapper').removeClass('form-inline');

        }
    </script>
@endsection
