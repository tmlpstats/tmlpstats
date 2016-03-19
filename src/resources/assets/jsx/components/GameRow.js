import React from 'react';
import GameField from './GameField';

var GameRow = React.createClass({
    updateGameValue: function(field) {
        this.props.updateGameData(this.props.game, field);
    },
    handleFieldOnChange: function (field, value) {
        var game = this.props.game;

        this.props.handleGameOnChange(game, field, value);
    },
    render: function () {
        var game = this.props.game,
            data = this.props.data,
            promise = data.promise,
            actual = null,
            gap = null,
            percent = null,
            points = null,
            suffix = (game == 'gitw') ? '%' : '';

        if (data.actual !== null) {
            actual = data.actual;
            percent = data.percent;
            points = data.points;
            gap = promise - actual;
        }



        return (
            <tr>
                <th className="border-left-thin">{game.toUpperCase()}</th>
                <td className="promise">
                    <GameField field="promise" gameValue={promise} suffix={suffix} />
                </td>
                <td className="actual">
                    <GameField
                        field="actual"
                        gameValue={actual}
                        suffix={suffix}
                        updateGameValue={this.updateGameValue}
                        editable={this.props.editable}
                        handleFieldOnChange={this.handleFieldOnChange}
                        />
                </td>
                <td>
                    <GameField field="gap" gameValue={gap} suffix={suffix} />
                </td>
                <td>
                    <GameField field="percent" gameValue={percent} suffix="%" />
                </td>
                <td className="border-right-thin">
                    <GameField field="points" gameValue={points} />
                </td>
            </tr>
        )
    }
});

export default GameRow;
