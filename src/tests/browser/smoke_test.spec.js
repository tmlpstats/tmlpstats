/**
 * This is an everything smoke test to use as our very first selenium interaction.
 *
 * It performs a login and then goes to a variety of views.
 * In the future it might be prudent to run more flows, but this is a good start.
 *
 */
const vars = require('./vars')

const DEFAULT_WAIT = 10000

describe('Smoke Test', function() {
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
        $('=Submit Report (beta)').click()
        browser.pause(1000)
        // We wait for the h3 first because it means the content pane is done loading
        $('h3').waitForExist(DEFAULT_WAIT)
        $('.submission-nav').waitForExist(1000)
    })

    it('can switch to Courses Link', () => {
        $('.submission-nav').$('a=Courses').click()
        $('h3=Manage Courses').waitForExist(DEFAULT_WAIT)
    })
})
