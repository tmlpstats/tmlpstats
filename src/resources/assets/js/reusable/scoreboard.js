import { objectAssign } from './ponyfill'

export const GAME_KEYS = ['cap', 'cpc', 't1x', 't2x', 'gitw', 'lf']

const MAX_POINTS = 28
const MIN_POINTS = 0

var ratingsByPoints = initRatingsByPoints()

const pointsByPercent = [
    [100, 4],
    [90, 3],
    [80, 2],
    [75, 1]
]

export class Scoreboard {
    constructor(input) {
        this.games = {}

        GAME_KEYS.forEach((key) => {
            this.games[key] = new ScoreboardGame(key)
        })

        if (input) {
            this.parseInput(input)
        }
    }

    // Iterate all game objects in the 'official' order
    eachGame(callback) {
        GAME_KEYS.forEach((key) => {
            callback(this.games[key])
        })
    }

    parseInput(input) {
        this.week = input.week
        this.meta = input.meta || {}
        if (input.games){
            this.eachGame((game) => {
                game.parseInput(input.games[game.key])
            })
        }
    }

    /**
     * Calculate points for this entire row
     * @return int Points total; 0-24
     */
    points() {
        var total = 0
        // NOTE a for..in loop does not guarantee property ordering. This is fine for points()
        for(var name in this.games) {
            total += this.games[name].points()
        }
        return total
    }

    rating() {
        return ratingsByPoints[this.points()]
    }

    key(suffix) {
        return this.week + (suffix || '')
    }
}

export class ScoreboardGame {
    constructor(key) {
        this.key = key
    }

    parseInput(input) {
        this.actual = input.actual
        this.promise = input.promise
        this.original = input.original
        this.op = input.op || 'default'
    }

    percent() {
        if (!this.actual) {
            return 0
        }

        return Math.round(calculatePercent(this.promise, this.actual))
    }

    points() {
        // yes I know, this recalculates a lot of things. A little math never hurt anybody.
        return getPoints(this.key, this.percent())
    }
}

export default Scoreboard

/**
 * ReduxFriendlyScoreboard is slightly different from the more traditional OOP representation of scoreboard.
 *
 * It provides some helpers to transform scoreboard values and implement minimal changes to them,
 * so that we can take advantage of pure component updates.
 *
 * For that reason, instead of things like points and rating being functions, they're simply assigned
 * as properties on generic objects that share a roughly similar shape to a Scoreboard object.
 */
export class ReduxFriendlyScoreboard {
    dataFromRaw(sb) {
        var games = {}
        GAME_KEYS.forEach((game) => {
            const data = this._normalizeGame(game, sb.games[game])
            games[game] = this._updateGameOnly(data)
        })
        var pr = this._pointsRating(games)
        pr.games = games
        return objectAssign({}, sb, pr)
    }

    mergeGameUpdates(sb, newGames) {
        var updatedGames = {}
        GAME_KEYS.forEach((game) => {
            updatedGames[game] = this._updateGameOnly(sb.games[game], newGames[game])
        })
        return objectAssign({}, sb, {games: updatedGames})
    }

    /**
     * Update a single value within a game, with minimal changes to the object tree.
     * @param  object sb    A scoreboard value
     * @param  string game  Game value
     * @param  string field The field we want to update
     * @param  any    value Any value
     * @return {[type]}       [description]
     */
    updateGameField(sb, game, field, value) {
        var gameData = sb.games[game]
        if (gameData[field] !== value) {
            const newGameData = this._updateGameOnly(gameData, {[field]: value})
            const newGames = objectAssign({}, sb.games, {[game]: newGameData})

            if (gameData.points != newGameData.points) {
                const pointsRating = this._pointsRating(newGames)
                pointsRating.games = newGames
                return objectAssign({}, sb, pointsRating)
            } else {
                return objectAssign({}, sb, {games: newGames})
            }
        }
        return sb
    }

    /** Add back the key to a game so we don't need it all over the place */
    _normalizeGame(game, data) {
        if (!data.key) {
            return objectAssign({}, data, {key: game})
        }
        return data
    }

    /**
     * Update only a single game with optional new values.
     * @param  object data      The data object describing a game
     * @param  object newValues If provided, new values to ascribe to this game.
     * @return object The same game value, or a new one
     */
    _updateGameOnly(data, newValues) {
        if (newValues) {
            data = objectAssign({}, data, newValues)
        }
        const percent = (data.actual)? Math.round(calculatePercent(data.promise, data.actual)) : 0
        if (percent != data.percent) {
            const generation = (data._gen || 0) + 1
            const points = getPoints(data.key, percent)
            return objectAssign({}, data, {percent, points, _gen: generation})
        } else {
            return data
        }
    }

    _pointsRating(games) {
        var points = 0
        for(var name in games) {
            points += games[name].points
        }
        const rating = ratingsByPoints[points]
        return {points, rating}
    }
}

function calculatePercent(promise, actual) {
    if (promise <= 0 || !actual) {
        return 0
    }

    var percent = (actual / promise) * 100

    return Math.max(percent, 0)
}

function getPoints(game, percent) {
    var points = 0
    for (var i = 0; i < pointsByPercent.length; i++) {
        const pp = pointsByPercent[i]
        if (percent >= pp[0]) {
            points = pp[1]
            break
        }
    }
    if (game == 'cap') {
        points = points * 2
    }
    return points
}

function initRatingsByPoints() {
    var rbp = {
        28: 'Powerful',
        22: 'High Performing',
        16: 'Effective',
        9: 'Marginally Effective',
        0: 'Ineffective'
    }
    var fill
    for (let i = MIN_POINTS; i < MAX_POINTS; i++) {
        if (rbp[i]) {
            fill = rbp[i]
        } else {
            rbp[i] = fill
        }
    }
    return rbp
}
