
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
