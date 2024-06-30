<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration - ITranslator</title>
    <style>
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            border-top: 6px solid #b51b10;
        }

        .header {
            background-color: #b51b10;
            padding: 20px;
            text-align: center;
            color: #ffffff;
            border-bottom: 1px solid #dddddd;
        }

        .header img {
            width: 60px;
            height: 80px;
            margin-bottom: 15px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            padding: 20px;
            color: #333333;
        }

        .content h1 {
            color: #b51b10;
            font-size: 22px;
            margin-top: 0;
        }

        .content p {
            font-size: 16px;
            line-height: 1.6;
        }

        .order-details {
            margin-top: 20px;
            border-collapse: collapse;
            width: 100%;
        }

        .order-details th, .order-details td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        .order-details th {
            background-color: #f4f4f4;
        }
        .order-summary p {
            margin: 5px 0;
            font-size: 14px;
            color: #666666;
        }

        .order-summary {
            background-color: #f9f9f9;
        padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .order-details tr:nth-child(even) {
            background-color: #f9f9f9;
        }


        .button {
            display: inline-block;
            padding: 12px 25px;
            margin: 20px 0;
            background-color: #b51b10;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }

        .footer {
            background-color: #f4f4f4;
            padding: 20px;
            text-align: center;
            color: #999999;
            font-size: 14px;
            border-top: 1px solid #dddddd;
        }

        .footer a {
            color: #b51b10;
            text-decoration: none;
        }

        .footer .social-icons a {
            margin: 0 10px;
            color: #b51b10;
            text-decoration: none;
            font-size: 20px;
        }

        .footer .social-icons a:hover {
            color: #333333;
        }

        .success-icon {
            font-size: 80px;
            color: #28a745;
            text-align: center;
            margin-top: 10px;
        }

        .order-number {
            font-size: 24px;
            font-weight: bold;
            color: #b51b10;
            text-align: center;
            margin-top: 10px;
        }

        .order-description {
            font-size: 16px;
            text-align: center;
            margin-top: 10px;
        }

        @media screen and (max-width: 600px) {
            .container {
                width: 100%;
                margin: 10px;
            }

            .order-details th, .order-details td {
                padding: 10px;
            }
        }
    </style>
</head>
@if($details['bodyType'] == 2)
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('avatar/itlogo.png') }}">
            <h1>Welcome to ITranslator</h1>
        </div>
        <div class="content">
            <h1>Hi {{$details['full_name']}},</h1>
            <p>Thank you for registering with ITranslator. We are delighted to have you on board.</p>
            <p>Registered Date:{{$details['created_at']}}</p>
            <a href="https://itranslate.lk/" class="button">Visit Our Website</a>
        </div>
        <div class="footer">
            <div class="social-icons">
                <a href="https://facebook.com/"><i class="fab fa-facebook-f"></i></a>
                <a href="https://youtube.com/"><i class="fab fa-youtube"></i></a>
                <a href="https://linkedin.com/"><i class="fab fa-linkedin-in"></i></a>
            </div>
            <p>&copy; 2024 ITranslator. All rights reserved.</p>
            <p>Developed by <a href="https://builtonus.com/">BuiltOnUs</a></p>
            <p>Website : <a href="https://itranslate.lk/">itranslate.lk </a> | Contact us: <a href="mailto:itranslate.lk@gmail.com">itranslate.lk@gmail.com</a></p>
        </div>
    </div>
</body>
@elseif($details['bodyType'] == 1)

<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('avatar/itlogo.png') }}" >
            <h1>Thank You for Your Order!!</h1>
        </div>
        <div class="content">
            <h1>Order Confirmation</h1>
            <p>Order Number: <strong>$details['OrderNo'] </strong></p>
            <p>Thank you for your order. Below are the details of your purchase:</p>
            
            <table class="order-details">
            
                <tr>
                <th>Product Name</th>
                    <td>$details['ProductName'] </td>
                   
                </tr>
                <tr>
                    <td>Delivery Method</td>
                    <td>$details['DeliveryMethord'] </td>
                </tr>
                <tr>
                    <td>Total Amount</td>
                    <td>$details['TotalAmount'] </td>
                </tr>
            </table>

            <div class="order-summary">
                <p><strong>Payment Details</strong></p>
                <p>Payment Method: $details['PaymentMethord'] </p>
                <p>Payment Status: Pending </p>
            </div>
            
            <p>We appreciate your business and hope you enjoy your purchase.</p>
            <a href="https://dashboard.itranslate.lk/" class="button">View Your Order</a>
        </div>
        <div class="footer">
            <div class="social-icons">
                <a href="https://facebook.com/"><i class="fab fa-facebook-f"></i></a>
                <a href="https://youtube.com/"><i class="fab fa-youtube"></i></a>
                <a href="https://linkedin.com/"><i class="fab fa-linkedin-in"></i></a>
            </div>
            <p>&copy; 2024 ITranslator. All rights reserved.</p>
            <p>Developed by <a href="https://builtonus.com/">BuiltOnUs</a></p>
            <p>Website : <a href="https://itranslate.lk/">itranslate.lk </a> | Contact us: <a href="mailto:itranslate.lk@gmail.com">itranslate.lk@gmail.com</a></p>
        </div>
    </div>
</body>
@elseif($details['bodyType'] == 3)
<body>
<div class="container">
        <div class="header">
            <img src="{{ asset('avatar/itlogo.png') }}" alt="ITranslator Logo">
            <h1>Thank You for Your Order</h1>
        </div>
        <div class="content">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="order-number">
                Order Number: {{$details['OrderNo']}}
            </div>
            <div class="order-summary">
                <p>Type of Order: <strong> {{$details['TypeOfOrder']}}</strong></p>
                <p>Order Placed Date: <strong>{{$details['created_at']}}</strong></p>
                <p>Current Status: <strong>Pending</strong></p>
            </div>
            
            <p>We appreciate your business and hope you enjoy your purchase.</p>
            <div class="button-container">
                <a href="https://dashboard.itranslate.lk/" class="button">View Your Order</a>
            </div>
        </div>
        <div class="footer">
            <div class="social-icons">
                <a href="https://facebook.com/"><i class="fab fa-facebook-f"></i></a>
                <a href="https://youtube.com/"><i class="fab fa-youtube"></i></a>
                <a href="https://linkedin.com/"><i class="fab fa-linkedin-in"></i></a>
            </div>
            <p>&copy; 2024 ITranslator. All rights reserved.</p>
            <p>Developed by <a href="https://builtonus.com/">BuiltOnUs</a></p>
            <p>Website : <a href="https://itranslate.lk/">itranslate.lk </a> | Contact us: <a href="mailto:itranslate.lk@gmail.com">itranslate.lk@gmail.com</a></p>
        </div>
    </div>
</body>
@endif
</html>
