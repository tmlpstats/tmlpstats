import React from 'react';
import ReactDOM from 'react-dom';
import GraphiQL from 'graphiql';
import { fetch } from '../reusable/ponyfill';

function graphQLFetcher(graphQLParams) {
    return fetch('/graphql', {
        method: 'post',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(graphQLParams),
        credentials: 'same-origin',
    }).then(response => response.json())
}

export function Interface() {
    return <GraphiQL fetcher={graphQLFetcher} />
}
