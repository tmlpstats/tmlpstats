<div style="margin-top: 10px;">

    <h1>Classrooms</h1>

    <table style="max-width: 300px" class="table table-condensed table-striped table-hover">
        <thead>
        <tr>
            <th class="border-left border-right border-top">Name</th>
            <th class="data-point border-right border-top"># Participants</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($reportData as $classroom => $numParticipants)
            <tr>
                <td class="border-left border-right border-bottom">
                    {{ $classroom }}
                </td>
                <td class="data-point border-right border-bottom">{{ $numParticipants }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
