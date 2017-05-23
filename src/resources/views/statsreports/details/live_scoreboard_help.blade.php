<div class="modal fade" tabindex="-1" role="dialog" id="liveScoreboardHelp">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Temporary Scoreboard Help</h4>
      </div>
      <div class="modal-body">
        <p>The temporary scoreboard is a tool that lets statisticians update their team's actuals during the week. This can be used as a &quot;weather report&quot; and/or providing updates for your accountables.</p>

        <h4>How to use</h4>
        <p>The statistician goes and edits the scoreboard however often they want. They can then send the scoreboard to their team by sending them the link to this mobile friendly dashboard:</p>
        <h4><a href="{{ $mobileDashUrl }}" target="_blank">{{ $mobileDashUrl }}</a></h4>
        <p>The mobile dashboard can be used on iPhone, Android, iPad, and PC/Mac so feel free to text this link to people on your team.</p>

        <p>Some details:</p>
        <ul>
          <li>The scoreboard shows the promises for the <b>upcoming</b> Friday</li>
          <li>As often as the statistician wants, they can edit the "actuals" during the week</li>
          <li>When a new excel sheet is submitted, the scoreboard is reset to whatever was on the new sheet</li>
        </ul>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <!--<button type="button" class="btn btn-primary">Save changes</button>-->
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
