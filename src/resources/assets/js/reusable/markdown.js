import React from 'react'
import { objectAssign } from './ponyfill'
import { defaultRules, parserFor, reactFor, ruleOutput } from 'simple-markdown'

const paragraphRule = objectAssign({}, defaultRules.paragraph, {
    react: function(node, output, state) {
        return React.createElement('p', {}, output(node.content, state))
    },
})

const rules = objectAssign({}, defaultRules, {
    paragraph: paragraphRule
})

const rawBuiltParser = parserFor(rules)
export function parse(source) {
    var blockSource = source + '\n\n'
    return rawBuiltParser(blockSource, {inline: false})
}
export const reactOutput = reactFor(ruleOutput(rules, 'react'))
