@extends('template')

@section('content')
    <div id="content">
        <div class="col-xs-2">
            <ul id="tabs " class="nav nav-tabs tabs-left" data-tabs="tabs">
                <?php $count = 0; ?>
                @foreach ($centerPeopleList as $centerName => $centerData)
                    <li {!! $count === 0 ?  'class="active"' : '' !!}><a href="#{{ preg_replace('/[ ,]/', '', $centerName) }}-tab" data-toggle="tab">{{ $centerName }}</a></li>
                    <?php $count++; ?>
                @endforeach
            </ul>
        </div>
        <div class="col-xs-10">
            <div class="tab-content">
                <?php $count = 0; ?>
                @foreach ($centerPeopleList as $centerName => $people)
                    <div class="tab-pane {{ $count === 0 ? 'active' : '' }}" id="{{ preg_replace('/[ ,]/', '', $centerName) }}-tab">
                        <h1>{{ $centerName }} - People Summary</h1>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover peopleTable">
                                <thead>
                                <tr>
                                    <th>First</th>
                                    <th>Last</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Team Member Id</th>
                                    <th>Accountabilities</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($people as $person)
                                    <tr>
                                        <td>{{ $person->firstName }}</td>
                                        <td>{{ $person->lastName }}</td>
                                        <td>{{ $person->phone }}</td>
                                        <td>{{ $person->email }}</td>
                                        <td>{{ $person->teamMember ? $person->teamMember->id : '' }}</td>
                                        <td>
                                            @if ($person->accountabilities)
                                                <ul>
                                                    @foreach ($person->accountabilities as $accountability)
                                                        <li>{{ $accountability->name }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php $count++; ?>
                @endforeach
            </div>
        </div>
    </div>

    <script src="{{ asset('/js/query.dataTables.min.js') }}"></script>
    <script src="{{ asset('/js/dataTables.bootstrap.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#tabs').tab();
            $('table.peopleTable').dataTable({
                "paging": false,
                "searching": false
            });
        });
    </script>
@endsection
