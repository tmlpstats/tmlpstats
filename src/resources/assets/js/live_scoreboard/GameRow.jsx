import React from 'react'
import GameField from './GameField'

export default class GameRow extends React.PureComponent {
    render() {
        var gap = null
        const { game, data } = this.props
        const { promise, actual } = data
        const suffix = (game == 'gitw') ? '%' : ''

        if (data.actual !== null) {
            gap = promise - actual
        }

        return (
            <tr>
                <th className="border-left-thin">{game.toUpperCase()}</th>
                <td className="promise">
                    {promise}{suffix}
                </td>
                <td className="actual">
                    <GameField
                        game={game}
                        field="actual"
                        gameValue={actual}
                        gameData={data}
                        suffix={suffix}
                        editable={this.props.editable}
                        />
                </td>
                <td>
                    {gap}{suffix}
                </td>
                <td>
                    {data.percent}%
                </td>
                <td className="border-right-thin">
                    {data.points}
                </td>
            </tr>
        )
    }
}

export default GameRow
