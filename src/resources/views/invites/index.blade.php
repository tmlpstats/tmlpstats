@extends('template')

@section('content')
    <h2>Invitations</h2>
    <a href="{{ url('/users/invites/create') }}">+ Add one</a>
    <br/><br/>

    <div id="messages" class="alert alert-danger" role="alert" style="display:none">
        <a href="#" class="close" data-dismiss="alert">&times;</a>
        <span class="message-prefix" style="font-weight:bold">Error: </span>
        <span class="message"></span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover want-datatable">
            <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Center</th>
                <th>Invited By</th>
                <th>Last Sent</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($invites as $invite)
                <tr id="{{ $invite->id }}">
                    <td><a href="{{ url("/users/invites/{$invite->id}/edit") }}">{{ $invite->firstName }} {{ $invite->lastName }}</a></td>
                    <td>{{ $invite->email }}</td>
                    <td>{{ $invite->role ? $invite->role->display : '' }}</td>
                    <td>{{ $invite->center ? $invite->center->name : '' }}</td>
                    <td>{{ $invite->invitedByUser->firstName }} {{ $invite->invitedByUser->lastName }}</td>
                    <td>
                        @if ($invite->emailSentAt)
                        {{ Auth::user()->toLocalTimezone($invite->emailSentAt)->format('M j, Y \a\t g:i a T') }}
                        @else
                        Not Sent
                        @endif
                    </td>
                    <td>
                        <a href="#" class="delete" title="Delete" style="color: black">
                            <span class="glyphicon glyphicon-remove"></span>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <script>
        $("a.delete").click(function() {
            var id = $(this).closest('tr').attr('id');
            $.ajax({
                type: "DELETE",
                url: "/users/invites/" + id + "/revoke",
                beforeSend: function (request) {
                    request.setRequestHeader("X-CSRF-TOKEN", "{{ csrf_token() }}");
                },
                success: function(response) {
                    var $messages = $("#messages");
                    var removeClass = "alert-success";
                    var addClass = "alert-danger";
                    var messagePrefix = "Error: ";

                    if (response.success) {
                        removeClass = "alert-danger";
                        addClass = "alert-success";
                        messagePrefix = "Success! ";
                    }

                    $messages.removeClass(removeClass);
                    $messages.addClass(addClass);
                    $messages.find("span.message-prefix").text(messagePrefix);
                    $messages.find("span.message").text(response.message);
                    $messages.show();

                    if (response.success) {
                        $("#" + response.invite).remove();
                    }
                }
            });
        });
    </script>
@endsection
