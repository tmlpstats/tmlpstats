<div class="modal fade" tabindex="-1" role="dialog" id="liveScoreboardHelp">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Live Scoreboard Help</h4>
      </div>
      <div class="modal-body">
        <p>The live scoreboard exists as a tool that lets statisticians update their teams during the week. This is an experiment that we are testing out, which allows us to test systems on how stats submission may look in the future.</p>

        <h4>How to use</h4>
        <p>The statistician goes and edits the scoreboard however often they want. They can then send the scoreboard to their team by sending them the link to this mobile friendly dashboard:</p>
        <h4><a href="{{ $mobileDashUrl }}" target="_blank">{{ $mobileDashUrl }}</a></h4>
        <p>The mobile dashboard can be used on iPhone, Android, iPad, and PC/Mac so feel free to text this link to people on your team.</p>

        <p>Some details:</p>
        <ul>
          <li>The scoreboard shows the promises for the <b>upcoming</b> Friday</li>
          <li>As often as the statistician wants, they can edit the "actuals" during the week</li>
          <li>When a new stats are submitted, the scoreboard is reset to whatever was on your sheet</li>
        </ul>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <!--<button type="button" class="btn btn-primary">Save changes</button>-->
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
