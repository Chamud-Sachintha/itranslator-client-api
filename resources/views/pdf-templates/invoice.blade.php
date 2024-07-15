<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
        }
        .invoice-container {
            max-width: 800px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .invoice-header img {
            width: 100px;
            margin-bottom: 10px;
        }
        .invoice-header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        .invoice-details div {
            width: 48%;
        }
        .invoice-details h2 {
            margin: 0 0 10px;
            font-size: 18px;
            color: #333;
        }
        .invoice-details p {
            margin: 0;
            font-size: 14px;
            color: #666;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .invoice-table th,
        .invoice-table td {
            padding: 15px;
            border: 1px solid #eee;
            text-align: left;
            font-size: 14px;
        }
        .invoice-table th {
            background-color: #f0f0f0;
            color: #333;
        }
        .invoice-total {
            text-align: right;
            margin-bottom: 20px;
        }
        .invoice-total h2 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .invoice-total p {
            margin: 5px 0 0;
            font-size: 14px;
            color: #666;
        }
        .invoice-footer {
            text-align: center;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="text-center text-150">
                <img style="width: 350px;" src="data:image/png;base64,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" alt="">
            </div>
            <h1>Invoice</h1>
        </div>
        <div class="invoice-details">
            <div>
                <h2>Invoice To:</h2>
                <p>{{ $data['clientName'] }}</p>
                <p> {{ $data["address"] }}</p>
                <p>Phone: {{ $data['mobileNumber'] }}</p>
            </div>
            <div>
                <h2>Invoice Details:</h2>
                <p>Invoice Number: {{ $data['invoiceNo'] }}</p>
                <p>Issue Date: {{ date('Y-m-d H:i:s') }}</p>
                <p>Delivery Method: {{ $data["deliveryType"] }}</p>
                <p>Status: Unpaid</p>
            </div>
        </div>
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Document Title</th>
                    <th>Pages</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['documentObjectArray'] as $item)
                    <tr>
                        <td>1</td>
                        <td>{{ $item['translationTitle'] }}</td>
                        <td>{{ $item['pages'] }}</td>
                        <td>LKR. {{ $item['nicTranslateModel']['price'] }}.00</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="invoice-total">
            <h2>Total Amount</h2>
            <p>LKR. {{ $data["totalAmount"] }}.00</p>
        </div>
        <div class="invoice-footer">
            <p>Thank you for your order!</p>
        </div>
    </div>
</body>
</html>