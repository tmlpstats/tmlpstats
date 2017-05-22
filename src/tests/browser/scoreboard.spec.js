/**
 * Tests the various functionality of the scoreboard.
 *
 */
const vars = require('./vars')

const DEFAULT_WAIT = 10000

describe('Scoreboard', () => {
    // After everything, capture logs and filter boring entries.
    if (browser.desiredCapabilities && browser.desiredCapabilities.browser == 'Chrome') {
        after(function() {
            const BORING_MESSAGES = /Download the React DevTools/

            let log = browser.log('browser').value
            let important = log.filter(entry => !BORING_MESSAGES.test(entry.message))
            if (important.length) {
                console.log(important)
                throw important
            }
        })
    }

    it('can perform login', () => {
        browser.url('/auth/login')

        let form = $('form')
        form.waitForExist()
        expect(browser.getTitle()).toMatch(/TMLP/i)
        form.$('[name="email"]').addValue(vars.login.email)
        form.$('[name="password"]').addValue(vars.login.password)
        form.$('input.btn-default').click()
        browser.pause(1000)
        $('table').waitForExist(DEFAULT_WAIT)
    })

    it('can load new Submission UI', () => {
        $('=Submit Report (beta)').click()
        browser.pause(1000)
        // We wait for the h3 first because it means the content pane is done loading.
        $('h3').waitForExist(DEFAULT_WAIT)
        $('.submission-nav').waitForExist(1000)
    })

    it('can enter in numbers for week', () => {
        //let form = $('form')
        let formweek = vars.scoreboard.week - 1 // Decrement because week is off by 1.
        
        // Get week elements.
        let cap = $('[name="submission.scoreboard.scoreboards['+formweek+'].games.cap.actual"]')
        let cpc = $('[name="submission.scoreboard.scoreboards['+formweek+'].games.cpc.actual"]')
        let t1x = $('[name="submission.scoreboard.scoreboards['+formweek+'].games.t1x.actual"]')
        let t2x = $('[name="submission.scoreboard.scoreboards['+formweek+'].games.t2x.actual"]')
        let gitw = $('[name="submission.scoreboard.scoreboards['+formweek+'].games.gitw.actual"]')
        let lf = $('[name="submission.scoreboard.scoreboards['+formweek+'].games.lf.actual"]')
        
        // Set values for week.
        cap.setValue(60)
        cpc.setValue(20)
        t1x.setValue(10)
        t2x.setValue(2)
        gitw.setValue('90%') // Give percent, should still work.
        lf.setValue(30)
        
        // The scoreboard is saved automatically.
        
        // Now verify that changes persists.
        browser.refresh()
        expect(cap.getValue()).toEqual('60')
        expect(cpc.getValue()).toEqual('20')
        expect(t1x.getValue()).toEqual('10')
        expect(t2x.getValue()).toEqual('2')
        expect(gitw.getValue()).toEqual('90%')
        expect(lf.getValue()).toEqual('30')
    })
})
