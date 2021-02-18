@extends('template')

@section('headers')
    <style rel="stylesheet">

        body {
            color: #333;
            font-size: 13px;
            margin: 0; }

        input, textarea, select, button {
            color: #333;
            font-size: 13px; }



        p, h1, h2, h3, h4, h5, h6, ul {
            margin: 0; }

        img {
            max-width: 100%; }

        ul {
            padding-left: 0;
            margin-bottom: 0; }

        a:hover {
            text-decoration: none; }

        :focus {
            outline: none; }

        .wrapper {
            min-height: 100vh;
            background-size: cover;
            background-repeat: no-repeat;
            display: flex;
            align-items: center; }

        .inner {
            min-width: 850px;
            margin: auto;
            padding-top: 68px;
            padding-bottom: 48px;
            background: url("img/interestform.png"); }
        .inner h3 {
            text-transform: uppercase;
            font-size: 22px;
            text-align: center;
            margin-bottom: 32px;
            color: #333;
            letter-spacing: 2px; }

        form {
            width: 50%;
            padding-left: 45px; }

        .form-group {
            display: flex; }
        .form-group .form-wrapper {
            width: 50%; }
        .form-group .form-wrapper:first-child {
            margin-right: 20px; }

        .form-wrapper {
            margin-bottom: 17px; }
        .form-wrapper label {
            margin-bottom: 9px;
            display: block; }

        .form-control {
            border: 1px solid #333;
            display: block;
            width: 100%;
            height: 40px;
            padding: 0 20px;
            border-radius: 20px;
            background: none; }
        .form-control:focus {
            border: 1px solid #333; }

        select {
            -moz-appearance: none;
            -webkit-appearance: none;
            cursor: pointer;
            padding-left: 20px; }
        select option[value=""][disabled] {
            display: none; }

        button {
            border: none;
            width: 152px;
            height: 40px;
            margin: auto;
            margin-top: 29px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            background: #333;
            font-size: 13px;
            color: #fff;
            text-transform: uppercase;
            border-radius: 20px;
            overflow: hidden;
            -webkit-transform: perspective(1px) translateZ(0);
            transform: perspective(1px) translateZ(0);
            box-shadow: 0 0 1px rgba(0, 0, 0, 0);
            position: relative;
            -webkit-transition-property: color;
            transition-property: color;
            -webkit-transition-duration: 0.5s;
            transition-duration: 0.5s; }
        button:before {
            content: "";
            position: absolute;
            z-index: -1;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #333;
            -webkit-transform: scaleX(0);
            transform: scaleX(0);
            -webkit-transform-origin: 0 50%;
            transform-origin: 0 50%;
            -webkit-transition-property: transform;
            transition-property: transform;
            -webkit-transition-duration: 0.5s;
            transition-duration: 0.5s;
            -webkit-transition-timing-function: ease-out;
            transition-timing-function: ease-out; }
        button:hover:before {
            -webkit-transform: scaleX(1);
            transform: scaleX(1);
            -webkit-transition-timing-function: cubic-bezier(0.52, 1.64, 0.37, 0.66);
            transition-timing-function: cubic-bezier(0.52, 1.64, 0.37, 0.66); }

        .checkbox {
            position: relative; }
        .checkbox label {
            padding-left: 22px;
            cursor: pointer; }
        .checkbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer; }
        .checkbox input:checked ~ .checkmark:after {
            display: block; }

        .checkmark {
            position: absolute;
            top: 50%;
            left: 0;
            transform: translateY(-50%);
            height: 12px;
            width: 13px;
            border-radius: 2px;
            background-color: #ebebeb;
            border: 1px solid #ccc;
            color: #000;
            font-size: 10px;
            font-weight: bolder; }
        .checkmark:after {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
            content: '\2713';
        }

        @media (max-width: 991px) {
            .inner {
                min-width: 768px; } }
        @media (max-width: 767px) {
            .inner {
                min-width: auto;
                background: none;
                padding-top: 0;
                padding-bottom: 0; }

            form {
                width: 100%;
                padding-right: 15px;
                padding-left: 15px; } }

        .option {
            margin-right: 10px;
        }

    </style>
@endsection

@section('content')
    <div>
        <div class="wrapper">
            <div class="inner">
                <form method="POST" action="/interestform">
                    {{ csrf_field() }}
                    <h3>Interest Form</h3>
                    <div class="form-wrapper">
                        <label>What team(s) are you interested in?</label>
                        <div class="form-group">
                            <label for="vision" class="option">
                                <input type="radio" name="team_interest" id="vision" value="vision" required> Vision
                            </label>
                            <label for="regional" class="option">
                                <input type="radio" name="team_interest" id="regional" value="regional"> Regional Statistician
                            </label>
                            <label for="both" class="option">
                                <input type="radio" name="team_interest" id="both" value="both"> Both
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-wrapper">
                            <label for="firstname">First Name</label>
                            <input id="firstname" name="firstname" type="text" class="form-control" required>
                        </div>
                        <div class="form-wrapper">
                            <label for="lastname">Last Name</label>
                            <input id="lastname" name="lastname" type="text" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-wrapper">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="text" class="form-control" required>
                    </div>
                    <div class="form-wrapper">
                        <label for="phone">Phone</label>
                        <input id="phone" name="phone" type="text" class="form-control" required>
                    </div>
                    <div class="form-wrapper">
                        <label for="team">Team</label>
                        <select id="team" name="team" class="form-control" required>
                            <option></option>
                            <optgroup label="Australia & New Zealand">
                                <option value="Auckland">Auckland</option>
                                <option value="Melbourne">Melbourne</option>
                                <option value="Perth">Perth</option>
                                <option value="Sydney">Sydney</option>
                            </optgroup>
                            <optgroup label="German Cars">
                                <option value="London">London</option>
                                <option value="Tel Aviv">Tel Aviv</option>
                            </optgroup>
                            <optgroup label="India">
                                <option value="Ahmedabad">Ahmedabad</option>
                                <option value="Aurangabad">Aurangabad</option>
                                <option value="Bangalore">Bangalore</option>
                                <option value="Chennai">Chennai</option>
                                <option value="Delhi">Delhi</option>
                                <option value="Hyderabad">Hyderabad</option>
                                <option value="Mumbai Blue">Mumbai Blue</option>
                                <option value="Mumbai Green">Mumbai Green</option>
                                <option value="Mumbai Red">Mumbai Red</option>
                            </optgroup>
                            <optgroup label="North America">
                                <option value="Atlanta">Atlanta</option>
                                <option value="Boston">Boston</option>
                                <option value="Central Florida">Central Florida</option>
                                <option value="Chicago">Chicago</option>
                                <option value="Dallas">Dallas</option>
                                <option value="Denver">Denver</option>
                                <option value="Detroit">Detroit</option>
                                <option value="Florida">Florida</option>
                                <option value="Houston">Houston</option>
                                <option value="Los Angeles">Los Angeles</option>
                                <option value="Mexico">Mexico</option>
                                <option value="Montreal">Montreal</option>
                                <option value="MSP">MSP</option>
                                <option value="New Jersey">New Jersey</option>
                                <option value="New York">New York</option>
                                <option value="Orange County">Orange County</option>
                                <option value="Philadelphia">Philadelphia</option>
                                <option value="Phoenix">Phoenix</option>
                                <option value="San Diego">San Diego</option>
                                <option value="San Francisco">San Francisco</option>
                                <option value="San Jose">San Jose</option>
                                <option value="Seattle">Seattle</option>
                                <option value="Toronto">Toronto</option>
                                <option value="Vancouver">Vancouver</option>
                                <option value="Washington, DC">Washington, DC</option>
                            </optgroup>
                        </select>
                    </div>
                    <button>Send</button>
                </form>
            </div>
        </div>
    </div>

@endsection
