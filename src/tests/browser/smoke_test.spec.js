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
        form.$('[name*="email"]').click()
        form.$('[name*="email"]').setValue('hello@example.com')
        form.$('button[type="submit"]').click()
    })

    it('can switch to Class List', () => {
        $('.submission-nav').$('a=Class List').click()
        $('h3=Class List').waitForExist(DEFAULT_WAIT * 3)
        $('h3=Program Leaders').waitForExist(DEFAULT_WAIT)
    })

    const testCases = [
        {
            firstName: 'First',
            lastName: 'Test',
            email: 'first.test@tmlpstats.com',
            phone: '555-555-5555',
            attendingWeekend: '1',
            accountability: 'Program Manager',
        },
        {
            firstName: 'Second',
            lastName: 'Test',
            email: 'second.test@tmlpstats.com',
            phone: '555-555-6666',
            attendingWeekend: '0',
            accountability: 'Classroom Leader',
        }
    ]

    const actions = ['edit', 'add']

    for (let i in testCases) {
        let testCase = testCases[i]
        let expectedRow = testCase.accountability == 'Program Manager' ? 1 : 2

        for (let j in actions) {
            let action = actions[j]

            it(`can ${action} ${testCase.accountability}`, () => {
                $('.submission-nav').$('a=Class List').click()
                $('h3=Program Leaders').waitForExist(DEFAULT_WAIT)

                // Click Leader link
                let leaderUrl = browser.getUrl()
                $(`td=${testCase.accountability}`).$('a').click()

                // Loads edit page
                $(`h3*=Edit ${testCase.accountability}`).waitForExist(DEFAULT_WAIT)
                expect(browser.getUrl()).not.toEqual(leaderUrl)

                // We're testing add. Click to get blank form
                if (action == 'add') {
                    $('.submission-content form').$('button*=New').click()
                    $(`h3*=Add ${testCase.accountability}`).waitForExist(DEFAULT_WAIT)
                }

                let form = $('.submission-content form')
                form.$('[name*="firstName"]').click()
                form.$('[name*="firstName"]').setValue(testCase.firstName)
                form.$('[name*="lastName"]').click()
                form.$('[name*="lastName"]').setValue(testCase.lastName)
                form.$('[name*="email"]').click()
                form.$('[name*="email"]').setValue(testCase.email)
                form.$('[name*="phone"]').click()
                form.$('[name*="phone"]').setValue(testCase.phone)
                form.$('[name*="attendingWeekend"]').selectByValue(testCase.attendingWeekend)
                form.$('button[type="submit"]').click()

                // Loads list page and row has new values
                $('h3*=Program Leaders').waitForExist(DEFAULT_WAIT)
                let row = $('table.programLeadersTable tbody').$(`tr:nth-child(${expectedRow})`)
                row.$(`td=${testCase.accountability}`).waitForExist(DEFAULT_WAIT)
                row.$(`td=${testCase.firstName} ${testCase.lastName}`).waitForExist(DEFAULT_WAIT)
                row.$(`td=${testCase.email}`).waitForExist(DEFAULT_WAIT)
                row.$(`td=${testCase.phone}`).waitForExist(DEFAULT_WAIT)
                row.$(`td=${(testCase.attendingWeekend ? 'yes' : 'no')}`).waitForExist(DEFAULT_WAIT)
            })

            it(`can go back from ${action}ing new ${testCase.accountability}`, () => {
                $('.submission-nav').$('a=Class List').click()
                $('h3=Program Leaders').waitForExist(DEFAULT_WAIT)

                // Click Leader link
                let leaderUrl = browser.getUrl()
                $(`td=${testCase.accountability}`).$('a').click()

                // Loads edit page
                $(`h3*=Edit ${testCase.accountability}`).waitForExist(DEFAULT_WAIT)
                expect(browser.getUrl()).not.toEqual(leaderUrl)

                // We're testing add. Click to get blank form
                if (action == 'add') {
                    $('.submission-content form').$('button*=New').click()
                    $(`h3*=Add ${testCase.accountability}`).waitForExist(DEFAULT_WAIT)
                }

                // Populate with fake data and click back
                let form = $('.submission-content form')
                form.$('[name*="firstName"]').click()
                form.$('[name*="firstName"]').setValue('Another')
                form.$('[name*="lastName"]').click()
                form.$('[name*="lastName"]').setValue('Name')
                form.$('button*=Back').click()

                // Loads list page and row has previous values
                $('h3*=Program Leaders').waitForExist(DEFAULT_WAIT)
                let row = $('table.programLeadersTable tbody').$(`tr:nth-child(${expectedRow})`)
                row.$(`td=${testCase.accountability}`).waitForExist(DEFAULT_WAIT)
                row.$(`td=${testCase.firstName} ${testCase.lastName}`).waitForExist(DEFAULT_WAIT)
                row.$(`td=${testCase.email}`).waitForExist(DEFAULT_WAIT)
                row.$(`td=${testCase.phone}`).waitForExist(DEFAULT_WAIT)
                row.$(`td=${(testCase.attendingWeekend ? 'yes' : 'no')}`).waitForExist(DEFAULT_WAIT)
            })
        }
    }
})
