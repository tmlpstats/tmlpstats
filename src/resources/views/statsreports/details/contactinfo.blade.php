<div class="table-responsive">
    <table class="table table-condensed table-striped">
        <thead>
        <tr>
            <th>Accountability</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Phone</th>
            <th>Email</th>
        </tr>
        </thead>
        <tbody>
        @foreach($contacts as $accountability => $contact)
            <tr>
                <td>{{ $accountability }}</td>
                <td>{{ $contact ? $contact->firstName : 'N/A' }}</td>
                <td>{{ $contact ? $contact->lastName : 'N/A' }}</td>
                <td>{{ $contact ? $contact->phone : 'N/A' }}</td>
                <td>{{ $contact ? $contact->email : 'N/A' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
