<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.4/axios.min.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Purchase</title>
</head>
<body>
<h1>Secure Checkout</h1>
<form>
    @csrf
    <h2>1. Choose Membership</h2>
    <label for="membership_id"></label>
    <select id="membership_id" name="membership_id">
        <option value="5">Monthly - $29.97</option>
        <option value="1">Yearly - $197</option>
    </select>

    <h2>2. Enter Your Details</h2>
    <div>
        <label for="first_name">First Name</label>
        <input type="text" id="first_name" name="first_name" placeholder="First Name">
    </div>
    <div>
        <label for="last_name">Last Name</label>
        <input type="text" id="last_name" name="last_name" placeholder="Last Name">
    </div>
    <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Email">
    </div>
    <div>
        <label for="email_confirmation">Confirm Email</label>
        <input type="email" id="email_confirmation" name="email_confirmation" placeholder="Confirm Email">
    </div>
    <div>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Password">
    </div>
    <div>
        <label for="password_confirmation">Confirm Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm Password">
    </div>
    <div>
        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" placeholder="Phone">
    </div>
    <div>
        <label for="address_line_1">Address Line 1</label>
        <input type="text" id="address_line_1" name="address_line_1" placeholder="Address Line 1">
    </div>
    <div>
        <label for="address_line_2">Address Line 2</label>
        <input type="text" id="address_line_2" name="address_line_2" placeholder="Address Line 2">
    </div>
    <div>
        <label for="address_city">City</label>
        <input type="text" id="address_city" name="address_city" placeholder="City">
    </div>
    <div>
        <label for="address_state">State</label>
        <input type="text" id="address_state" name="address_state" placeholder="State">
    </div>
    <div>
        <label for="address_postal_code">Postal Code</label>
        <input type="text" id="address_postal_code" name="address_postal_code" placeholder="Postal Code">
    </div>
    <div>
        <label for="address_country">Country</label>
        <input type="text" id="address_country" name="address_country" placeholder="Country">
    </div>

    <h2>3. Payment Details</h2>
    <div id="card-element">
        <!-- A Stripe Card Element will be inserted here. -->
    </div>
    <div id="card-errors" role="alert"></div>

    <div>
        <button id="generate">Generate Payment Token</button>
        <span id="payment_token"></span>
    </div>

    <div>
        <button type="submit">Register</button>
    </div>
</form>

<script>
    var stripe = Stripe('{{  config('services.stripe.1.key') }}');

    // Initialize the card element.
    var elements = stripe.elements({
        hidePostalCode: true,
    });
    var cardElement = elements.create('card');
    cardElement.mount('#card-element');

    // Clean up the states.
    $(document).ready(function () {
        // Logout existing user
        axios.post('/logout')

        $('form').on('submit', function (e) {
            e.preventDefault()

            generatePaymentMethod()
                .then(function (result) {
                    if (result.error) {
                        alert(result.error.message)
                    } else {
                        return register(result.paymentMethod.id)
                            .then(function (response) {
                                const apiToken = response.data.data.token
                                axios.defaults.headers.common['Authorization'] = `Bearer ${apiToken}`;

                                subscribe()
                                    .then(function () {
                                        alert('You have successfully subscribed to the membership!')
                                    })
                                    .catch(function (error) {
                                        console.log(error)
                                        alert(error)
                                    })
                            })
                            .catch(function (error) {
                                console.log(error)
                                alert(error)
                            })
                    }
                })
        })

        $('#generate').on('click', function (e) {
            e.preventDefault()

            generatePaymentToken()
                .then(function (result) {
                    if (result.error) {
                        alert(result.error.message)
                    } else {
                        $('#payment_token').text(result.token.id)
                    }
                })
        })

        function generatePaymentToken() {
            return stripe
                .createToken(cardElement, {
                    name: $('#first_name').val() + ' ' + $('#last_name').val(),
                    address_line1: $('#address_line_1').val(),
                    address_line2: $('#address_line_2').val(),
                    address_city: $('#address_city').val(),
                    address_state: $('#address_state').val(),
                    address_zip: $('#address_postal_code').val(),
                    address_country: $('#address_country').val(),
                })
        }

        function generatePaymentMethod() {
            return stripe
                .createPaymentMethod({
                    type: 'card',
                    card: cardElement,
                    billing_details: {
                        name: $('#first_name').val() + ' ' + $('#last_name').val(),
                        email: $('#email').val(),
                        phone: $('#phone').val(),
                        address: {
                            line1: $('#address_line_1').val(),
                            line2: $('#address_line_2').val(),
                            city: $('#address_city').val(),
                            state: $('#address_state').val(),
                            postal_code: $('#address_postal_code').val(),
                            country: $('#address_country').val(),
                        }
                    }
                })

        }

        function register(paymentMethod) {
            const requestData = {
                first_name: $('#first_name').val(),
                last_name: $('#last_name').val(),
                email: $('#email').val(),
                email_confirmation: $('#email_confirmation').val(),
                password: $('#password').val(),
                password_confirmation: $('#password_confirmation').val(),
                phone: $('#phone').val(),
                address_line_1: $('#address_line_1').val(),
                address_line_2: $('#address_line_2').val(),
                address_city: $('#address_city').val(),
                address_state: $('#address_state').val(),
                address_postal_code: $('#address_postal_code').val(),
                address_country: $('#address_country').val(),
                payment_method: paymentMethod,
                market_id: 1,
            };

            return axios.post('/api/v1/auth/register/member', requestData)
        }

        function subscribe() {
            return axios.post('/api/v1/subscriptions',
                {
                    membership_id: $('#membership_id').val(),
                }
            )
        }
    })
</script>
</body>
</html>
