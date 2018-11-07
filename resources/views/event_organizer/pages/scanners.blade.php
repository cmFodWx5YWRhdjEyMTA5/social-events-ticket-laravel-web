@extends('common_pages.layouts')

@section('testing-styles')
<style>

</style>
    
@endsection

@section('content')
    @include('includes.header')
    @include('includes.side-menu')

    <main class="app-content">
      <div class="app-title">
        <div>
          <h1><i class="fa fa-user-secret"></i> Scanners</h1>
          <p>List of all scanners from {{$event_name}} event</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
          <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
          <li class="breadcrumb-item"><a href="{{ route('admin_home') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('event_organizer_unverified_events') }}">Events</a></li>
          <li class="breadcrumb-item"><a href="{{ route('scanners') }}">Scanners</a></li>
        </ul>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="tile">  
              <p><a class="btn btn-primary icon-btn float-right" href="{{ route('add_scanner') }}" onclick="event.preventDefault(); document.getElementById('scanner-form-{{$event_id}}').submit();"><i class="fa fa-plus"></i>Add Scanner</a></p><br><br>
                <form id="scanner-form-{{$event_id}}" action="{{ route('add_scanner') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                    <input type="hidden" name="id" value="{{$event_id}}">
                </form>
            <div class="tile-body">
              <table class="table table-hover table-bordered" id="adminsTable">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Eamil</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                    @foreach ($scanners as $scanner)
                    <tr class="item">
                        <td>{{ $scanner->first_name}} {{ $scanner->last_name}}</td>                        
                        <td>{{$scanner->email}}</td>
                        <td>
                          <button onClick="document.getElementById('edit_form_{{$scanner->id}}').submit();" class="btn btn-sm btn-outline-primary">Edit</button>
                          <form id="edit_form_{{$scanner->id}}" action="{{ route('edit_scanner') }}" method="POST" style="display: none;">
                              {{ csrf_field() }}
                              <input type="hidden" name="id" value="{{$scanner->id}}">
                              <input type="hidden" name="event_status" value="{{$scanner->event_status}}">
                              <input type="hidden" name="event_status" value="{{$scanner->event_status}}">
                          </form>

                          <button onClick="deleteBtn({{$scanner->id}})" class="btn btn-sm btn-outline-danger">Delete</button>
                          <form id="delete_form_{{$scanner->id}}" action="{{ route('delete_scanner') }}" method="POST" style="display: none;">
                              {{ csrf_field() }}
                              <input type="hidden" name="id" value="{{$scanner->id}}">
                              <input type="hidden" name="event_status" value="{{$scanner->event_status}}">
                          </form>
                        </td>
                    </tr>                        
                    @endforeach                
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>
@endsection

@section('other-scripts')
<!-- Data table plugin-->
<script type="text/javascript" src="{{ asset('js/plugins/jquery.dataTables.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/plugins/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">$('#adminsTable').DataTable();</script>
<script type="text/javascript" src="{{ asset('js/plugins/bootstrap-notify.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/plugins/sweetalert.min.js') }}"></script>
<script>
  function deleteBtn(id) {    
    swal({
      		title: "Are you sure?",
      		text: "You will not be able to recover this record",
      		type: "warning",
      		showCancelButton: true,
      		confirmButtonText: "Yes, delete it!",
      		cancelButtonText: "No, cancel!",
      		closeOnConfirm: false,
      		closeOnCancel: true
      	}, function(isConfirm) {
          if (isConfirm) {
            $form ="delete_form_"+id;
      			document.getElementById($form).submit();
      		}
                		
      	});
  }
  
</script>
@if (session('status'))
    <script type="text/javascript">
      $.notify({
            title: "Success : ",
            message: "{{ session('status') }}",
            icon: 'fa fa-check' 
          },{
            type: "info"
      });
    </script>        
@endif 
@endsection