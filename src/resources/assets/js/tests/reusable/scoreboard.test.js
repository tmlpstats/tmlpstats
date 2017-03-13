import yaml from 'js-yaml'
import fs from 'fs'

import { Scoreboard, ScoreboardGame, GAME_KEYS } from '../../reusable/scoreboard'

const input = yaml.safeLoad(fs.readFileSync(`${__dirname}/../../../../../tests/inputs/scoreboard.yml`, 'utf8'))

describe('ScoreboardGame', () => {
    describe('Points', () => {
        input.points.forEach((fields) => {
            const [promise, actual, game, points] = fields
            test(`points for p=${promise} a=${actual} are ${points} (${game})`, () => {
                const sg = new ScoreboardGame(game.toLowerCase())
                sg.parseInput({promise, actual})
                expect(sg.points()).toBe(points)
            })

        })
    })

    describe('Percent', () => {
        input.percent.forEach((fields) => {
            const [promise, actual, percent] = fields
            test(`percent for p=${promise} a=${actual} is ${percent}%`, () => {
                const sg = new ScoreboardGame('cap')
                sg.parseInput({promise, actual})
                expect(sg.percent()).toBe(percent)
            })
        })
    })
})


describe('Scoreboard', () => {
    describe('With a blank scoreboard', () => {
        let sb = new Scoreboard()

        it('Should start with zero points and percent', () => {
            expect(sb.points()).toBe(0)
            expect(sb.percent()).toBe(0)
        })

        it('Should have a game for each game key', () => {
            GAME_KEYS.forEach((game) => {
                expect(sb.games[game]).toBeDefined()
            })
        })
    })

    describe('Test Ratings', () => {
        let sb = new Scoreboard()
        input.ratings.forEach((fields) => {
            const [score, rating] = fields
            test(`Rating for score ${score} should be ${rating}`, () => {
                // override the points return (effectively a 'mock')
                sb.points = () => score
                expect(sb.rating()).toEqual(rating)
            })
        })
    })

    describe('Full Scoreboards', () => {
        input.scoreboards_classic.forEach((item, n) => {
            test(`Full Scoreboard ${n}: ${item.points} points`, () => {
                let sb = new Scoreboard({games: convertClassicGame(item)})
                expect(sb.points()).toEqual(item.points)
            })
        })
    })
})


function convertClassicGame({promise, actual}) {
    let output = {}
    GAME_KEYS.forEach((game) => {
        output[game] = {
            promise: promise[game],
            actual: actual[game]
        }
    })
    return output
}
