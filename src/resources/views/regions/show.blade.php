@extends('template')

@section('content')

    <h2>{{ $region->name }}</h2>
    <a href="{{ url('/regions') }}"><< See All</a><br/><br/>


    @if ($region->email)
    <div class="form-group">
        <label class="col-sm-2 control-label">Region Email:</label>
        <div class="col-sm-2">
            {{ $region->email }}
        </div>
        <div class="col-sm-5">
            {{--<a href="{{ url("/regions/{$region->id}/edit") }}">Edit</a>--}}
        </div>
    </div>
    <br />
    @endif

    @if (Session::get('message'))
    <div class="alert alert-{{ Session::get('success') ? 'success' : 'danger' }}" role="alert">
        <p>{{ Session::get('message') }}</p>
    </div>
    @endif

    <h3>Centers</h3>
    <div class="select-action-pill">
        <ul class="nav nav-pills">
            <li role="presentation" class="dropdown active">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                    Actions <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="#" data-toggle="modal" data-target="#updateVersionModel">Update Version</a></li>
                    <li><a href="#" data-toggle="modal" data-target="#updatePasswordModel">Update Password</a></li>
                    {{--<li><a href="#">Add Setting</a></li>--}}
                </ul>
            </li>
        </ul>
    </div>

    <div class="modal fade" id="updateVersionModel" tabindex="-1" role="dialog" aria-labelledby="updateVersionModel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="updateVersionModel">Update Version</h4>
                </div>
                {!! Form::open(['url' => "admin/centers", 'class' => 'centersUpdateForm']) !!}
                <div class="modal-body">
                    <div class="form-group">
                        {!! Form::label('sheet_version', 'Sheet Version:', ['class' => 'col-sm-3 control-label']) !!}
                        <div class="col-sm-5">
                            {!! Form::text('sheet_version', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                    <br />
                    <br />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

    <div class="modal fade" id="updatePasswordModel" tabindex="-1" role="dialog" aria-labelledby="updatePasswordModel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="updatePasswordModel">Update Password</h4>
                </div>
                {!! Form::open(['url' => "admin/centers", 'class' => 'centersUpdateForm']) !!}
                <div class="modal-body">
                    <div class="form-group">
                        {!! Form::label('new_password', 'New Password:', ['class' => 'col-sm-3 control-label']) !!}
                        <div class="col-sm-5">
                            {!! Form::password('new_password', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                    <br/>
                    <div class="form-group">
                        {!! Form::label('confirm_password', 'Confirm Password:', ['class' => 'col-sm-3 control-label']) !!}
                        <div class="col-sm-5">
                            {!! Form::password('confirm_password', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                    <br />
                    <br />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

    <div class="table-responsive">
        {!! Form::open(['url' => "regions/{$region->id}"]) !!}
        <table id="mainTable" class="table table-condensed table-hover">
            <thead>
                <th><input type="checkbox" id="checkAll" /></th>
                <th>Name</th>
                <th>Sheet Version</th>
                <th>Email</th>
            </thead>
            <tbody>
            @foreach ($region->centers as $center)
            <tr>
                <td>{!! Form::checkbox("centers[]", $center->id, null, ['class' => 'checkedCenter']) !!}</td>
                <td><a href="{{ url("admin/centers/{$center->abbreviation}/edit") }}">{{ $center->name }}</a></td>
                <td>{{ $center->sheetVersion }}</td>
                <td>{{ $center->statsEmail }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
        {!! Form::close() !!}
    </div>

    <br/>
    <br/>


    <script>
        $(document).ready(function() {
            $('#mainTable').dataTable({
                "paging":    false,
                "searching": false
            });

            $('#checkAll').on('click', function () {
                $('input:checkbox').prop('checked', this.checked);
            });

            $('form.centersUpdateForm').submit(function( event ) {
                var centerIds = [];
                $.each($("input:checkbox[class=checkedCenter]:checked"), function(){
                    centerIds.push($(this).val());
                });

                var data = {};
                if (centerIds) {
                    data.centerIds = centerIds;

                    var sheetVersion = $('input[name=sheet_version]').val()
                    if (sheetVersion) {
                        data.sheetVersion = sheetVersion;
                    }

                    var newPassword = $('input[name=new_password]').val()
                    if (newPassword) {
                        data.newPassword = newPassword;
                        data.confirmPassword = $('input[name=confirm_password]').val();
                    }
                }

                if (!$.isEmptyObject(data)) {
                    $.ajax({
                        type: "POST",
                        url: "{{ url('admin/centers') }}",
                        beforeSend: function (request) {
                            request.setRequestHeader("X-CSRF-TOKEN", "{{ csrf_token() }}");
                        },
                        data: $.param(data),
                        success: function () {
                            location.reload();
                        }
                    });
                }
                event.preventDefault();
            });
        });
    </script>
@endsection
