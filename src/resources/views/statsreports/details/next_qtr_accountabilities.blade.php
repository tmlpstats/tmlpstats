<div class="table-responsive">
    <table class="table table-condensed table-striped table-hover">
        <thead>
        <tr>
            <th>Accountability</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Email</th>
        </tr>
        </thead>
        <tbody>
        @foreach($nqAccountabilities as $idx => $nqa)
            <tr>
                <td>{{ $nqa->getAccountability()->display }}</td>
                <td>{{ $nqa ? $nqa->name : 'N/A' }}</td>
                <td>{{ $nqa ? $nqa->phone : 'N/A' }}</td>
                <td>{{ $nqa ? $nqa->email : 'N/A' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
