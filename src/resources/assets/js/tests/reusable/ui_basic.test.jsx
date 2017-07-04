import React from 'react'
import renderer from 'react-test-renderer'

import {
    ModeSelectButtons, LoadStateFlip, SubmitFlip, ButtonStateFlip, Alert, MessagesComponent,
    Panel, Glyphicon, Modal, scrollIntoView } from '../../reusable/ui_basic'
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


describe('LoadStateFlip', () => {
    const states = LoadingMultiState.states

    test('Loading', () => {
        const tree = renderer.create(
            <LoadStateFlip loadState={states.loading}>
                <div>Will never be seen</div>
            </LoadStateFlip>
        ).toJSON()
        expect(tree).toMatchSnapshot()
        expect(tree.children[1]).toMatch(/spinner/)

    })

    test('Loaded', () => {
        const tree2 = renderer.create(
            <LoadStateFlip loadState={states.loaded}>
                <div className="foo">HELLO</div>
            </LoadStateFlip>
        ).toJSON()
        expect(tree2).toMatchSnapshot()
        const div = tree2.children[0]
        expect(div.props.className).toEqual('foo')
        expect(div.children[0]).toEqual('HELLO')
    })

    test('Failed with no additional message', () => {
        const tree = renderer.create(
            <LoadStateFlip loadState={states.failed}>
                <div className="foo">HELLO</div>
            </LoadStateFlip>
        )
        expect(tree).toMatchSnapshot()
    })

    test('Failed with additional error message', () => {
        let altFailed = Object.assign({}, states.failed, {error: {message: 'Message here.'}})
        const tree = renderer.create(
            <LoadStateFlip loadState={altFailed}>
                <div className="foo">not seen</div>
            </LoadStateFlip>
        )
        expect(tree).toMatchSnapshot()
    })

})

test('SubmitFlip', () => {
    const states = LoadingMultiState.states

    const loadingTree = renderer.create(
        <SubmitFlip loadState={states.loading} wrapGroup={true}>Submit</SubmitFlip>
    )
    expect(loadingTree).toMatchSnapshot()

    const loadedTree = renderer.create(
        <SubmitFlip loadState={states.loaded} wrapGroup={true}>Submit</SubmitFlip>
    )
    expect(loadedTree).toMatchSnapshot()

})

describe('ButtonStateFlip', () => {
    const states = LoadingMultiState.states

    test('Loading', () => {
        const loadingTree = renderer.create(
            <ButtonStateFlip loadState={states.loading} wrapGroup={true}>Submit</ButtonStateFlip>
        )
        expect(loadingTree).toMatchSnapshot()
    })

    test('Loaded', () => {
        const loadedTree = renderer.create(
            <ButtonStateFlip loadState={states.loaded} wrapGroup={true}>Submit</ButtonStateFlip>
        )
        expect(loadedTree).toMatchSnapshot()
    })

})

describe('MessagesComponent', () => {
    test('Empty Messages', () => {
        const emptyTree = renderer.create(
            <MessagesComponent />
        )
        expect(emptyTree).toMatchSnapshot()
    })

    const messages = [
        {level: 'warning', message: 'Hello W1'},
        {level: 'error', message: 'Error 1'},
        {level: 'warning', message: 'Warning 2'}
    ]

    test('Both Errors and Warnings', () => {
        const tree = renderer.create(
            <MessagesComponent messages={messages} />
        )
        expect(tree).toMatchSnapshot()
    })

    test('With custom reference string', () => {
        const tree = renderer.create(
            <MessagesComponent messages={messages} referenceString="Things" />
        )
        expect(tree).toMatchSnapshot()
    })
})

test('Alert', () => {
    const tree = renderer.create(
        <Alert alert="info">Content Here</Alert>
    )
    expect(tree).toMatchSnapshot()
})


test('Glyphicon', () => {
    const tree = renderer.create(
        <Glyphicon icon="ok-sign" />
    )
    expect(tree).toMatchSnapshot()
})

describe('Panel', () => {
    test('With Heading', () => {
        const tree = renderer.create(
            <Panel heading="Heading Text" headingLevel="h4">
                Panel body
            </Panel>
        )
        expect(tree).toMatchSnapshot()
    })

    test('No Heading', () => {
        const tree = renderer.create(
            <Panel>
                Simple Panel body
            </Panel>
        )
        expect(tree).toMatchSnapshot()
    })
})

test('Modal', () => {
    const onClose = jest.fn()
    let mockJQNode = {
        addClass: jest.fn(),
        removeClass: jest.fn(),
        append: jest.fn(),
        find: jest.fn()
    }
    const mockJQ = jest.fn(() => mockJQNode)
    window.$ = mockJQ
    const footer = <div>Modal Footer</div>
    const tree = renderer.create(
        <Modal title="Modal Title" footer={footer} onClose={onClose}>
            Modal body
        </Modal>
    )
    expect(tree).toMatchSnapshot()

    expect(mockJQ).toHaveBeenCalledTimes(1)
    expect(mockJQNode.addClass).toHaveBeenCalledWith('modal-open')
    expect(mockJQNode.append).toHaveBeenCalled()
    expect(mockJQNode.removeClass).not.toHaveBeenCalled()
    window.$ = undefined
})


// Maybe overkill mocking everything about `window` but also kinda cool
describe('scrollIntoView', () => {
    let element = {
        scrollIntoView: jest.fn()
    }
    const spy = jest.spyOn(window.document, 'getElementById').mockImplementation(() => element)

    afterEach(() => {
        spy.mockClear()
    })
    afterAll(() => {
        spy.mockRestore()
    })

    test('Immediate', () => {
        scrollIntoView('foo')
        expect(spy).toHaveBeenCalledWith('foo')
        expect(element.scrollIntoView).toHaveBeenCalledTimes(1)
    })

    test('Delayed', (done) => {
        element.scrollIntoView = element.scrollIntoView.mockImplementation(() => {
            expect(spy).toHaveBeenCalledWith('foobar')
            done()
        })
        scrollIntoView('foobar', 10)
    })
})
