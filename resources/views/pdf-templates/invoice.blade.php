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
        .company-details {
            margin-top: 10px;
            font-size: 12px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="text-center text-150">
                <img alt="" style="width: 350px;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAlAAAADHCAYAAADWHm2XAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAEDoSURBVHhe7d13fBRl/gfwz2xLJYWEJHSDUQFR0BMVUVHEhiIKWMHOCYoaC/bzlPOsqFhQ7lBP2okgKkXxJ3DYQQELCNI7JpBCetv2/P74ZkMy27NL2A2f9+s1L3FnZ5KdzM5853m+z/fRlFIKRERERBQwg/4FIiIiIvKNARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJAVQ4ORzAqlXAY48BNpt+LREREbUSDKDCyeEAJk0Cpk4FCgsBp1P/DiIiImoFGECFi9MJHDwIfPklUFYGLF8O2O36dxEREVErwAAqXJxOYMsWoKJC/v3880BlJaCU/p1EREQU5RhAhYvdDsyaJd14ALB9O7Bp06H/JyIiolaDAVQ4OJ1ASQnw6aeHXrPZgA8+AGprG7+TiIiIWgEGUOHgcAAbN0ruk4tSEkDt3ctWKCIiolaGAVQ4WK3AzJnuSeMVFcA338h6IiIiajUYQIXK6ZSSBZ9/rl8jLU+TJx9KLCciIqJWgQFUqOx2YMOGpt13jW3fDvz4IwtrEhERtSIMoEJVVwfMm+fefeditQJz5gDV1SxpQERE1EowgAqF0wkUFQFLlngPjpSS4po7dzKZnIiIqJVgABUKqxVYswYoLtavaaqsDFi8mCUNiIiIWgkGUM2hlHTZlZUBc+f6H2XncAALFsj7mUxO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q19FREREUYwBVAhMJhO6dOmCIUOGeMxtSkhIwGmnncaCmERERK0MA6gQxcbGYuTIkR5znPr378/uOyIiolaIAVSIzGYzTj75ZOTk5DR53Wg0YvTo0ey+IyIiaoUYQIVI0zQkJiZi/PjxTVqaEhIScNJJJ3FaFiIiolaIAVQYmM1m9O/fH4mJiQ2vnXLKKcjIyOD0LERERK0Q7+5hYDAYkJmZiWuuuQaapsFgMGDcuHFMHiciImqlGECFicViwejRo2E2m5GQkIBTTz2VyeNEREStFAOoMDEYDMjJycFxxx2Hc845B5mZmey+IyIiaqU0pZTSv0jN43A48Mknn6B9+/bo168fW6CIiIhaKQZQREREREFiHxMRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQWJARQRERFRkBhAEREREQXp/wEBNb/gjMTm2QAAAABJRU5ErkJggg==" />     
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
                        <td>
                        @if(isset($item['nicTranslateModel']) && isset($item['nicTranslateModel']['price']))
                            LKR. {{ $item['nicTranslateModel']['price'] }}.00
                        @elseif(isset($item['bcTranslateModel']) && isset($item['bcTranslateModel']['price']))
                            LKR. {{ $item['bcTranslateModel']['price'] }}.00
                        
                            @elseif(isset($item['dcTranslateModel']) && isset($item['dcTranslateModel']['price']))
                        LKR. {{ $item['dcTranslateModel']['price'] }}.00
                        
                        @elseif(isset($item['otherDocumentModel']) && isset($item['otherDocumentModel']['price']))
                        LKR. {{ $item['otherDocumentModel']['price'] }}.00
                        
                        @else
                            LKR. 0.00
                        @endif
                    </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="invoice-total">
            <h2>Total Amount</h2>
            <p>LKR. {{ $data["totalAmount"] }}.00</p>
        </div>
        
    </div>
    <div class="invoice-footer" style="margin-top=80px;">
            <p style="display: block; text-align: center;">Thank you for your order!</p>
            <div class="company-details">
                <p>No.71/D, 1st Floor, High Level Plaza , Delkanda, Nugegoda, Sri Lanka.</p>
                <p>Phone: +94 11 282 6212, Mobile: +94 71 362 3018, Mobile: +94 71 717 7099</p>
                <p>Email: itranslate.lk@gmail.com, Email: info@itranslate.lk</p>
            </div>
        </div>
</body>
</html>