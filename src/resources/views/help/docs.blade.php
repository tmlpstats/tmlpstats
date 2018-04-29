@extends('template')

@section('content')
    <h1>Documentation</h1>
    <div>
        <h3>Points Breakdown</h3>
        <table class="table-bordered table-condensed text-center">
            <tr>
                <td>% Game Fulfilled</td>
                <td># Points for All Games<br>(Except Access to Power)</td>
                <td># Points for<br>Access to Power</td>
            </tr>
            <tr>
                <td>100%</td>
                <td>4</td>
                <td>8</td>
            </tr>
            <tr>
                <td>90%</td>
                <td>3</td>
                <td>6</td>
            </tr>
            <tr>
                <td>80%</td>
                <td>2</td>
                <td>4</td>
            </tr>
            <tr>
                <td>75%</td>
                <td>1</td>
                <td>2</td>
            </tr>
        </table>
        <br/>
        <table class="table-bordered table-condensed">
            <tr>
                <td colspan="2">Category of Team Performance Rating</td>
            </tr>
            <tr>
                <td>Powerful</td>
                <td>28</td>
            </tr>
            <tr>
                <td>High Performing</td>
                <td>22-27</td>
            </tr>
            <tr>
                <td>Effective</td>
                <td>16-21</td>
            </tr>
            <tr>
                <td>Marginally Effective</td>
                <td>9-15</td>
            </tr>
            <tr>
                <td>Ineffective</td>
                <td>Under 9</td>
            </tr>
        </table>
    </div>
@endsection
