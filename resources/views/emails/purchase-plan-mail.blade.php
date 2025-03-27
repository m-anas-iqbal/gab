<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Invoice</title>
    <style>
        .invoice-container {
            max-width: 700px;
            margin: 0 auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            background: #fff;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #F4F4F4;
            padding: 15px;
            border-radius: 8px;

            & p {
                font-size: 12px;
                margin: 0;
            }
        }

        .logo {
            width: 50px;
        }

        h2 {
            margin: 0;
            font-size: 22px;
        }

        .invoice-section {
            margin-top: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }

        .invoice-section h3 {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .invoice-section p {
            font-size: 14px;
            margin: 5px 0;
        }

        .invoice-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .invoice-table th,
        .invoice-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .invoice-footer {
            margin-top: 20px;
            text-align: center;
            font-weight: bold;
        }

        .cus,
        .org {
            display: inline-block;
        }

        .org {
            float: right;
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <img src="https://stage523.yourdesigndemo.net/assets/media/logo-icon.png" alt="Logo" class="logo" />
            <div>
                <h2>INVOICE</h2>
                <p><strong>Invoice No:</strong> #{{ $invoice }}</p>
                <p><strong>Date:</strong>{{ \Carbon\Carbon::parse($date)->format('d M Y') }} </p>
            </div>
        </div>
        <div class="invoice-section cus">
            <h3>Customer Information</h3>
            <p>
                <strong>Name:</strong> {{ $user->name }}
            </p>
            <p>
                <strong>Email:</strong> {{ $user->email }}
            </p>
            <p>
                <strong>Phone:</strong> {{ $user->phone }}
            </p>
        </div>
        <div class="invoice-section org">
            <h3>Organization Information</h3>
            <p>
                <strong>Organization:</strong> {{ $organization->name }}
            </p>
            <p>
                <strong>Address:</strong> {{ $organization->city . ', ' . $organization->country }}
            </p>
            <p>
                <strong>Website:</strong> {{ $organization->website }}
            </p>
        </div>
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Expiry</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $plan }}</td>
                    <td>{{ $expairy }}</td>
                    <td>{{ $currency . $amount }}</td>
                </tr>
            </tbody>
        </table>
        <div class="invoice-footer">
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>

</html>
