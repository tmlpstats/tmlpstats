/**
 * This is an everything smoke test to use as our very first selenium interaction.
 *
 * It performs a login and then goes to a variety of views.
 * In the future it might be prudent to run more flows, but this is a good start.
 *
 */
const vars = require('./vars')

const DEFAULT_WAIT = 10000

describe('Smoke Test', () => {
    // After everything, capture logs and filter boring entries
    if (browser.desiredCapabilities && browser.desiredCapabilities.browser == 'Chrome') {
        after(function() {
            const BORING_MESSAGES = /Download the React DevTools|is not a valid email address/

            let log = browser.log('browser').value
            let important = log.filter(entry => !BORING_MESSAGES.test(entry.message))
            if (important.length) {
                console.log(important)
                throw important
            }
        })
    }

    it('can perform login', () => {
        browser.windowHandleMaximize('current')
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
        $('=Submit Report').click()
        browser.pause(1000)
        // We wait for the h3 first because it means the content pane is done loading
        $('h3').waitForExist(DEFAULT_WAIT)
        $('.submission-nav').waitForExist(1000)
    })

    it('can switch to Courses Link', () => {
        $('.submission-nav').$('a=Courses').click()
        $('h3=Manage Courses').waitForExist(DEFAULT_WAIT)
    })

    it('can switch to Review page', () => {
        $('.submission-nav').$('a=Review').click()
        $('h3=Review').waitForExist(DEFAULT_WAIT)
    })

    it('can manage Team Expansion', () => {
        $('.submission-nav').$('a=Team Expansion').click()
        $('h3=Manage Registrations').waitForExist(DEFAULT_WAIT)
        let expansionUrl = browser.getUrl()
        // Clicking the first team registration means we have to have one in the database
        $('table tbody').$('tr:nth-child(1)').$('a').click()
        $('h3*=Edit Application').waitForExist(DEFAULT_WAIT)
        expect(browser.getUrl()).not.toEqual(expansionUrl)
        let form = $('.submission-content form')
        //form.$('[name*="email"]').click()
        //form.$('[name*="email"]').setValue('hello@example.com')
        form.$('button=Save').click()
        browser.pause(1000)
    })

    it('can switch to Class List', () => {
        $('.submission-nav').$('a=Class List').click()
        $('h3=Class List').waitForExist(DEFAULT_WAIT * 3)
        $('h3=Program Leaders').waitForExist(DEFAULT_WAIT)
    })
})
