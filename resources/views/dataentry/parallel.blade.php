@extends("layout")
@section("content")
<br />
<div class="row">
    <div class="col-lg-12">
        <ol class="breadcrumb">
            <li class="active">

                <i class="fa fa-dashboard"></i> {{ Lang::choice('messages.dashboard', 1) }}
            </li>
        </ol>
    </div>
</div>

 <br/>

<div class="panel panel-primary">
    <div class="panel-heading"><i class="fa fa-tags"></i> {{ Lang::choice('messages.page-summary-data-entry-parallel-algorithm', '1') }}</div>
    <div class="panel-body">
    @if($errors->all())
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                {!! HTML::ul($errors->all(), array('class'=>'list-unstyled')) !!}
            </div>
            @endif
{!! Form::open(array('route' => 'parallel.store', 'id' => 'form-parallel', 'class' => 'form-horizontal')) !!}


   <div class="row">
        <div class="col-md-2">
            <div class="row">
                <div class="col-md-2">
                   {!! Form::label('test_site_id', Lang::choice('messages.test-site', 1)) !!}
                </div>
                <div class="col-md-8">
                    {!! Form::select('test_site', array(''=>trans('messages.select-site'))+$sites,'', 
                            array('class' => 'form-control', 'id' => 'test_site')) !!}
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="row">
                <div class="col-md-3">
                     {!! Form::label('book_no', Lang::choice('messages.book-no', 1)) !!}
                </div>
                <div class="col-md-5">
                    {!! Form::text('book_no', Input::old('book_no'), array('class' => 'form-control')) !!} 
                </div>
            </div>
        </div>
         <div class="col-md-2">
            <div class="row">
                <div class="col-md-3">
                     {!! Form::label('page_no', Lang::choice('messages.page-no', 1)) !!}
                </div>
                <div class="col-md-5">
                    {!! Form::text('page_no', Input::old('page_no'), array('class' => 'form-control')) !!} 
                </div>
            </div>
        </div>
         <div class="col-md-3">
            <div class="row">
               {!! Form::label('start_date', Lang::choice('messages.start-date', 1), array('class' => 'col-md-2 control-label')) !!}
                    <div class=" input-group input-append date datepicker" id="date-of-birth" >
                        {!! Form::text('start_date', Input::old('start_date'), array('class' => 'form-control')) !!}
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                    </div>
                    
            </div>
        </div>
        <div class="col-md-3">
            <div class="row">
               {!! Form::label('end_date', Lang::choice('messages.end-date', 1), array('class' => 'col-md-2 control-label')) !!}
                    <div class=" input-group input-append date datepicker" id="date-of-birth" >
                        {!! Form::text('end_date', Input::old('end_date'), array('class' => 'form-control')) !!}
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                    </div>
                    
            </div>
        </div>
    </div>


<div class="row">
            <div class="col-md-4">
            <div class="row">
                <div class="col-md-5">
                 {!! Form::label('test_kit1_id', Lang::choice('messages.test-kit1', 1), array('class' => 'control-label')) !!}
                </div>
                <div class="col-md-5">

                    {!! Form::select('test_kit1', array(''=>trans('messages.test-kit1'))+$assignedtestkits,'', 
                            array('class' => 'form-control', 'id' => 'test_kit1')) !!}
                </div>
                </div>
            </div>
            <div class="col-md-4">
            <div class="row">
                <div class="col-md-5">
                 {!! Form::label('test_kit2_id', Lang::choice('messages.test-kit2', 2), array('class' => 'control-label')) !!}
                </div>
                <div class="col-md-6">

                    {!! Form::select('test_kit2', array(''=>trans('messages.test-kit2'))+$assignedtestkits,'', 
                            array('class' => 'form-control', 'id' => 'test_kit2')) !!}
                </div>
                </div>
            </div>
            <div class="col-md-4">
            <div class="row">
                <div class="col-md-5">
                 {!! Form::label('test_kit3_id', Lang::choice('messages.test-kit3', 3), array('class' => 'control-label')) !!}
                </div>
                <div class="col-md-6">

                    {!! Form::select('test_kit3', array(''=>trans('messages.test-kit3'))+$assignedtestkits,'', 
                            array('class' => 'form-control', 'id' => 'test_kit3')) !!}
                </div>
                </div>
            </div>
</div>

<div class="row">
        <div class="col-md-1">
            <div class="row">
                <div class="col-md-5">
                   {!! Form::label('test_kit1', Lang::choice('messages.test-kit1', 1), array('class' => 'control-label')) !!}
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="row">
                <div class="col-md-2">
                     {!! Form::label('test_kit1R', Lang::choice('messages.R', 1), array('class' => 'control-label')) !!}
                </div>
                <div class="col-md-5">
                    {!! Form::text('test_kit1R', Input::old('test_kit1R'), array('class' => 'form-control')) !!} 
                </div>
            </div>
        </div>
         <div class="col-md-2">
            <div class="row">
                <div class="col-md-2">
                     {!! Form::label('test_kit1NR', Lang::choice('messages.NR', 1), array('class' => 'control-label')) !!}
                </div>
                <div class="col-md-5">
                    {!! Form::text('test_kit1NR', Input::old('test_kit1NR'), array('class' => 'form-control')) !!} 
                </div>
            </div>
        </div>
         <div class="col-md-2">
            <div class="row">
                <div class="col-md-2">
                    {!! Form::label('test_kit1Inv', Lang::choice('messages.Inv', 1), array('class' => 'control-label')) !!}  
                     </div>
                <div class="col-md-5">
                    {!! Form::text('test_kit1Inv', Input::old('test_kit1Inv'), array('class' => 'form-control')) !!} 
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-1">
            <div class="row">
                <div class="col-md-4">
                   {!! Form::label('test_kit2', Lang::choice('messages.test-kit2', 1), array('class' => 'control-label')) !!}
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="row">
                <div class="col-md-2">
                     {!! Form::label('test_kit2R', Lang::choice('messages.R', 1), array('class' => 'control-label')) !!}
                </div>
                <div class="col-md-5">
                    {!! Form::text('test_kit2R', Input::old('test_kit1R'), array('class' => 'form-control')) !!} 
                </div>
            </div>
        </div>
         <div class="col-md-2">
            <div class="row">
                <div class="col-md-2">
                     {!! Form::label('test_kit2NR', Lang::choice('messages.NR', 1), array('class' => 'control-label')) !!}
                </div>
                <div class="col-md-5">
                    {!! Form::text('test_kit2NR', Input::old('test_kit2NR'), array('class' => 'form-control')) !!} 
                </div>
            </div>
        </div>
         <div class="col-md-2">
            <div class="row">
                <div class="col-md-2">
                    {!! Form::label('test_kit2Inv', Lang::choice('messages.Inv', 1), array('class' => 'control-label')) !!}   
                </div>
                <div class="col-md-5">
                    {!! Form::text('test_kit2Inv', Input::old('test_kit2Inv'), array('class' => 'form-control')) !!} 
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-1">
            <div class="row">
                <div class="col-md-4">
                   {!! Form::label('test_kit3', Lang::choice('messages.test-kit3', 1), array('class' => 'control-label')) !!}
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="row">
                <div class="col-md-2">
                     {!! Form::label('test_kit3R', Lang::choice('messages.R', 1), array('class' => 'control-label')) !!}
                </div>
                <div class="col-md-5">
                    {!! Form::text('test_kit3R', Input::old('test_kit3R'), array('class' => 'form-control')) !!} 
                </div>
            </div>
        </div>
         <div class="col-md-2">
            <div class="row">
                <div class="col-md-2">
                     {!! Form::label('test_kit3NR', Lang::choice('messages.NR', 1), array('class' => 'control-label')) !!}
                </div>
                <div class="col-md-5">
                    {!! Form::text('test_kit3NR', Input::old('test_kit3NR'), array('class' => 'form-control')) !!} 
                </div>
            </div>
        </div>
         <div class="col-md-2">
            <div class="row">
                <div class="col-md-2">
                    {!! Form::label('test_kit3Inv', Lang::choice('messages.Inv', 1), array('class' => 'control-label')) !!}   
                </div>
                <div class="col-md-5">
                    {!! Form::text('test_kit3Inv', Input::old('test_kit3Inv'), array('class' => 'form-control')) !!} 
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-1">
            <div class="row">
                <div class="col-md-4">
                   {!! Form::label('result', Lang::choice('messages.final-result', 1), array('class' => 'control-label')) !!}
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="row">
                <div class="col-md-4">
                     {!! Form::label('positive', Lang::choice('messages.positive', 1), array('class' => 'control-label')) !!}
                </div>
                <div class="col-md-5">
                    {!! Form::text('positive', Input::old('positive'), array('class' => 'form-control')) !!} 
                </div>
            </div>
        </div>
         <div class="col-md-2">
            <div class="row">
                <div class="col-md-6">
                     {!! Form::label('negative', Lang::choice('messages.negative', 1), array('class' => 'control-label')) !!}
                </div>
                <div class="col-md-5">
                    {!! Form::text('negative', Input::old('negative'), array('class' => 'form-control')) !!} 
                </div>
            </div>
        </div>
         <div class="col-md-2">
            <div class="row">
                <div class="col-md-7">
                    {!! Form::label('indeterminate', Lang::choice('messages.indeterminate', 1), array('class' => 'control-label')) !!}  
                     </div>
                <div class="col-md-5">
                    {!! Form::text('indeterminate', Input::old('indeterminate'), array('class' => 'form-control')) !!} 
                </div>
            </div>
        </div>
         
                    <div class="col-sm-offset-1 col-sm-3">
                    {!! Form::button("<i class='glyphicon glyphicon-ok-circle'></i> ".Lang::choice('messages.submit', 1), 
                          array('class' => 'btn btn-success', 'onclick' => 'submit()')) !!}
                          {!! Form::button("<i class='glyphicon glyphicon-remove-circle'></i> ".'Reset', 
                          array('class' => 'btn btn-default', 'onclick' => 'reset()')) !!}
                     </div>
                
    </div>

 {!! Form::close() !!} 

      </div>
</div>
@stop