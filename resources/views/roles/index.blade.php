@extends('app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    {!! app('menu')->handler('roles.panel-buttons')->render('inline') !!}
                    <h4>
                        {{trans('roles.roles')}}
                    </h4>
                </div>
                <div class="panel-body">
                    {!! $DataTable->table(['class' => 'table table-bordered table-hover']) !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    {!! $DataTable->scripts() !!}
@endsection