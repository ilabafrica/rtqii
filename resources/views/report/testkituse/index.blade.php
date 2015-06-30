@extends("layout")
@section("content")
<br />
<div class="row">
    <div class="col-lg-12">
        <ol class="breadcrumb">
            <li class="active">
                <i class="fa fa-dashboard"></i> {{ Lang::choice('messages.trend-report', 1) }}
            </li>
        </ol>
    </div>
</div>

<div class="panel panel-primary">
    <div class="panel-heading"><i class="fa fa-tags"></i> {{ Lang::choice('messages.trend-report', '1') }}
    <span class="panel-btn">
        <a class="btn btn-sm btn-info" href="{{ URL::to("testkituse/create") }}" >
        <span class="glyphicon glyphicon-plus-sign"></span>
            {{ trans('messages.generate-report') }}
          </a>
    </span></div>
   <div class="panel-body">
        <div class="row">
            <div class="col-sm-12">
                <table class="table table-striped table-bordered table-hover search-table">
                    <thead>
                        <tr>
                            <th rowspan="2">{{ Lang::choice('messages.site', 1) }}</th> 
                            <th rowspan="2">{{ Lang::choice('messages.algorithm', 1) }}</th>  
                            <th rowspan="2">{{ Lang::choice('messages.start-date', 1) }}</th> 
                            <th rowspan="2">{{ Lang::choice('messages.end-date', 1) }}</th>                 
                            <th rowspan="2"  class="success">{{ Lang::choice('messages.test1-used', 1) }}</th>
                            <th rowspan="2"  class="danger">{{ Lang::choice('messages.test2-used', 1) }}</th>
                            <th rowspan="2"  class="info">{{ Lang::choice('messages.test3-used', 1) }}</th> 
                            <th rowspan="2">{{ Lang::choice('messages.invalid-results', 1) }}</th>                                                                           
                            <th rowspan="2"></th>                            
                        </tr>
                         
                    </thead>
                    <tbody>
                   
                    </tbody>
                </table>
            </div>
            {{ Session::put('SOURCE_URL', URL::full()) }}
        </div>
      </div>
</div>
@stop