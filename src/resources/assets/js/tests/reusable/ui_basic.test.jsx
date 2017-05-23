import React from 'react'
import renderer from 'react-test-renderer'

import { ModeSelectButtons, LoadStateFlip } from '../../reusable/ui_basic'
import { LoadingMultiState } from '../../reusable/reducers'

test('ModeSelectButtons', () => {

    const items = [
        {id: 'foo', label: 'Hello'},
        {id: 'bar', label: 'Bar'}
    ]
    let onChange = () => null
    const tree = renderer.create(
        <ModeSelectButtons keyProp="id" items={items} current="foo" onClick={onChange} />
    ).toJSON()

    expect(tree).toMatchSnapshot()

    // Check that the 'active' class is only set on one of the buttons
    expect(tree.children.length).toEqual(2)
    expect(tree.children[0].props.className).toMatch(/active/)
    expect(tree.children[1].props.className).not.toMatch(/active/)
})


test('LoadStateFlip', () => {
    const states = LoadingMultiState.states

    const tree = renderer.create(
        <LoadStateFlip loadState={states.loading}>
            <div>Will never be seen</div>
        </LoadStateFlip>
    ).toJSON()
    expect(tree).toMatchSnapshot()
    expect(tree.children[1]).toMatch(/spinner/)

    const tree2 = renderer.create(
        <LoadStateFlip loadState={states.loaded}>
            <div className="foo">HELLO</div>
        </LoadStateFlip>
    ).toJSON()
    expect(tree).toMatchSnapshot()
    const div = tree2.children[0]
    expect(div.props.className).toEqual('foo')
    expect(div.children[0]).toEqual('HELLO')
})
