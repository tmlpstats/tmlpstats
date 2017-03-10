<div class="hs">
    <button id="contactLink" href="#" title="Feedback">
        <div id="feedbackTabText">Feedback</div>
    </button>
</div>

<div class="modal fade" id="feedbackModel" tabindex="-1" role="dialog" aria-labelledby="feedbackModelLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="feedbackModelLabel">Send us your feedback</h4>
            </div>
            <div class="modal-body">
                We would love to hear your feedback! If you have any comments, ideas, suggestions, issues, or anything else, please let us know below.
                <br/><br/>
                Also, if you or a TMLP member/graduate you know are interested in participating in creating a new future of statistics for TMLP, let us know that too!
                <br/><br/>
                <div id="feedbackSubmitResult" class="alert" role="alert" style="display:none">
                    <span class="message"></span>
                </div>
                <div id="feedbackForm">
                    {!! Form::open(['url' => url('/feedback')]) !!}

                    <br/>
                    {!! Form::label('name', 'Name:', ['class' => 'control-label']) !!}
                    {!! Form::text('name', null, ['class' => 'form-control']) !!}
                    <br/>
                    {!! Form::label('email', 'Email:', ['class' => 'control-label']) !!}
                    {!! Form::email('email', null, ['class' => 'form-control']) !!}
                    <br/>
                    {!! Form::label('message', 'Message:', ['class' => 'control-label']) !!}
                    {!! Form::textarea('message', null, ['class' => 'form-control', 'rows' => '10']) !!}
                    <br/>
                    {!! Form::checkbox('copySender', 1, true, []) !!} Send me a copy

                    {!! Form::close() !!}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" id="submitFeedbackCancel">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitFeedback">Send</button>
            </div>
        </div>
    </div>
</div>
