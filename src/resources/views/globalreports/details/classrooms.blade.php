<div>
    <table class="table table-condensed table-striped table-hover">
        <thead>
        <tr>
            <th class="border-left border-right border-top">Classroom</th>
            <th class="data-point border-right border-top"># of Participants</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($reportData as $classroom => $numParticipants)
            <tr>
                <td class="border-left border-right">
                    {{ $classroom }}
                </td>
                <td class="data-point">{{ $numParticipants }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
