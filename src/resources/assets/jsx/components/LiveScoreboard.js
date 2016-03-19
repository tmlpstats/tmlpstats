import React from 'react';
import GameRow from './GameRow';

var LiveScoreboard = React.createClass({
    getInitialState: function() {
        return {
            editable: true,
            rating: '',
            points: 0,
            games: {
                cap: {
                    promise: '',
                    actual: '',
                    percent: '',
                    points: '',
                },
                cpc: {
                    promise: '',
                    actual: '',
                    percent: '',
                    points: '',
                },
                t1x: {
                    promise: '',
                    actual: '',
                    percent: '',
                    points: '',
                },
                t2x: {
                    promise: '',
                    actual: '',
                    percent: '',
                    points: '',
                },
                gitw: {
                    promise: '',
                    actual: '',
                    percent: '',
                    points: '',
                },
                lf: {
                    promise: '',
                    actual: '',
                    percent: '',
                    points: '',
                },
            }
        };
    },
    refreshData: function () {
        var self = this,
            request = {center: settings.center.abbreviation};

        console.log("Getting stats for center: " + settings.center.name)

        Api.LiveScoreboard.getCurrentScores(request, function (data) {
            self.updateScoreboard(data);
        });
    },
    componentDidMount: function() {
        // Start data fetch request as soon as the component loads
        this.refreshData();
    },
    updateScoreboard: function (data) {
        var games = this.formatData(data);

        this.setState({
            games: games,
            rating: data.rating,
            points: data.points.total,
        });
    },
    updateGameData: function (game, field) {
        var self = this,
            request = {},
            value = this.state.games[game][field];

        if (value == '') {
            value = 0;
            this.state.games[game][field] = 0;
            this.setState({games: this.state.games});
        }

        request = {
            center: settings.center.abbreviation,
            game: game,
            type: field,
            value: value,
        };

        console.log("updating " + game);

        Api.LiveScoreboard.setScore(request, function (data) {
            self.updateScoreboard(data);
        });
    },
    handleGameOnChange: function (game, field, value) {
        value = value.replace(/\D/g,'');

        this.state.games[game][field] = value;
        this.setState({games: this.state.games});
    },
    formatData: function (data) {
        var output = {},
            games = Object.keys(data.promise);

        for (var i in games) {
            var game = games[i];
            output[game] = {
                game: game,
                promise: data.promise[game],
                actual: data.actual[game],
                percent: data.percent[game],
                points: data.points[game],
            };
        }

        return output;
    },
    renderGameRow: function (game) {
        return <GameRow key={game} game={game} data={this.state.games[game]} editable={this.state.editable} updateGameData={this.updateGameData} handleGameOnChange={this.handleGameOnChange} />
    },
    render: function () {
        var date = moment().format('MMMM D'),
            games = this.state.games,
            rating = this.state.rating,
            points = this.state.points;

        return (
            <div className="table-responsive">
                <table className="table table-condensed table-bordered table-striped centerStatsSummaryTable">
                    <thead>
                        <tr>
                            <th rowSpan="2">&nbsp;</th>
                            <th colSpan="5" className="date border-top-thin border-right-thin">{date}</th>
                        </tr>
                        <tr>
                            <th className="promise">P</th>
                            <th>A</th>
                            <th>Gap</th>
                            <th>%</th>
                            <th className="border-right-thin">Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                        {Object.keys(games).map(this.renderGameRow)}
                        <tr>
                            <th className="border-left-thin" colSpan="4">{rating}</th>
                            <th className="total">Total:</th>
                            <th className="border-right-thin">{points}</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        )
    }
});

export default LiveScoreboard;
